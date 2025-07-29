<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MantenimientoController extends Controller
{
public function index(Request $request)
{
    $search = $request->input('search');

    $mantenimientosQuery = Mantenimiento::query();

    if ($search) {
        $searchTerm = strtolower($search);
        $mantenimientosQuery->whereRaw('LOWER(nombre) LIKE ?', ["%{$searchTerm}%"])
                            ->orWhereRaw('LOWER(descripcion) LIKE ?', ["%{$searchTerm}%"]);
    }

    $mantenimientos = $mantenimientosQuery->get();

    return Inertia::render('Mantenimientos/Index', [
        'mantenimientos' => $mantenimientos,
        'filters' => ['search' => $search],
    ]);
}

    public function create()
    {
        return Inertia::render('Mantenimientos/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
        ]);

        Mantenimiento::create($request->only('descripcion', 'nombre'));

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento creado correctamente');
    }

    public function edit(Mantenimiento $mantenimiento)
    {
        return Inertia::render('Mantenimientos/Edit', [
            'mantenimiento' => $mantenimiento,
        ]);
    }

    public function update(Request $request, Mantenimiento $mantenimiento)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
        ]);

        $mantenimiento->update($request->only('descripcion', 'nombre'));

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento actualizado');
    }

    public function destroy(Mantenimiento $mantenimiento)
    {
        $mantenimiento->delete();

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento eliminado');
    }
}
