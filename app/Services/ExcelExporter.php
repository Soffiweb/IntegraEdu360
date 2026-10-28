<?php

namespace App\Services;

use RuntimeException;
use XMLWriter;
use ZipArchive;

class ExcelExporter
{
    public function export(array $headers, iterable $rows, string $sheetName = 'Docentes'): string
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('worksheet');
        $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $xml->startElement('sheetViews');
        $xml->startElement('sheetView');
        $xml->writeAttribute('workbookViewId', '0');
        $xml->startElement('pane');
        foreach (['ySplit' => '1', 'topLeftCell' => 'A2', 'activePane' => 'bottomLeft', 'state' => 'frozen'] as $key => $value) {
            $xml->writeAttribute($key, $value);
        }
        $xml->endElement();
        $xml->endElement();
        $xml->endElement();
        $xml->startElement('cols');
        $xml->startElement('col');
        foreach (['min' => '1', 'max' => (string) count($headers), 'width' => '24', 'customWidth' => '1'] as $key => $value) {
            $xml->writeAttribute($key, $value);
        }
        $xml->endElement();
        $xml->endElement();
        $xml->startElement('sheetData');
        $rowNumber = 1;
        $this->writeRow($xml, $rowNumber, $headers);
        foreach ($rows as $row) {
            $this->writeRow($xml, ++$rowNumber, $row);
        }
        $xml->endElement();
        $xml->startElement('autoFilter');
        $xml->writeAttribute('ref', 'A1:'.$this->columnName(count($headers)).$rowNumber);
        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();

        $path = tempnam(sys_get_temp_dir(), 'docentes-excel-');
        if ($path === false) {
            throw new RuntimeException('No se pudo preparar el archivo Excel.');
        }
        $zip = new ZipArchive;
        try {
            if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('No se pudo crear el archivo Excel.');
            }
            $parts = [
                '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>',
                '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
                'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.htmlspecialchars($sheetName, ENT_XML1 | ENT_QUOTES, 'UTF-8').'" sheetId="1" r:id="rId1"/></sheets></workbook>',
                'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
                'xl/worksheets/sheet1.xml' => $xml->outputMemory(),
            ];
            foreach ($parts as $name => $content) {
                if (! $zip->addFromString($name, $content)) {
                    throw new RuntimeException('No se pudo escribir el archivo Excel.');
                }
            }
            if (! $zip->close()) {
                throw new RuntimeException('No se pudo finalizar el archivo Excel.');
            }
            $content = file_get_contents($path);
            if ($content === false) {
                throw new RuntimeException('No se pudo leer el archivo Excel.');
            }

            return $content;
        } finally {
            unlink($path);
        }
    }

    private function writeRow(XMLWriter $xml, int $number, array $values): void
    {
        $xml->startElement('row');
        $xml->writeAttribute('r', (string) $number);
        foreach (array_values($values) as $index => $value) {
            $xml->startElement('c');
            $xml->writeAttribute('r', $this->columnName($index + 1).$number);
            $xml->writeAttribute('t', 'inlineStr');
            $xml->startElement('is');
            $xml->startElement('t');
            $xml->writeAttribute('xml:space', 'preserve');
            $xml->text(preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', (string) $value) ?? '');
            $xml->endElement();
            $xml->endElement();
            $xml->endElement();
        }
        $xml->endElement();
    }

    private function columnName(int $number): string
    {
        $column = '';
        while ($number > 0) {
            $number--;
            $column = chr(65 + $number % 26).$column;
            $number = intdiv($number, 26);
        }

        return $column;
    }
}
