<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlatoSeeder extends Seeder
{
    public function run(): void
    {
        // IDs de categoría según el orden insertado en CategoriaSeeder:
        // 1 = Entradas, 2 = Sopas y Cremas, 3 = Platos Fuertes, 4 = Mariscos y Pescados
        // 5 = Carnes a la Parrilla, 6 = Pastas y Arroces, 7 = Ensaladas
        // 8 = Postres, 9 = Bebidas, 10 = Menú del Día

        DB::table('platos')->insert([
            // ── Entradas (1)
            [
                'nombre'       => 'Patacones con Hogao',
                'descripcion'  => 'Crujientes patacones de plátano verde acompañados de hogao casero y suero costeño.',
                'precio'       => 12000.00,
                'imagen'       => null,
                'categoria_id' => 1,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Ceviche de Camarón',
                'descripcion'  => 'Camarones frescos marinados en limón con tomate, cebolla y cilantro.',
                'precio'       => 22000.00,
                'imagen'       => null,
                'categoria_id' => 1,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Tabla de Quesos y Embutidos',
                'descripcion'  => 'Selección de quesos artesanales, jamón serrano y acompañamientos.',
                'precio'       => 35000.00,
                'imagen'       => null,
                'categoria_id' => 1,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],

            // ── Sopas y Cremas (2)
            [
                'nombre'       => 'Ajiaco Bogotano',
                'descripcion'  => 'Sopa tradicional con tres tipos de papa, pollo desmenuzado, guascas y crema de leche.',
                'precio'       => 28000.00,
                'imagen'       => null,
                'categoria_id' => 2,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Sancocho de Res',
                'descripcion'  => 'Caldo espeso con costilla de res, yuca, papa y plátano.',
                'precio'       => 25000.00,
                'imagen'       => null,
                'categoria_id' => 2,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Crema de Champiñones',
                'descripcion'  => 'Crema suave de champiñones frescos con toque de nuez moscada y crutones.',
                'precio'       => 18000.00,
                'imagen'       => null,
                'categoria_id' => 2,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],

            // ── Platos Fuertes (3)
            [
                'nombre'       => 'Bandeja Paisa',
                'descripcion'  => 'Fríjoles, chicharrón, carne molida, chorizo, morcilla, huevo frito, arroz y aguacate.',
                'precio'       => 38000.00,
                'imagen'       => null,
                'categoria_id' => 3,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Pollo a la Plancha con Vegetales',
                'descripcion'  => 'Pechuga de pollo a la plancha con vegetales salteados y papa al vapor.',
                'precio'       => 28000.00,
                'imagen'       => null,
                'categoria_id' => 3,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Lomo Saltado',
                'descripcion'  => 'Tiras de lomo fino salteadas con tomate, cebolla y papas fritas al estilo peruano.',
                'precio'       => 42000.00,
                'imagen'       => null,
                'categoria_id' => 3,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],

            // ── Mariscos y Pescados (4)
            [
                'nombre'       => 'Filete de Tilapia al Limón',
                'descripcion'  => 'Filete de tilapia a la plancha con mantequilla de limón y arroz con coco.',
                'precio'       => 32000.00,
                'imagen'       => null,
                'categoria_id' => 4,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Cazuela de Mariscos',
                'descripcion'  => 'Camarones, mejillones y calamares en salsa criolla con pan artesanal.',
                'precio'       => 52000.00,
                'imagen'       => null,
                'categoria_id' => 4,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],

            // ── Carnes a la Parrilla (5)
            [
                'nombre'       => 'Churrasco Angus 300g',
                'descripcion'  => 'Corte Angus a las brasas con chimichurri, papas rústicas y ensalada.',
                'precio'       => 65000.00,
                'imagen'       => null,
                'categoria_id' => 5,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Costillas BBQ',
                'descripcion'  => 'Costillas de cerdo con salsa BBQ artesanal, maíz asado y coleslaw.',
                'precio'       => 55000.00,
                'imagen'       => null,
                'categoria_id' => 5,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],

            // ── Pastas y Arroces (6)
            [
                'nombre'       => 'Pasta Carbonara',
                'descripcion'  => 'Fettuccine con salsa cremosa, panceta crujiente y parmesano rallado.',
                'precio'       => 30000.00,
                'imagen'       => null,
                'categoria_id' => 6,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Arroz con Mariscos',
                'descripcion'  => 'Arroz al dente con camarones, calamares y almejas en salsa de azafrán.',
                'precio'       => 45000.00,
                'imagen'       => null,
                'categoria_id' => 6,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],

            // ── Ensaladas (7)
            [
                'nombre'       => 'Ensalada César',
                'descripcion'  => 'Lechuga romana, pollo a la plancha, crutones, parmesano y aderezo César.',
                'precio'       => 22000.00,
                'imagen'       => null,
                'categoria_id' => 7,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Ensalada Caprese',
                'descripcion'  => 'Tomate fresco, mozzarella, albahaca y aceite de oliva extra virgen.',
                'precio'       => 20000.00,
                'imagen'       => null,
                'categoria_id' => 7,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],

            // ── Postres (8)
            [
                'nombre'       => 'Tres Leches',
                'descripcion'  => 'Bizcocho esponjoso bañado en tres tipos de leche con crema batida.',
                'precio'       => 14000.00,
                'imagen'       => null,
                'categoria_id' => 8,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Brownie con Helado',
                'descripcion'  => 'Brownie de chocolate caliente con helado de vainilla y salsa de caramelo.',
                'precio'       => 16000.00,
                'imagen'       => null,
                'categoria_id' => 8,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Flan de Caramelo',
                'descripcion'  => 'Flan artesanal con caramelo dorado y toque de canela.',
                'precio'       => 12000.00,
                'imagen'       => null,
                'categoria_id' => 8,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],

            // ── Bebidas (9)
            [
                'nombre'       => 'Jugo Natural de Mango',
                'descripcion'  => 'Jugo fresco de mango en agua o leche sin azúcar añadida.',
                'precio'       => 8000.00,
                'imagen'       => null,
                'categoria_id' => 9,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Limonada de Coco',
                'descripcion'  => 'Limonada cremosa con leche de coco, hielo y azúcar.',
                'precio'       => 10000.00,
                'imagen'       => null,
                'categoria_id' => 9,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Agua Aromática',
                'descripcion'  => 'Infusión de hierbas (menta, jengibre o canela) caliente o fría.',
                'precio'       => 5000.00,
                'imagen'       => null,
                'categoria_id' => 9,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],

            // ── Menú del Día (10)
            [
                'nombre'       => 'Menú Ejecutivo Lunes',
                'descripcion'  => 'Sopa del día + Plato principal con proteína, arroz, ensalada y jugo.',
                'precio'       => 18000.00,
                'imagen'       => null,
                'categoria_id' => 10,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nombre'       => 'Menú Vegetariano',
                'descripcion'  => 'Sopa de verduras + Plato vegetariano con proteína vegetal, arroz integral y bebida.',
                'precio'       => 16000.00,
                'imagen'       => null,
                'categoria_id' => 10,
                'estado'       => 'disponible',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
