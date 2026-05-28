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
        Schema::table('pagos', function (Blueprint $table) {
            // Eliminar la columna string original
            $table->dropColumn('metodo_pago');

            // Agregar la FK a metodos_pago
            $table->unsignedBigInteger('metodo_pago_id')->nullable()->after('pedido_id');
            $table->foreign('metodo_pago_id')
                  ->references('id')
                  ->on('metodos_pago')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['metodo_pago_id']);
            $table->dropColumn('metodo_pago_id');
            $table->string('metodo_pago')->after('pedido_id');
        });
    }
};
