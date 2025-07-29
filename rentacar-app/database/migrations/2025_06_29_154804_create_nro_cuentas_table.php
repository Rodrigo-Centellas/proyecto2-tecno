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
        Schema::create('nro_cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('banco');
            $table->integer('nro_cuenta');
            $table->boolean('es_activa')->default(false); // Indica si la cuenta es activa o no
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nro_cuentas');
    }
};
