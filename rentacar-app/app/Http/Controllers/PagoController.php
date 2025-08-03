<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\User;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // si no está al tope del archivo
use Inertia\Inertia;

class PagoController extends Controller
{
    /**
     * Mostrar listado de pagos (simplificado)
     */
public function index(Request $request)
{
    $search = $request->input('search');
    $metodoPago = $request->input('metodo_pago');
    $user = auth()->user();

    $pagosQuery = Pago::with([
        'reserva.user',
        'reserva.vehiculo',
        'contrato.users',
        'contrato.vehiculo',
    ]);

    // Si es cliente, primero restringir a sus pagos (independientemente de search)
    if ($user->hasRole('Cliente')) {
        $pagosQuery->where(function ($q) {
            $q->whereHas('contrato.users', function ($q2) {
                $q2->where('user_id', auth()->id());
            })
            ->orWhereHas('reserva', function ($q3) {
                $q3->where('user_id', auth()->id());
            });
        });
    }

    // Búsqueda global (solo afecta el conjunto ya restringido si es cliente)
    if ($search) {
        $pagosQuery->where(function ($q) use ($search) {
            $q->where('id', 'like', "%{$search}%")
              ->orWhereHas('reserva.user', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
              })
              ->orWhereHas('reserva.vehiculo', function ($q2) use ($search) {
                  $q2->where('placa', 'like', "%{$search}%");
              })
              ->orWhereHas('contrato.users', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
              })
              ->orWhereHas('contrato.vehiculo', function ($q2) use ($search) {
                  $q2->where('placa', 'like', "%{$search}%");
              });
        });
    }

    // Filtro de método de pago
    if ($metodoPago) {
        $pagosQuery->where('metodo_pago', $metodoPago);
    }

    $pagos = $pagosQuery
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($pago) {
            $pagoData = $pago->toArray();
            $pagoData['metodo_pago'] = $pago->metodo_pago ?? Pago::METODO_EFECTIVO;

            if ($pago->reserva && !$pago->contrato) {
                $pagoData['contrato'] = [
                    'users' => [[
                        'name' => $pago->reserva->user->name ?? 'Usuario Desconocido',
                        'email' => $pago->reserva->user->email ?? '',
                        'apellido' => $pago->reserva->user->apellido ?? ''
                    ]],
                    'vehiculo' => [
                        'placa' => $pago->reserva->vehiculo->placa ?? 'Placa Desconocida',
                        'tipo' => $pago->reserva->vehiculo->tipo ?? '',
                        'marca' => $pago->reserva->vehiculo->marca ?? '',
                        'modelo' => $pago->reserva->vehiculo->modelo ?? ''
                    ]
                ];
            }

            if ($pago->contrato && (!$pago->contrato->users || $pago->contrato->users->isEmpty())) {
                $pagoData['contrato']['users'] = [[
                    'name' => 'Usuario Desconocido',
                    'email' => '',
                    'apellido' => ''
                ]];
            }

            if ($pago->contrato && !$pago->contrato->vehiculo) {
                $pagoData['contrato']['vehiculo'] = [
                    'placa' => 'Vehículo Desconocido',
                    'tipo' => '',
                    'marca' => '',
                    'modelo' => ''
                ];
            }

            if (!$pago->contrato && !$pago->reserva) {
                $pagoData['contrato'] = [
                    'users' => [[
                        'name' => 'Cliente Desconocido',
                        'email' => '',
                        'apellido' => ''
                    ]],
                    'vehiculo' => [
                        'placa' => 'Vehículo Desconocido',
                        'tipo' => '',
                        'marca' => '',
                        'modelo' => ''
                    ]
                ];
            }

            return $pagoData;
        });

    return Inertia::render('Pagos/Index', [
        'pagos' => $pagos,
        'filters' => [
            'search' => $search,
            'metodo_pago' => $metodoPago,
        ],
        'userRole' => $user->getRoleNames()->first(),
    ]);
}

    /**
     * Mostrar detalle de un pago específico
     */
    public function show($id)
    {
        $pago = Pago::with([
            'reserva.user',
            'reserva.vehiculo',
            'contrato.users',
            'contrato.vehiculo',
        ])->findOrFail($id);

        $user = auth()->user();
        if ($user->hasRole('Cliente')) {
            $tieneAcceso = false;

            if ($pago->contrato && $pago->contrato->users()->where('user_id', $user->id)->exists()) {
                $tieneAcceso = true;
            }

            if ($pago->reserva && $pago->reserva->user_id == $user->id) {
                $tieneAcceso = true;
            }

            if (!$tieneAcceso) {
                abort(403, 'No tiene permisos para ver este pago');
            }
        }

        $pagoData = $pago->toArray();
        $pagoData['metodo_pago'] = $pago->metodo_pago ?? Pago::METODO_EFECTIVO;

        if ($pago->reserva && !$pago->contrato) {
            $pagoData['contrato'] = [
                'users' => [[
                    'name' => $pago->reserva->user->name ?? 'Usuario Desconocido',
                    'email' => $pago->reserva->user->email ?? '',
                    'apellido' => $pago->reserva->user->apellido ?? ''
                ]],
                'vehiculo' => [
                    'placa' => $pago->reserva->vehiculo->placa ?? 'Placa Desconocida',
                    'tipo' => $pago->reserva->vehiculo->tipo ?? '',
                    'marca' => $pago->reserva->vehiculo->marca ?? '',
                    'modelo' => $pago->reserva->vehiculo->modelo ?? ''
                ]
            ];
        }

        return Inertia::render('Pagos/Show', [
            'pago' => $pagoData,
        ]);
    }

    /**
     * Mostrar vista específica para realizar pago
     */
    public function pagar(Request $request, $id)
    {

        Log::info('Iniciando proceso de pago', ['pago_id' => $id]);
        $pago = Pago::with([
            'reserva.user',
            'reserva.vehiculo',
            'contrato.users',
            'contrato.vehiculo',
        ])->findOrFail($id);

        if ($pago->estado !== 'pendiente' && $request->input('metodo_pago') !== 'qr') {
            return redirect()->route('pagos.show', $id)
                ->with('error', 'Este pago ya no está disponible para procesar.');
        }

        $user = auth()->user();
        if ($user->hasRole('Cliente')) {
            $tieneAcceso = false;

            if ($pago->contrato && $pago->contrato->users()->where('user_id', $user->id)->exists()) {
                $tieneAcceso = true;
            }

            if ($pago->reserva && $pago->reserva->user_id == $user->id) {
                $tieneAcceso = true;
            }

            if (!$tieneAcceso) {
                abort(403, 'No tiene permisos para realizar este pago');
            }
        }

        $metodo = $request->input('metodo_pago', $pago->metodo_pago ?? Pago::METODO_EFECTIVO);
        Log::info('Método de pago seleccionado', ['metodo_pago' => $metodo]);
        if ($metodo === 'efectivo') {
            if ($pago->estado === 'pagado') {
                return redirect()->route('pagos.show', $id)
                    ->with('error', 'El pago ya fue realizado');
            }

            DB::transaction(function () use ($pago) {
                $pago->metodo_pago = 'efectivo';
                $pago->estado = 'pagado';
                $pago->pagofacil_transaction_id = null;
                $pago->updated_at = now();
                $pago->save();

                try {
                    Notificacion::crearNotificacionPago($pago, 'pago_exitoso');
                } catch (\Throwable $e) {
                    Log::error('Error creando notificación por pago en efectivo', [
                        'pago_id' => $pago->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

            $reserva1 = $pago->reserva;
            if( $reserva1) {
                Log::info('Actualizando estado de reserva a pagado', [
                    'reserva_id' => $reserva1->id,
                    'pago_id' => $pago->id,
                ]);
                $reserva1->estado = 'pagado';
                $reserva1->save();
            }
            

            return redirect()->route('pagos.show', $id)
                ->with('success', 'Pago en efectivo registrado correctamente');
        }

        // QR path: preparar datos para vista (el frontend hará request separado para generar QR)
        $pagoData = $pago->toArray();
        $pagoData['metodo_pago'] = 'qr';
        if ($pago->reserva && !$pago->contrato) {
            $pagoData['contrato'] = [
                'users' => [[
                    'name' => $pago->reserva->user->name ?? 'Usuario Desconocido',
                    'email' => $pago->reserva->user->email ?? '',
                    'apellido' => $pago->reserva->user->apellido ?? ''
                ]],
                'vehiculo' => [
                    'placa' => $pago->reserva->vehiculo->placa ?? 'Placa Desconocida',
                    'tipo' => $pago->reserva->vehiculo->tipo ?? '',
                    'marca' => $pago->reserva->vehiculo->marca ?? '',
                    'modelo' => $pago->reserva->vehiculo->modelo ?? ''
                ]
            ];

            $reserva1 = $pago->reserva;
            if ($reserva1) {
                Log::info('Actualizando estado de reserva a pendiente', [
                    'reserva_id' => $reserva1->id,
                    'pago_id' => $pago->id,
                ]);
                $reserva1->estado = 'pagado';
                $reserva1->save();
            }
        }

        return Inertia::render('Pagos/Pagar', [
            'pago' => $pagoData,
        ]);
    }


    /**
     * Registrar pago en efectivo (usa mismo modelo, marca como pagado)
     */


    /**
     * Actualizar (por ejemplo método de pago u otros campos)
     */
  public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $validated = $request->validate([
            'metodo_pago' => 'sometimes|in:qr,efectivo',
            'estado' => 'sometimes|string',
        ]);

        if (isset($validated['metodo_pago'])) {
            $pago->metodo_pago = $validated['metodo_pago'];
        }

        if (isset($validated['estado'])) {
            $pago->estado = $validated['estado'];
        }

        $pago->save();

        return redirect()->back()->with('success', 'Pago actualizado correctamente');
    }
    public function pagarEfectivo(Request $request, $id)
    {
        $pago = Pago::with([
            'reserva.user',
            'reserva.vehiculo',
            'contrato.users',
            'contrato.vehiculo',
        ])->findOrFail($id);

        if ($pago->estado !== 'pendiente') {
            $msg = 'Este pago ya no está disponible para procesar.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return redirect()->route('pagos.show', $id)->with('error', $msg);
        }

        $user = auth()->user();
        if ($user->hasRole('Cliente')) {
            $tieneAcceso = false;
            if ($pago->contrato && $pago->contrato->users()->where('user_id', $user->id)->exists()) {
                $tieneAcceso = true;
            }
            if ($pago->reserva && $pago->reserva->user_id == $user->id) {
                $tieneAcceso = true;
            }
            if (!$tieneAcceso) {
                abort(403, 'No tiene permisos para realizar este pago');
            }
        }

        DB::transaction(function () use ($pago) {
            $pago->metodo_pago = 'efectivo';
            $pago->estado = 'pagado';
            $pago->pagofacil_transaction_id = null;
            $pago->updated_at = now();
            $pago->save();

            try {
                Notificacion::crearNotificacionPago($pago, 'pago_exitoso');
            } catch (\Throwable $e) {
                Log::error('Error creando notificación por pagarEfectivo', [
                    'pago_id' => $pago->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        $message = 'Pago en efectivo registrado correctamente';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'pago' => $pago->fresh()->toArray(),
            ]);
        }

        return redirect()->route('pagos.show', $id)->with('success', $message);
    }
}
