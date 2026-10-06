<?php

namespace Database\Seeders;

use App\Models\Distrito;
use App\Models\Zona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DistritoSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/mineduc/distritos-2025.csv');
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('No se pudo leer el catálogo oficial de distritos.');
        }

        $rows = [];
        try {
            $header = fgetcsv($handle, 0, ',', '"', '');
            if ($header !== ['zona_codigo', 'codigo', 'provincia', 'nombre']) {
                throw new RuntimeException('El catálogo de distritos tiene columnas incorrectas.');
            }

            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($row) !== 4 || ! preg_match('/^[1-9]$/', $row[0]) || ! preg_match('/^[0-9]{2}D[0-9]{2}$/', $row[1]) || $row[2] === '' || $row[3] === '') {
                    throw new RuntimeException('El catálogo de distritos contiene un registro inválido.');
                }
                $rows[] = array_combine($header, $row);
            }
        } finally {
            fclose($handle);
        }

        if (count($rows) !== 140 || count(array_unique(array_column($rows, 'codigo'))) !== 140) {
            throw new RuntimeException('El catálogo debe contener 140 códigos distritales únicos.');
        }

        DB::transaction(function () use ($rows) {
            $zonas = Zona::query()->pluck('id', 'codigo');
            foreach ($rows as $row) {
                if (! isset($zonas[$row['zona_codigo']])) {
                    throw new RuntimeException('Falta la zona '.$row['zona_codigo'].' para importar los distritos.');
                }
                Distrito::firstOrCreate(['codigo' => $row['codigo']], [
                    'zona_id' => $zonas[$row['zona_codigo']],
                    'nombre' => $row['nombre'],
                    'provincia' => $row['provincia'],
                ]);
            }
        });
    }
}
