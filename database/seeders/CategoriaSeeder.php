<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categorias')->insert([
            [
                'nombre'      => 'Entradas',
                'descripcion' => 'Platos ligeros para comenzar la experiencia gastronómica.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Sopas y Cremas',
                'descripcion' => 'Caldos, sopas tradicionales y cremas artesanales.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Platos Fuertes',
                'descripcion' => 'Platos principales con proteínas, acompañamientos y salsas.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Mariscos y Pescados',
                'descripcion' => 'Especialidades del mar, frescos y preparados al momento.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Carnes a la Parrilla',
                'descripcion' => 'Cortes selectos preparados a las brasas.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Pastas y Arroces',
                'descripcion' => 'Pastas artesanales y arroces con ingredientes frescos.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Ensaladas',
                'descripcion' => 'Opciones frescas y saludables con ingredientes de temporada.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Postres',
                'descripcion' => 'Dulces creaciones para finalizar con el mejor sabor.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Bebidas',
                'descripcion' => 'Jugos naturales, gaseosas, aguas y bebidas especiales.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Menú del Día',
                'descripcion' => 'Combinaciones especiales a precio especial disponibles cada día.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
