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
        Schema::create('vehiculo_mantenimientos', function (Blueprint $table) {
            $table->id();
             $table->date('fecha');
            $table->string('monto');
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->foreignId('mantenimiento_id')->constrained('mantenimientos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculo_mantenimientos');
    }
};
