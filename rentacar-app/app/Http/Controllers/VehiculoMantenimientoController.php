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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('vehiculo', fn($q2) => $q2->where('tipo', 'ilike', "%{$search}%"))
                  ->orWhereHas('mantenimiento', fn($q2) => $q2->where('nombre', 'ilike', "%{$search}%"))
                  ->orWhere('fecha', 'ilike', "%{$search}%")
                  ->orWhere('monto', 'ilike', "%{$search}%");
            });
        }

        $registros = $query->orderBy('fecha', 'desc')->get();

        return Inertia::render('VehiculoMantenimiento/Index', [
            'registros' => $registros,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        $vehiculos = Vehiculo::orderBy('tipo')->get();
        $mantenimientos = Mantenimiento::orderBy('nombre')->get();

        return Inertia::render('VehiculoMantenimiento/Create', [
            'vehiculos' => $vehiculos,
            'mantenimientos' => $mantenimientos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'monto' => 'required|numeric',
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'mantenimiento_id' => 'required|exists:mantenimientos,id',
        ]);

        VehiculoMantenimiento::create($validated);

        return redirect()->route('registro-mantenimientos.index')
            ->with('success', 'Registro de mantenimiento creado correctamente');
    }

    public function show($id)
    {
        $registro = VehiculoMantenimiento::with(['vehiculo', 'mantenimiento'])->findOrFail($id);

        return Inertia::render('VehiculoMantenimiento/Show', [
            'registro' => $registro,
        ]);
    }

    public function edit($id)
    {
        $registro = VehiculoMantenimiento::findOrFail($id);
        $vehiculos = Vehiculo::orderBy('tipo')->get();
        $mantenimientos = Mantenimiento::orderBy('nombre')->get();

        return Inertia::render('VehiculoMantenimiento/Edit', [
            'registro' => $registro,
            'vehiculos' => $vehiculos,
            'mantenimientos' => $mantenimientos,
        ]);
    }

    public function update(Request $request, $id)
    {
        $registro = VehiculoMantenimiento::findOrFail($id);

        $validated = $request->validate([
            'fecha' => 'required|date',
            'monto' => 'required|numeric',
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'mantenimiento_id' => 'required|exists:mantenimientos,id',
        ]);

        $registro->update($validated);

        return redirect()->route('registro-mantenimientos.index')
            ->with('success', 'Registro de mantenimiento actualizado correctamente');
    }

    public function destroy($id)
    {
        $registro = VehiculoMantenimiento::findOrFail($id);
        $registro->delete();

        return redirect()->route('registro-mantenimientos.index')
            ->with('success', 'Registro de mantenimiento eliminado correctamente');
    }
}
