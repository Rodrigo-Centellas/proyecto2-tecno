<?php

namespace App\Http\Controllers;

use App\Models\Garante;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GaranteController extends Controller
{
public function index(Request $request)
{
    $search = $request->input('search');

    $garantes = Garante::query()
        ->when($search, function ($query, $search) {
            $search = strtolower($search);

            $query->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(apellido) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(ci::text) LIKE ?', ["%{$search}%"]);
        })
        ->get();

    return Inertia::render('Garantes/Index', [
        'garantes' => $garantes,
        'filters' => $request->only('search'),
    ]);
}



    public function create()
    {
        return Inertia::render('Garantes/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'apellido' => 'required|string|max:255',
            'ci' => 'required|integer',
            'domicilio' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|integer',
        ]);

        Garante::create($request->only('apellido', 'ci', 'domicilio', 'nombre', 'telefono'));

        return redirect()->route('garantes.index');
    }

    public function edit(Garante $garante)
    {
        return Inertia::render('Garantes/Edit', [
            'garante' => $garante,
        ]);
    }

    public function update(Request $request, Garante $garante)
    {
        $request->validate([
            'apellido' => 'required|string|max:255',
            'ci' => 'required|integer',
            'domicilio' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|integer',
        ]);

        $garante->update($request->only('apellido', 'ci', 'domicilio', 'nombre', 'telefono'));

        return redirect()->route('garantes.index');
    }

    public function destroy(Garante $garante)
    {
        $garante->delete();

        return redirect()->route('garantes.index');
    }
}
