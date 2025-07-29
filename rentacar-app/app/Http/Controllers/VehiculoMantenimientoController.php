<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use App\Models\VehiculoMantenimiento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehiculoMantenimientoController extends Controller
{
public function index(Request $request)
{
    $query = VehiculoMantenimiento::with(['vehiculo', 'mantenimiento']);

    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->whereHas('vehiculo', fn($q) => $q->where('tipo', 'ilike', "%$search%"))
              ->orWhereHas('mantenimiento', fn($q) => $q->where('nombre', 'ilike', "%$search%"))
              ->orWhere('fecha', 'ilike', "%$search%")
              ->orWhere('monto', '::text', 'ilike', "%$search%"); // o convertir monto a texto según BD
    }

    $registros = $query->get();

    return Inertia::render('VehiculoMantenimiento/Index', [
        'registros' => $registros,
        'filters' => $request->only('search'),
    ]);
}


    public function create()
    {
        $vehiculos = Vehiculo::all();
        $mantenimientos = Mantenimiento::all();
        return Inertia::render('VehiculoMantenimiento/Create', compact('vehiculos', 'mantenimientos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'monto' => 'required|numeric',
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'mantenimiento_id' => 'required|exists:mantenimientos,id',
        ]);

        VehiculoMantenimiento::create($request->all());

        return redirect()->route('registro-mantenimientos.index')->with('success', 'Registro creado correctamente');
    }
}
