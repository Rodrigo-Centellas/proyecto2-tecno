<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NroCuenta;

class NroCuentaSeeder extends Seeder
{
    public function run()
    {
        $cuentas = [
            [
                'banco' => 'Banco Nacional de Bolivia',
                'nro_cuenta' => '4567890',
                'es_activa' => true, // Cuenta principal para recibir pagos
            ],
            [
                'banco' => 'Banco Mercantil Santa Cruz',
                'nro_cuenta' => '20567890',
                'es_activa' => false,
            ],
            [
                'banco' => 'Banco Económico',
                'nro_cuenta' => '30012390',
                'es_activa' => false,
            ],
            [
                'banco' => 'Banco de Crédito de Bolivia',
                'nro_cuenta' => '400190',
                'es_activa' => false,
            ],
            [
                'banco' => 'Banco Unión',
                'nro_cuenta' => '5001290',
                'es_activa' => false,
            ],
            [
                'banco' => 'Banco FIE',
                'nro_cuenta' => '6001890',
                'es_activa' => false,
            ],
            [
                'banco' => 'Banco Solidario',
                'nro_cuenta' => '7890',
                'es_activa' => false,
            ],
        ];

        foreach ($cuentas as $cuenta) {
            NroCuenta::create($cuenta);
        }
    }
}