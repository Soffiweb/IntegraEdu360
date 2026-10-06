#!/usr/bin/env python3
"""Extract the latest AMIE period and join published Minedec district data."""
import argparse
import collections
import csv
import gzip
import json
import re
import xml.etree.ElementTree as ET
from pathlib import Path
from zipfile import ZipFile

NS = {'m': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
COLUMNS = ['codigo_amie', 'nombre', 'codigo_distrito', 'zona_codigo', 'provincia', 'canton', 'parroquia', 'sostenimiento', 'regimen', 'periodo']


def read_latest(path):
    with ZipFile(path) as archive:
        strings = []
        with archive.open('xl/sharedStrings.xml') as source:
            for event, elem in ET.iterparse(source, events=['end']):
                if elem.tag.endswith('}si'):
                    strings.append(''.join(elem.itertext()))
                    elem.clear()
        latest, rows, conflicts = '', {}, set()
        parent = None
        with archive.open('xl/worksheets/sheet1.xml') as source:
            for event, elem in ET.iterparse(source, events=['start', 'end']):
                if event == 'start' and elem.tag.endswith('}sheetData'):
                    parent = elem
                if event != 'end' or not elem.tag.endswith('}row'):
                    continue
                values = {}
                for cell in elem.findall('m:c', NS):
                    column = re.sub(r'\d+', '', cell.get('r'))
                    if len(column) > 1 or column > 'O':
                        continue
                    value = cell.find('m:v', NS)
                    if value is not None:
                        values[column] = strings[int(value.text)] if cell.get('t') == 's' else value.text
                period = values.get('A', '')
                if re.fullmatch(r'20\d{2}-20\d{2} Inicio', period):
                    if period > latest:
                        latest, rows, conflicts = period, {}, set()
                    if period == latest:
                        code = values.get('J', '').strip().upper()
                        if not re.fullmatch(r'\d{2}[A-Z]\d{5}', code):
                            raise ValueError('Código AMIE inválido: ' + code)
                        if code in rows and rows[code] != values:
                            conflicts.add(code)
                        rows[code] = values
                parent.remove(elem)
        if not rows:
            raise ValueError('El archivo no contiene períodos AMIE de inicio.')
        if conflicts:
            raise ValueError('AMIE con datos contradictorios en el período más reciente: ' + ', '.join(sorted(conflicts)))
        return latest, rows


def read_powerbi(path):
    raw = Path(path).read_bytes()
    response = json.loads(gzip.decompress(raw) if raw[:2] == b'\x1f\x8b' else raw)
    records = []
    for result in response['results']:
        data = result['result']['data']
        columns = [field['Name'].split('.', 1)[1] for field in data['descriptor']['Select']]
        entity = data['descriptor']['Select'][0]['Name'].split('.', 1)[0]
        for dataset in data['dsr']['DS']:
            for partition in dataset.get('PH', []):
                entries = partition.get('DM0', [])
                if not entries:
                    continue
                schema = entries[0]['S']
                previous = [None] * len(columns)
                for entry in entries:
                    values, decoded = iter(entry.get('C', [])), []
                    for index, field in enumerate(schema):
                        if entry.get('R', 0) & (1 << index):
                            value = previous[index]
                        elif entry.get('Ø', 0) & (1 << index):
                            value = None
                        else:
                            value = next(values)
                            if isinstance(value, int) and 'DN' in field:
                                value = dataset['ValueDicts'][field['DN']][value]
                        decoded.append(value)
                    previous = decoded
                    record = dict(zip(columns, decoded))
                    record['_source'] = entity
                    records.append(record)
    return records


def project(period, institutions, geography, supplements, districts):
    mapped = {}
    for row in geography:
        code = str(row.get('COD_AMIE') or '').strip().upper()
        if code in mapped:
            raise ValueError('AMIE duplicado en el cruce geográfico: ' + code)
        mapped[code] = row
    fallback = collections.defaultdict(set)
    for row in supplements:
        fallback[str(row.get('AMIE') or '').strip().upper()].add((str(row.get('DISTRITO') or '').strip().upper(), str(row.get('ZONA') or '').strip()))
    output, pending, zone_changes = [], [], []
    for code, row in sorted(institutions.items()):
        zone = row.get('B', '').replace('Zona ', '').strip()
        geo = mapped.get(code)
        district = str(geo.get('COD_AD_DISTRITO') or '').strip().upper() if geo else ''
        geo_zone = str(geo.get('NOM_ZONA') or '').replace('Zona ', '').strip() if geo else ''
        if district in districts and geo_zone != districts[district]:
            raise ValueError('Distrito y zona inconsistentes en la fuente: ' + code)
        if district not in districts:
            matches = {(d, z) for d, z in fallback[code] if d in districts and districts[d] == z}
            if len(matches) == 1:
                district, geo_zone = next(iter(matches))
            else:
                pending.append({'codigo_amie': code, 'nombre': row.get('I', ''), 'motivo': 'AMIE ausente del cruce' if geo is None else 'Código distrital sin identificación válida: ' + district})
                district = ''
        if district:
            if zone != geo_zone:
                zone_changes.append({'codigo_amie': code, 'zona_registro_amie': zone, 'zona_cruce_geografico': geo_zone, 'codigo_distrito': district})
            zone = geo_zone
        output.append({
            'codigo_amie': code, 'nombre': row.get('I', '').strip(), 'codigo_distrito': district,
            'zona_codigo': zone,
            'provincia': (geo.get('NOM_PROVINCIA') if geo and district else None) or row.get('C', '').strip(),
            'canton': (geo.get('NOM_CANTON') if geo and district else None) or row.get('E', '').strip(),
            'parroquia': (geo.get('NOM_PARROQUIA') if geo and district else None) or row.get('G', '').strip(), 'sostenimiento': row.get('M', '').strip(),
            'regimen': row.get('O', '').strip(), 'periodo': period,
        })
    return output, pending, zone_changes


def write_csv(path, fields, rows):
    with Path(path).open('w', newline='', encoding='utf-8') as target:
        writer = csv.DictWriter(target, fieldnames=fields)
        writer.writeheader()
        writer.writerows(rows)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('registro_xlsx')
    parser.add_argument('cruce_powerbi')
    parser.add_argument('--complemento')
    parser.add_argument('--directorio', default='database/data/mineduc')
    args = parser.parse_args()
    directory = Path(args.directorio)
    with (directory / 'distritos-2025.csv').open(encoding='utf-8') as source:
        districts = {row['codigo']: row['zona_codigo'] for row in csv.DictReader(source)}
    period, institutions = read_latest(args.registro_xlsx)
    rows, pending, changes = project(period, institutions, read_powerbi(args.cruce_powerbi), read_powerbi(args.complemento) if args.complemento else [], districts)
    write_csv(directory / 'instituciones-2025-2026.csv', COLUMNS, rows)
    write_csv(directory / 'instituciones-pendientes.csv', ['codigo_amie', 'nombre', 'motivo'], pending)
    write_csv(directory / 'instituciones-cambios-zona.csv', ['codigo_amie', 'zona_registro_amie', 'zona_cruce_geografico', 'codigo_distrito'], changes)
    print(json.dumps({'periodo': period, 'instituciones': len(rows), 'con_distrito': len(rows) - len(pending), 'pendientes': len(pending), 'zonas_actualizadas': len(changes)}, ensure_ascii=False))


if __name__ == '__main__':
    main()
