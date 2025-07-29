<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehiculo;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehiculos = [
            ['placa'=>'5464-ASG','marca'=>'Suzuki','estado' => 'Disponible', 'monto_garantia' => 300, 'precio_dia' => 50, 'tipo' => 'Sedán'],
            ['placa'=>'5464-NEB','marca'=>'Suzuki','estado' => 'Disponible', 'monto_garantia' => 400, 'precio_dia' => 65, 'tipo' => 'SUV'],
            ['placa'=>'5464-DSR','marca'=>'Suzuki','estado' => 'Disponible', 'monto_garantia' => 500, 'precio_dia' => 80, 'tipo' => 'Pickup'],
            ['placa'=>'5464-FJF','marca'=>'Suzuki','estado' => 'Disponible', 'monto_garantia' => 250, 'precio_dia' => 40, 'tipo' => 'Hatchback'],
            ['placa'=>'5464-AJY','marca'=>'Suzuki','estado' => 'Disponible', 'monto_garantia' => 600, 'precio_dia' => 90, 'tipo' => 'Convertible'],
            ['placa'=>'2060-ASD','marca'=>'Suzuki','estado' => 'Disponible', 'monto_garantia' => 350, 'precio_dia' => 55, 'tipo' => 'Compacto'],
            ['placa'=>'1070-ASD','marca'=>'Suzuki','estado' => 'Disponible', 'monto_garantia' => 700, 'precio_dia' => 100, 'tipo' => 'Van'],
            ['placa'=>'2080-ASD','marca'=>'Suzuki','estado' => 'Disponible', 'monto_garantia' => 300, 'precio_dia' => 45, 'tipo' => 'Sedán'],
            ['placa'=>'6090-ASD','marca'=>'Suzuki','estado' => 'Disponible', 'monto_garantia' => 450, 'precio_dia' => 75, 'tipo' => 'SUV'],
            ['placa'=>'4100-ASD','marca'=>'Suzuki','estado' => 'Disponible', 'monto_garantia' => 500, 'precio_dia' => 85, 'tipo' => 'Pickup'],
        ];

        foreach ($vehiculos as $v) {
            Vehiculo::create($v);
        }
    }
}
