<?php

namespace App\Services\Minedec;

use App\Models\Distrito;
use App\Models\Institucion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ImportInstituciones
{
    public const COLUMNS = ['codigo_amie', 'nombre', 'codigo_distrito', 'zona_codigo', 'provincia', 'canton', 'parroquia', 'sostenimiento', 'regimen', 'periodo'];

    public function run(string $path, bool $dryRun = false): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('No se pudo leer el archivo de instituciones.');
        }

        $distritos = Distrito::query()->with('zona')->get()->keyBy('codigo');
        $sostenimientos = DB::table('cat_sostenimiento')->pluck('id', 'nombre');
        $regimenes = DB::table('cat_regimen_escolar')->pluck('id', 'nombre');
        $records = [];
        $sinDistrito = [];
        $periodos = [];
        $line = 1;
        try {
            $header = fgetcsv($handle, 0, ',', '"', '');
            if ($header) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            }
            if ($header !== self::COLUMNS) {
                throw new RuntimeException('Las columnas del archivo no corresponden al catálogo normalizado del Minedec.');
            }
            while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                $line++;
                if (count($values) !== count(self::COLUMNS)) {
                    throw new RuntimeException('Columnas incompletas en la línea '.$line.'.');
                }
                $row = array_combine(self::COLUMNS, array_map('trim', $values));
                $row['codigo_amie'] = strtoupper($row['codigo_amie']);
                $row['codigo_distrito'] = strtoupper($row['codigo_distrito']);
                $validator = Validator::make($row, [
                    'codigo_amie' => ['required', 'string', 'max:20', 'regex:/^[0-9]{2}[A-Z][0-9]{5}$/'],
                    'nombre' => ['required', 'string', 'max:255'],
                    'codigo_distrito' => ['nullable', 'regex:/^[0-9]{2}D[0-9]{2}$/'],
                    'zona_codigo' => ['required', 'string'],
                    'provincia' => ['required', 'string', 'max:120'],
                    'canton' => ['required', 'string', 'max:120'],
                    'parroquia' => ['nullable', 'string', 'max:120'],
                    'periodo' => ['required', 'string', 'max:50'],
                ]);
                if ($validator->fails()) {
                    throw new RuntimeException('Datos inválidos en la línea '.$line.': '.$validator->errors()->first());
                }
                if (isset($records[$row['codigo_amie']])) {
                    throw new RuntimeException('Código AMIE repetido en la línea '.$line.': '.$row['codigo_amie']);
                }
                if (! isset($sostenimientos[$row['sostenimiento']], $regimenes[$row['regimen']])) {
                    throw new RuntimeException('Sostenimiento o régimen desconocido en la línea '.$line.'.');
                }
                $distrito = $row['codigo_distrito'] !== '' ? $distritos->get($row['codigo_distrito']) : null;
                if ($row['codigo_distrito'] !== '' && (! $distrito || (string) $distrito->zona->codigo !== $row['zona_codigo'])) {
                    throw new RuntimeException('Distrito inexistente o zona inconsistente en la línea '.$line.'.');
                }
                if (! $distrito) {
                    $sinDistrito[] = $row['codigo_amie'];
                }
                $records[$row['codigo_amie']] = [
                    'codigo_amie' => $row['codigo_amie'],
                    'nombre' => $row['nombre'],
                    'distrito_id' => $distrito?->id,
                    'sostenimiento_id' => $sostenimientos[$row['sostenimiento']],
                    'regimen_id' => $regimenes[$row['regimen']],
                    'provincia' => $row['provincia'],
                    'canton' => $row['canton'],
                    'parroquia' => $row['parroquia'] ?: null,
                ];
                $periodos[$row['periodo']] = true;
            }
        } finally {
            fclose($handle);
        }
        if ($records === [] || count($periodos) !== 1) {
            throw new RuntimeException('El archivo debe contener instituciones de un único período lectivo.');
        }

        $existing = Institucion::query()->whereIn('codigo_amie', array_keys($records))->get()->keyBy('codigo_amie');
        $nuevas = 0;
        $actualizadas = 0;
        $sinCambios = 0;
        $batches = [];
        $now = now()->toDateTimeString();
        foreach ($records as $codigo => $record) {
            $institucion = $existing->get($codigo);
            if ($institucion) {
                if ($record['distrito_id'] === null && $institucion->distrito_id !== null) {
                    $record['distrito_id'] = $institucion->distrito_id;
                }
                $institucion->fill($record);
                if (! $institucion->isDirty()) {
                    $sinCambios++;

                    continue;
                }
                $actualizadas++;
            } else {
                $nuevas++;
            }
            $batches[] = $record + [
                'estado' => $institucion?->estado ?? 'ACTIVO',
                'created_at' => $institucion?->created_at?->toDateTimeString() ?? $now,
                'updated_at' => $now,
            ];
        }
        if (! $dryRun) {
            DB::transaction(function () use ($batches) {
                foreach (array_chunk($batches, 200) as $batch) {
                    Institucion::query()->upsert($batch, ['codigo_amie'], [
                        'nombre', 'distrito_id', 'sostenimiento_id', 'regimen_id', 'provincia', 'canton', 'parroquia', 'updated_at',
                    ]);
                }
            });
        }

        return [
            'periodo' => array_key_first($periodos),
            'registros' => count($records),
            'nuevas' => $nuevas,
            'actualizadas' => $actualizadas,
            'sin_cambios' => $sinCambios,
            'sin_distrito_en_fuente' => $sinDistrito,
            'simulacion' => $dryRun,
        ];
    }
}
