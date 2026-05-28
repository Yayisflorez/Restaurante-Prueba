<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estados')->insert([
            ['nombre' => 'Pendiente',   'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Confirmado',  'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'En proceso',  'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Completado',  'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cancelado',   'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Rechazado',   'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
