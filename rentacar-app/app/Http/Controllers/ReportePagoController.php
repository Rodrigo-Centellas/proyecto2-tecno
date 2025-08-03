<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PagosExport;
use Illuminate\Support\Facades\Auth;

class ReportePagoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Usuarios para filtros
        if ($user->hasRole('Cliente')) {
            // Solo él mismo
            $usuarios = collect([
                [
                    'id' => $user->id,
                    'nombre_completo' => $user->name . ' ' . $user->apellido,
                ]
            ]);
        } else {
            $usuarios = User::select('id', 'name', 'apellido')
                ->whereHas('contratos')
                ->orWhereHas('reservas')
                ->orderBy('name')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'nombre_completo' => $user->name . ' ' . $user->apellido,
                    ];
                });
        }

        // Vehículos para filtros
        if ($user->hasRole('Cliente')) {
            // Vehículos de sus contratos o reservas
            $vehiculosQuery = Vehiculo::query()
                ->whereHas('contratos.users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orWhereHas('reservas', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });

            $vehiculos = $vehiculosQuery
                ->select('id', 'tipo', 'marca', 'modelo', 'placa')
                ->orderBy('tipo')
                ->get()
                ->map(function ($vehiculo) {
                    return [
                        'id' => $vehiculo->id,
                        'descripcion' => $vehiculo->tipo . ' - ' . $vehiculo->marca . ' ' . $vehiculo->modelo . ' (' . $vehiculo->placa . ')',
                    ];
                });
        } else {
            $vehiculos = Vehiculo::select('id', 'tipo', 'marca', 'modelo', 'placa')
                ->orderBy('tipo')
                ->get()
                ->map(function ($vehiculo) {
                    return [
                        'id' => $vehiculo->id,
                        'descripcion' => $vehiculo->tipo . ' - ' . $vehiculo->marca . ' ' . $vehiculo->modelo . ' (' . $vehiculo->placa . ')',
                    ];
                });
        }

        // Tipos de pago únicos (para filtro)
        $tiposPago = Pago::select('tipo_pago')
            ->distinct()
            ->pluck('tipo_pago')
            ->sort()
            ->values();

        return Inertia::render('Reportes/Pagos', [
            'usuarios' => $usuarios,
            'vehiculos' => $vehiculos,
            'tiposContrato' => $tiposPago, // reutiliza la prop en Vue como tiposContrato para lista de tipo_pago
            'filters' => $request->only([
                'desde', 'hasta', 'usuario_id', 'vehiculo_id',
                'tipo_pago', 'metodo_pago', 'estado'
            ])
        ]);
    }

    public function exportPdf(Request $request)
    {
        $pagos = $this->filtrarPagos($request);
        $filtros = $this->obtenerFiltrosAplicados($request);

        $pdf = PDF::loadView('reportes.pagos', compact('pagos', 'filtros'));
        return $pdf->download('reporte_pagos_' . now()->format('Y-m-d_H-i') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new PagosExport($request), 'reporte_pagos_' . now()->format('Y-m-d_H-i') . '.xlsx');
    }

    private function filtrarPagos(Request $request)
    {
        $user = Auth::user();

        $query = Pago::with([
            'contrato.vehiculo',
            'contrato.users',
            'reserva.vehiculo',
            'reserva.user'
        ]);

        // Si es cliente, restringir a sus pagos
        if ($user->hasRole('Cliente')) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('contrato.users', function ($subQ) use ($user) {
                    $subQ->where('user_id', $user->id);
                })->orWhereHas('reserva', function ($subQ) use ($user) {
                    $subQ->where('user_id', $user->id);
                });
            });
        }

        // Filtro por fechas
        if ($request->desde && $request->hasta) {
            $query->whereBetween('fecha', [$request->desde, $request->hasta]);
        } elseif ($request->desde) {
            $query->where('fecha', '>=', $request->desde);
        } elseif ($request->hasta) {
            $query->where('fecha', '<=', $request->hasta);
        }

        // Filtro por usuario
        if ($request->usuario_id) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('contrato.users', function ($subQ) use ($request) {
                    $subQ->where('user_id', $request->usuario_id);
                })->orWhereHas('reserva', function ($subQ) use ($request) {
                    $subQ->where('user_id', $request->usuario_id);
                });
            });
        }

        // Filtro por vehículo
        if ($request->vehiculo_id) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('contrato.vehiculo', function ($subQ) use ($request) {
                    $subQ->where('id', $request->vehiculo_id);
                })->orWhereHas('reserva.vehiculo', function ($subQ) use ($request) {
                    $subQ->where('id', $request->vehiculo_id);
                });
            });
        }

        // Filtro por tipo de pago
        if ($request->tipo_pago) {
            $query->where('tipo_pago', $request->tipo_pago);
        }

        // Filtro por método de pago
        if ($request->metodo_pago) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        // Filtro por estado
        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        return $query->orderBy('fecha', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($pago) {
                        return [
                            'id' => $pago->id,
                            'fecha' => $pago->fecha,
                            'monto' => $pago->monto,
                            'tipo_pago' => $pago->tipo_pago,
                            'metodo_pago' => $pago->metodo_pago ?? 'qr',
                            'estado' => $pago->estado,
                            'usuario' => $this->obtenerUsuarioPago($pago),
                            'vehiculo' => $this->obtenerVehiculoPago($pago),
                            'fuente' => $this->obtenerFuentePago($pago),
                            'transaction_id' => $pago->pagofacil_transaction_id,
                            'created_at' => $pago->created_at
                        ];
                    });
    }

    private function obtenerUsuarioPago($pago)
    {
        if ($pago->contrato && $pago->contrato->users->count() > 0) {
            $user = $pago->contrato->users->first();
            return $user->name . ' ' . $user->apellido;
        }

        if ($pago->reserva && $pago->reserva->user) {
            return $pago->reserva->user->name . ' ' . $pago->reserva->user->apellido;
        }

        return 'N/A';
    }

    private function obtenerVehiculoPago($pago)
    {
        $vehiculo = null;

        if ($pago->contrato && $pago->contrato->vehiculo) {
            $vehiculo = $pago->contrato->vehiculo;
        } elseif ($pago->reserva && $pago->reserva->vehiculo) {
            $vehiculo = $pago->reserva->vehiculo;
        }

        if ($vehiculo) {
            return $vehiculo->tipo . ' - ' . $vehiculo->marca . ' ' . $vehiculo->modelo . ' (' . $vehiculo->placa . ')';
        }

        return 'N/A';
    }

    private function obtenerFuentePago($pago)
    {
        if ($pago->contrato) {
            return 'Contrato';
        }
        if ($pago->reserva) {
            return 'Reserva';
        }
        return 'N/A';
    }

    private function obtenerFiltrosAplicados(Request $request)
    {
        $filtros = [];

        if ($request->desde) {
            $filtros['Desde'] = $request->desde;
        }

        if ($request->hasta) {
            $filtros['Hasta'] = $request->hasta;
        }

        if ($request->usuario_id) {
            $user = User::find($request->usuario_id);
            $filtros['Usuario'] = $user ? $user->name . ' ' . $user->apellido : 'N/A';
        }

        if ($request->vehiculo_id) {
            $vehiculo = Vehiculo::find($request->vehiculo_id);
            $filtros['Vehículo'] = $vehiculo ? $vehiculo->tipo . ' - ' . $vehiculo->placa : 'N/A';
        }

        if ($request->tipo_pago) {
            $filtros['Tipo de Pago'] = ucfirst($request->tipo_pago);
        }

        if ($request->metodo_pago) {
            $filtros['Método de Pago'] = ucfirst($request->metodo_pago);
        }

        if ($request->estado) {
            $filtros['Estado'] = ucfirst($request->estado);
        }

        return $filtros;
    }
}
