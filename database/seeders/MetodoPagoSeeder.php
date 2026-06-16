<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('metodos_pago')->delete();
        DB::table('metodos_pago')->insert([
            [
                'nombre'      => 'Efectivo',
                'descripcion' => 'Pago en efectivo al momento de la entrega o en caja.',
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
                'nombre'      => 'Transferencia bancaria Bancolombia',
                'descripcion' => 'Transferencia directa a cuenta Bancolombia.',
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
