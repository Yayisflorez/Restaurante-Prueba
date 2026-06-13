<?php

namespace App\Providers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('users')) {
            User::firstOrCreate(
                ['email' => 'admin.prueba@admin.com'],
                [
                    'name' => 'Admin',
                    'lastname' => 'Administrador',
                    'telefono' => null,
                    'tipo_documento_id' => null,
                    'numero_documento' => null,
                    'password' => Hash::make('Admin123456'),
                    'rol' => 'admin',
                ]
            );
        }
    }
}
