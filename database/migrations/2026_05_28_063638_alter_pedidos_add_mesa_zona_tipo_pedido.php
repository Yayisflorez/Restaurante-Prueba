<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('mesa', 10)->nullable()->after('user_id');
            $table->string('zona', 50)->nullable()->after('mesa');
            $table->enum('tipo_pedido', ['con_reserva', 'sin_reserva'])->default('sin_reserva')->after('zona');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('tipo_pedido');
            $table->dropColumn('zona');
            $table->dropColumn('mesa');
        });
    }
};
