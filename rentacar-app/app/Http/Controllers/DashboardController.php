<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Vehiculo;
use App\Models\Pago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
public function index()
{
    $reservasPorEstado = Reserva::select('estado', DB::raw('count(*) as total'))
        ->groupBy('estado')
        ->get();

    $vehiculosPorEstado = Vehiculo::select('estado', DB::raw('count(*) as total'))
        ->groupBy('estado')
        ->get();

    // ← Aquí el cambio:
    $ingresosPorMes = Pago::select(
            DB::raw("to_char(fecha, 'YYYY-MM') as mes"),
            DB::raw('SUM(monto) as total')
        )
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();

    $topVehiculos = Reserva::select('vehiculo_id', DB::raw('count(*) as total'))
        ->groupBy('vehiculo_id')
        ->orderByDesc('total')
        ->take(5)
        ->with('vehiculo:id,tipo')
        ->get();

    return Inertia::render('Dashboard', [
        'reservasPorEstado'  => $reservasPorEstado,
        'vehiculosPorEstado' => $vehiculosPorEstado,
        'ingresosPorMes'     => $ingresosPorMes,
        'topVehiculos'       => $topVehiculos,
    ]);
}
}
