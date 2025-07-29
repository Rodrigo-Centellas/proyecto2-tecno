<?php

// ClausulaSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clausula;

class ClausulaSeedeer extends Seeder
{
    public function run()
    {
        $clausulas = [
            [
                'descripcion' => 'El arrendatario se compromete a usar el vehículo de manera responsable y únicamente para fines legales. No está permitido el uso del vehículo para actividades comerciales sin autorización previa.',
                'activa' => true,
            ],
            [
                'descripcion' => 'Cualquier daño al vehículo durante el período de alquiler será responsabilidad total del arrendatario, quien deberá cubrir los costos de reparación según el presupuesto oficial del taller autorizado.',
                'activa' => true,
            ],
            [
                'descripcion' => 'La garantía será retenida hasta la devolución del vehículo en las mismas condiciones en que fue entregado. La garantía será devuelta en un plazo máximo de 15 días hábiles después de la devolución.',
                'activa' => true,
            ],
            [
                'descripcion' => 'El arrendatario debe mantener el vehículo en condiciones óptimas de limpieza y funcionamiento. Está prohibido fumar, comer alimentos que manchen o transportar mascotas sin autorización.',
                'activa' => true,
            ],
            [
                'descripcion' => 'En caso de accidente, el arrendatario debe notificar inmediatamente a la empresa y a las autoridades correspondientes. No se debe admitir culpabilidad ni realizar arreglos directos con terceros.',
                'activa' => true,
            ],
            [
                'descripcion' => 'El combustible es responsabilidad del arrendatario. El vehículo debe ser devuelto con el mismo nivel de combustible con el que fue entregado, caso contrario se cobrará el costo adicional.',
                'activa' => true,
            ],
            [
                'descripcion' => 'El arrendatario debe portar siempre su licencia de conducir vigente y el contrato de alquiler. En caso de infracciones de tránsito, el arrendatario será el único responsable de las multas.',
                'activa' => true,
            ],
            [
                'descripcion' => 'No está permitido conducir bajo los efectos del alcohol o sustancias psicoactivas. El incumplimiento de esta norma dará lugar a la terminación inmediata del contrato sin devolución de dinero.',
                'activa' => true,
            ],
            [
                'descripcion' => 'El vehículo no puede ser subarrendado, prestado o cedido a terceros sin autorización escrita de la empresa. Solo las personas autorizadas en el contrato pueden conducir el vehículo.',
                'activa' => true,
            ],
            [
                'descripcion' => 'Los retrasos en la devolución del vehículo generarán un cargo adicional equivalente al 150% del precio diario de alquiler por cada día de retraso o fracción.',
                'activa' => true,
            ],
            [
                'descripcion' => 'En caso de robo o pérdida total del vehículo, el arrendatario deberá cubrir el valor comercial del vehículo según tasación oficial, independientemente del monto de la garantía.',
                'activa' => true,
            ],
            [
                'descripcion' => 'La empresa se reserva el derecho de inspeccionar el vehículo en cualquier momento durante el período de alquiler, previa notificación al arrendatario.',
                'activa' => true,
            ],
            [
                'descripcion' => 'Cualquier modificación al vehículo (cambio de llantas, instalación de accesorios, etc.) debe ser autorizada previamente por escrito por la empresa.',
                'activa' => false, // Ejemplo de cláusula inactiva
            ],
            [
                'descripcion' => 'El arrendatario acepta que la empresa puede utilizar sistemas de rastreo GPS para monitorear la ubicación del vehículo por razones de seguridad.',
                'activa' => true,
            ],
            [
                'descripcion' => 'En caso de disputa, ambas partes se someten a la jurisdicción de los tribunales competentes de la ciudad donde se firmó el contrato.',
                'activa' => true,
            ],
        ];

        foreach ($clausulas as $clausula) {
            Clausula::create($clausula);
        }
    }
}