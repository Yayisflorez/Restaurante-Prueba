<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_documentos')->insert([
            ['sigla' => 'CC',  'nombre' => 'Cédula de Ciudadanía',       'created_at' => now(), 'updated_at' => now()],
            ['sigla' => 'TI',  'nombre' => 'Tarjeta de Identidad',       'created_at' => now(), 'updated_at' => now()],
            ['sigla' => 'CE',  'nombre' => 'Cédula de Extranjería',      'created_at' => now(), 'updated_at' => now()],
            ['sigla' => 'PA',  'nombre' => 'Pasaporte',                  'created_at' => now(), 'updated_at' => now()],
            ['sigla' => 'NIT', 'nombre' => 'Número de Identificación Tributaria', 'created_at' => now(), 'updated_at' => now()],
            ['sigla' => 'RC',  'nombre' => 'Registro Civil',             'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
