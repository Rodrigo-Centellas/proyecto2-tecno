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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->date('desde');
            $table->date('fecha');
            $table->date('hasta');
            $table->string('estado');
            $table->string('pagofacil_transaction_id')->nullable()->after('estado');
            $table->float('monto');
            $table->string('tipo_pago');
            $table->foreignId('reserva_id')
                  ->nullable()
                  ->constrained('reservas')
                  ->cascadeOnDelete();
            $table->string('metodo_pago')->nullable()->default('ninguno')->after('tipo_pago'); // Agregado método de pago con valor por defecto 'efectivo'
            $table->index('pagofacil_transaction_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
