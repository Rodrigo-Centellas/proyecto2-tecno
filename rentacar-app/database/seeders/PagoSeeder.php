<?php

namespace Database\Seeders;

use App\Models\Pago;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $tipos = ['QR', 'Efectivo'];
        $estados = ['Pagado', 'Pendiente'];

        for ($i = 1; $i <= 20; $i++) {
            $mes = $i;
            if ($mes > 12) $mes = $mes - 12;
            $desde = "2024-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
            $hasta = "2024-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-28";
            $fecha = "2024-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15";

            Pago::create([
                'desde' => $desde,
                'fecha' => $fecha,
                'hasta' => $hasta,
                'estado' => $estados[array_rand($estados)],
                'monto' => rand(300, 1500),
                'tipo_pago' => $tipos[array_rand($tipos)],
                'reserva_id' => rand(0, 0) ? rand(0, 0) : null, // a veces null
            ]);
        }
    }
}
