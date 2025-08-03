<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mantenimiento;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class MantenimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $datos = [
            [
                'nombre' => 'Cambio de aceite',
                'descripcion' => 'Reemplazo de aceite del motor y filtro de aceite para garantizar lubricación adecuada.',
            ],
            [
                'nombre' => 'Revisión de frenos',
                'descripcion' => 'Inspección y ajuste de pastillas, discos y nivel de líquido de frenos.',
            ],
            [
                'nombre' => 'Alineación y balanceo',
                'descripcion' => 'Alineación de ruedas y balanceo de llantas para evitar desgaste irregular.',
            ],
            [
                'nombre' => 'Cambio de filtro de aire',
                'descripcion' => 'Sustitución del filtro de aire para mejorar la combustión y el rendimiento.',
            ],
            [
                'nombre' => 'Revisión de suspensión',
                'descripcion' => 'Chequeo de amortiguadores, resortes y componentes de la suspensión.',
            ],
            [
                'nombre' => 'Revisión de batería',
                'descripcion' => 'Verificación del estado de la batería, terminales y sistema de carga.',
            ],
            [
                'nombre' => 'Cambio de bujías',
                'descripcion' => 'Reemplazo de bujías para asegurar encendido eficiente del motor.',
            ],
            [
                'nombre' => 'Revisión de correa de distribución',
                'descripcion' => 'Inspección o reemplazo de la correa de distribución según kilometraje.',
            ],
            [
                'nombre' => 'Servicio de climatización',
                'descripcion' => 'Revisión y recarga del sistema de aire acondicionado y filtrado.',
            ],
            [
                'nombre' => 'Inspección general',
                'descripcion' => 'Chequeo completo de sistemas básicos: luces, neumáticos, niveles y seguridad.',
            ],
        ];

        foreach ($datos as $item) {
            Mantenimiento::create([
                'nombre' => $item['nombre'],
                'descripcion' => $item['descripcion'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
