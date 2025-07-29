<?php

namespace App\Http\Controllers;

use App\Models\FrecuenciaPago;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FrecuenciaPagoController extends Controller
{
public function index(Request $request)
{
    $search = $request->input('search');

    $frecuenciaPagos = \App\Models\FrecuenciaPago::query()
        ->when($search, function ($query, $search) {
            $search = strtolower($search);
            $query->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"]);
        })
        ->get();

    return Inertia::render('FrecuenciaPagos/Index', [
        'frecuenciaPagos' => $frecuenciaPagos,
        'filters' => $request->only('search'),
    ]);
}


    public function create()
    {
        return Inertia::render('FrecuenciaPagos/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'frecuencia_dias' => 'required|integer',
            'nombre' => 'required|string|max:255',
        ]);

        FrecuenciaPago::create($request->only('frecuencia_dias', 'nombre'));

        return redirect()->route('frecuencia-pagos.index');
    }

    public function edit(FrecuenciaPago $frecuenciaPago)
    {
        return Inertia::render('FrecuenciaPagos/Edit', [
            'frecuenciaPago' => $frecuenciaPago,
        ]);
    }

    public function update(Request $request, FrecuenciaPago $frecuenciaPago)
    {
        $request->validate([
            'frecuencia_dias' => 'required|integer',
            'nombre' => 'required|string|max:255',
        ]);

        $frecuenciaPago->update($request->only('frecuencia_dias', 'nombre'));

        return redirect()->route('frecuencia-pagos.index');
    }

    public function destroy(FrecuenciaPago $frecuenciaPago)
    {
        $frecuenciaPago->delete();

        return redirect()->route('frecuencia-pagos.index');
    }
}
