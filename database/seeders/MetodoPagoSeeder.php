<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('metodos_pago')->insert([
            [
                'nombre'      => 'Efectivo',
                'descripcion' => 'Pago en efectivo al momento de la entrega o en caja.',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Tarjeta de Crédito',
                'descripcion' => 'Pago con tarjeta de crédito Visa, Mastercard o American Express.',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Tarjeta de Débito',
                'descripcion' => 'Pago con tarjeta débito directamente desde cuenta bancaria.',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Nequi',
                'descripcion' => 'Pago digital a través de la billetera virtual Nequi.',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Daviplata',
                'descripcion' => 'Pago digital a través de la billetera virtual Daviplata.',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'PSE',
                'descripcion' => 'Pago electrónico mediante débito a cuenta bancaria por PSE.',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Transferencia Bancaria',
                'descripcion' => 'Transferencia directa entre cuentas bancarias.',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nombre'      => 'Bono / Vale',
                'descripcion' => 'Pago mediante bonos o vales de regalo del restaurante.',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
