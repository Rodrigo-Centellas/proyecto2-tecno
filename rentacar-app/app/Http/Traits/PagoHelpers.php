<?php

namespace App\Http\Traits;

use App\Models\Pago;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait PagoHelpers
{
    /**
     * Verificar permisos del usuario para un pago específico
     */
    protected function verificarPermisosPago($pago)
    {
        $user = Auth::user();
        
        // Administradores y empleados operativos tienen acceso total
        if ($user->hasRole(['Administrador', 'Empleado Operativo'])) {
            return true;
        }
        
        // Clientes solo pueden acceder a sus propios pagos
        if ($user->hasRole('Cliente')) {
            $tieneAcceso = false;
            
            // Verificar acceso por contrato
            if ($pago->contrato && $pago->contrato->users()->where('user_id', $user->id)->exists()) {
                $tieneAcceso = true;
            }
            
            // Verificar acceso por reserva
            if ($pago->reserva && $pago->reserva->user_id == $user->id) {
                $tieneAcceso = true;
            }
            
            if (!$tieneAcceso) {
                abort(403, 'No tiene permisos para acceder a este pago');
            }
        }
        
        return true;
    }

    /**
     * Obtener usuario asociado a un pago
     */
    protected function obtenerUsuarioPago($pago)
    {
        try {
            // Prioridad 1: Usuario del contrato
            if ($pago->contrato && $pago->contrato->users()->count() > 0) {
                return $pago->contrato->users()->first();
            }

            // Prioridad 2: Usuario de la reserva
            if ($pago->reserva && $pago->reserva->user) {
                return $pago->reserva->user;
            }

            // Fallback: Usuario autenticado
            return Auth::user();

        } catch (\Exception $e) {
            Log::error('Error obteniendo usuario del pago', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Normalizar datos de pago para las vistas frontend
     */
    protected function normalizarDatosPago($pago)
    {
        $pagoData = $pago->toArray();
        
        // Si es una reserva sin contrato, simular estructura de contrato
        if ($pago->reserva && !$pago->contrato) {
            $pagoData['contrato'] = [
                'users' => [
                    [
                        'name' => $pago->reserva->user->name ?? 'Usuario Desconocido',
                        'email' => $pago->reserva->user->email ?? '',
                        'apellido' => $pago->reserva->user->apellido ?? ''
                    ]
                ],
                'vehiculo' => [
                    'placa' => $pago->reserva->vehiculo->placa ?? 'Placa Desconocida',
                    'tipo' => $pago->reserva->vehiculo->tipo ?? '',
                    'marca' => $pago->reserva->vehiculo->marca ?? '',
                    'modelo' => $pago->reserva->vehiculo->modelo ?? ''
                ]
            ];
        }
        
        // Llenar datos faltantes de contrato
        if ($pago->contrato) {
            if (!$pago->contrato->users || $pago->contrato->users->isEmpty()) {
                $pagoData['contrato']['users'] = [
                    [
                        'name' => 'Usuario Desconocido',
                        'email' => '',
                        'apellido' => ''
                    ]
                ];
            }
            
            if (!$pago->contrato->vehiculo) {
                $pagoData['contrato']['vehiculo'] = [
                    'placa' => 'Vehículo Desconocido',
                    'tipo' => '',
                    'marca' => '',
                    'modelo' => ''
                ];
            }
        }
        
        // Si no hay ni contrato ni reserva
        if (!$pago->contrato && !$pago->reserva) {
            $pagoData['contrato'] = [
                'users' => [
                    [
                        'name' => 'Cliente Desconocido',
                        'email' => '',
                        'apellido' => ''
                    ]
                ],
                'vehiculo' => [
                    'placa' => 'Vehículo Desconocido',
                    'tipo' => '',
                    'marca' => '',
                    'modelo' => ''
                ]
            ];
        }
        
        return $pagoData;
    }

    /**
     * Obtener descripción del producto según el tipo de pago
     */
    protected function obtenerDescripcionProducto($pago)
    {
        $descripciones = [
            'contrato' => 'Pago de Contrato',
            'garantia' => 'Pago de Garantía',
            'reserva' => 'Pago de Reserva'
        ];

        return $descripciones[$pago->tipo_pago] ?? 'Pago de Servicio';
    }

    /**
     * Mapear estados de PagoFácil a estados internos
     */
    protected function mapearEstadoPagoFacil($estadoPagoFacil)
    {
        $mapeo = [
            'COMPLETADO' => 'pagado',
            'PENDIENTE' => 'procesando',
            'FALLIDO' => 'fallido',
            'VENCIDO' => 'vencido',
            'CANCELADO' => 'cancelado',
            '1' => 'pagado',           // Estado numérico completado
            '0' => 'fallido',          // Estado numérico fallido
            'completed' => 'pagado',   // Estado en inglés
            'failed' => 'fallido',
            'pending' => 'procesando',
            'expired' => 'vencido',
            'cancelled' => 'cancelado'
        ];

        return $mapeo[$estadoPagoFacil] ?? 'pendiente';
    }

    /**
     * Validar datos de usuario para PagoFácil
     */
    protected function validarDatosUsuario($usuario)
    {
        $errores = [];
        
        // Validar teléfono (8 dígitos)
        $telefono = preg_replace('/[^0-9]/', '', $usuario->telefono ?? $usuario->phone ?? '');
        if (strlen($telefono) != 8) {
            $errores[] = 'El teléfono debe tener exactamente 8 dígitos';
        }
        
        // Validar email
        if (!filter_var($usuario->email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido';
        }
        
        // Validar nombre
        if (empty(trim($usuario->name))) {
            $errores[] = 'El nombre es requerido';
        }
        
        // Validar CI/NIT (opcional pero si existe debe ser numérico)
        $ciNit = preg_replace('/[^0-9]/', '', $usuario->ci ?? $usuario->nit ?? '');
        if (!empty($ciNit) && !is_numeric($ciNit)) {
            $errores[] = 'El CI/NIT debe ser numérico';
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'datos_limpios' => [
                'telefono' => strlen($telefono) == 8 ? $telefono : '70000000',
                'ci_nit' => !empty($ciNit) ? $ciNit : $telefono,
                'email' => $usuario->email,
                'nombre' => trim($usuario->name)
            ]
        ];
    }

    /**
     * Generar número de pago único
     */
    protected function generarNumeroPago($pago, $prefijo = 'PAG')
    {
        return $prefijo . '-' . $pago->id . '-' . time() . '-' . rand(100, 999);
    }

    /**
     * Determinar monto para el ambiente actual
     */
    protected function determinarMonto($pago, $montoMinimoPrueba = 0.10)
    {
        // En desarrollo, usar monto mínimo para pruebas
        if (app()->environment('local', 'development')) {
            return $montoMinimoPrueba;
        }
        
        // En producción, usar el monto real
        return floatval($pago->monto);
    }

    /**
     * Obtener URLs de callback según el ambiente
     */
    protected function obtenerUrlsCallback()
    {
        $baseUrl = url('/');
        
        // URLs por defecto
        $urlCallback = url('/api/pagos/qr/callback');
        $urlReturn = url('/pagos/confirmacion');
        
        // En desarrollo local, usar webhook.site si está configurado
        if (app()->environment('local') && env('WEBHOOK_SITE_ID')) {
            $urlCallback = "https://webhook.site/" . env('WEBHOOK_SITE_ID');
        }
        
        return [
            'callback' => $urlCallback,
            'return' => $urlReturn
        ];
    }

    /**
     * Log de actividad del pago
     */
    protected function logActividadPago($pago, $accion, $datos = [])
    {
        Log::info("Pago: {$accion}", array_merge([
            'pago_id' => $pago->id,
            'tipo_pago' => $pago->tipo_pago,
            'estado' => $pago->estado,
            'monto' => $pago->monto,
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString()
        ], $datos));
    }

    /**
     * Formatear monto para mostrar
     */
    protected function formatearMonto($monto, $moneda = 'Bs.')
    {
        return $moneda . ' ' . number_format(floatval($monto), 2, '.', ',');
    }

    /**
     * Verificar si un pago puede ser procesado
     */
    protected function puedeSerProcesado($pago)
    {
        $estadosPermitidos = ['pendiente'];
        
        if (!in_array($pago->estado, $estadosPermitidos)) {
            return [
                'puede' => false,
                'razon' => "El pago está en estado '{$pago->estado}' y no puede ser procesado"
            ];
        }
        
        // Verificar si no ha expirado (si tiene fecha límite)
        if ($pago->hasta && now()->isAfter($pago->hasta)) {
            return [
                'puede' => false,
                'razon' => 'El pago ha expirado'
            ];
        }
        
        return [
            'puede' => true,
            'razon' => null
        ];
    }
}