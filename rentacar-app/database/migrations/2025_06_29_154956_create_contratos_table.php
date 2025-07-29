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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
             $table->string('estado');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->foreignId('frecuencia_pago_id')->constrained('frecuencia_pagos');
            $table->foreignId('nro_cuenta_id')->constrained('nro_cuentas');
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
