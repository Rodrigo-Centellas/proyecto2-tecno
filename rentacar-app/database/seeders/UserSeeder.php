<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles CON LOS NOMBRES CORRECTOS
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $empleadoRole = Role::firstOrCreate(['name' => 'Empleado Operativo']); // ← CORREGIDO
        $clienteRole = Role::firstOrCreate(['name' => 'Cliente']);

        // Crear permisos por caso de uso
        $permisos = [
            // CU1. Gestión de Usuarios
            'usuarios.ver_todos',
            'usuarios.crear_cliente',
            'usuarios.editar_cliente',
            'usuarios.ver_cliente',
            'usuarios.editar_propio_perfil',

            // CU2. Gestión de Vehículos
            'vehiculos.crear',
            'vehiculos.editar',
            'vehiculos.eliminar',
            'vehiculos.mostrar',
            'vehiculos.index',


            // CU3. Gestión de Servicios (Contratos)
            'contratos.ver_todos',
            'contratos.crear',
            'contratos.editar',
            'contratos.eliminar',


            // CU4. Gestión de Mantenimientos
            'mantenimientos_Tipo.crear',
            'mantenimientos_Tipo.editar',
            'mantenimientos_Tipo.eliminar',
            'mantenimientos_Tipo.ver',
            //REGISTRO DE MANTEMIENTO
            'mantenimientos.crear',
            'mantenimientos.editar',
            'mantenimientos.eliminar',
            'mantenimientos.ver',

            // CU5. Gestión de Reservas
            'reservas.ver',
            'reservas.crear',
            'reservas.editar',
            'reservas.eliminar',


            // CU6. Gestión de Notificaciones
            'notificaciones.ver',
            'notificaciones.gestionar_plantillas',
            'notificaciones.configuracion',
            'notificaciones.ver_vencimientos',
            'notificaciones.recibir_vencimientos',
            'notificaciones.recibir_pagos_atrasados',

            // CU7. Gestión de Pagos
            'pagos.ver',
            'pagos.crear',
            'pagos.editar',
            'pagos.eliminar',

            // CU8. Reportes y Estadísticas
            'reportes.pagos',
        ];

        // Crear todos los permisos
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // Asignar permisos al rol ADMINISTRADOR (todos los permisos)
        $adminRole->syncPermissions(Permission::all());

        // Asignar permisos al rol EMPLEADO OPERATIVO
        $empleadoPermisos = [
            // Usuarios - limitado
            'usuarios.ver_cliente',
            'usuarios.crear_cliente',

            // Vehículos - solo lectura
            'vehiculos.crear',
            'vehiculos.editar',
            'vehiculos.mostrar',
            'vehiculos.index',
            

            // Contratos - crear y gestionar, no eliminar cerrados
            'contratos.ver_todos',
            'contratos.crear',
            

            // Mantenimientos - limitado
            'mantenimientos_Tipo.crear',
            'mantenimientos_Tipo.editar',
            'mantenimientos_Tipo.eliminar',
            'mantenimientos_Tipo.ver',
            'mantenimientos.crear',
            'mantenimientos.editar',
            'mantenimientos.eliminar',
            'mantenimientos.ver',

            // Reservas - gestión completa
            'reservas.ver',
            'reservas.crear',
            'reservas.editar',
            'reservas.eliminar',

            // Notificaciones - solo ver
            'notificaciones.ver_vencimientos',

            // Pagos - registrar y ver
            'pagos.ver',
            'pagos.crear',
            

            // Reportes - básicos
            'reportes.pagos',

        ];
        $empleadoRole->syncPermissions($empleadoPermisos);

        // Asignar permisos al rol CLIENTE
        $clientePermisos = [
            // Usuarios - solo su perfil
            'usuarios.editar_propio_perfil',

            // Vehículos - solo alquilables
            'vehiculos.mostrar',


            // Contratos - solo los suyos
            'contratos.ver_todos',

            // Mantenimientos - opcional
            'mantenimientos_Tipo.ver',
            'mantenimientos_Tipo.crear',

            // Reservas - sus propias reservas
            'reservas.crear',
            'reservas.ver',
            

            // Notificaciones - recibir
            'notificaciones.recibir_vencimientos',
            'notificaciones.recibir_pagos_atrasados',

            // Pagos - ver y subir comprobantes
            'pagos.ver',
            'pagos.crear',


            //reporte de pagos
            'reportes.pagos',
        ];
        $clienteRole->syncPermissions($clientePermisos);

        // Crear usuarios

        // Administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrador',
                'apellido' => 'Sistema',
                'ci' => 12345678,
                'domicilio' => 'Oficina Central',
                'telefono' => 71234567,
                'password' => Hash::make('123'),
            ]
        );
        $admin->assignRole($adminRole);

        // Empleado Operativo
        $empleado = User::firstOrCreate(
            ['email' => 'secretaria@gmail.com'],
            [
                'name' => 'María',
                'apellido' => 'González',
                'ci' => 87654321,
                'domicilio' => 'Zona Central',
                'telefono' => 76543210,
                'password' => Hash::make('123'),
            ]
        );
        $empleado->assignRole($empleadoRole);

        // Cliente de ejemplo
        $cliente = User::firstOrCreate(
            ['email' => 'cliente@gmail.com'],
            [
                'name' => 'Juan',
                'apellido' => 'Pérez',
                'ci' => 11223344,
                'domicilio' => 'Calle Principal 123',
                'telefono' => 78901234,
                'password' => Hash::make('123'),
            ]
        );
        $cliente->assignRole($clienteRole);

        echo "✅ Roles y permisos creados exitosamente\n";
        echo "📋 Administrador: admin@gmail.com / 123\n";
        echo "👥 Empleado: secretaria@gmail.com / 123\n";
        echo "🙋 Cliente: cliente@gmail.com / 123\n";
    }
}