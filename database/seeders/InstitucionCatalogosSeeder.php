<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitucionCatalogosSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['cat_sostenimiento' => ['Fiscal', 'Fiscomisional', 'Municipal', 'Particular'], 'cat_regimen_escolar' => ['Costa', 'Sierra']] as $table => $names) {
            DB::table($table)->insertOrIgnore(array_map(fn ($name) => ['nombre' => $name], $names));
        }
    }
}
