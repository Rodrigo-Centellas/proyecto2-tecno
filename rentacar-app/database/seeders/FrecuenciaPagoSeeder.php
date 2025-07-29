<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FrecuenciaPago;

class FrecuenciaPagoSeeder extends Seeder
{
    public function run()
    {
        $frecuencias = [
            [
                'nombre' => 'Diario',
                'frecuencia_dias' => 1,
            ],
            [
                'nombre' => 'Semanal',
                'frecuencia_dias' => 7,
            ],
            [
                'nombre' => 'Quincenal',
                'frecuencia_dias' => 15,
            ],
            [
                'nombre' => 'Mensual',
                'frecuencia_dias' => 30,
            ],
            [
                'nombre' => 'Bimestral',
                'frecuencia_dias' => 60,
            ],
            [
                'nombre' => 'Trimestral',
                'frecuencia_dias' => 90,
            ],
        ];

        foreach ($frecuencias as $frecuencia) {
            FrecuenciaPago::create($frecuencia);
        }
    }
}