<?php

namespace App\Http\Controllers;

use App\Models\Clausula;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClausulaController extends Controller
{
    public function index()
    {
        $clausulas = Clausula::all();

        return Inertia::render('Clausulas/Index', [
            'clausulas' => $clausulas,
        ]);
    }

    public function create()
    {
        return Inertia::render('Clausulas/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
            'activa'      => 'required|boolean',
        ]);

        // Crear la cláusula directamente
        Clausula::create($data);

        return redirect()
            ->route('clausulas.index')
            ->with('success', 'Cláusula creada correctamente.');
    }

    public function destroy(Clausula $clausula)
    {
        $clausula->delete();

        return redirect()->back()->with('success', 'Cláusula eliminada.');
    }

    public function edit(Clausula $clausula)
{
    return Inertia::render('Clausulas/Edit', [
        'clausula' => $clausula,
    ]);
}

public function update(Request $request, Clausula $clausula)
{
    $data = $request->validate([
        'descripcion' => 'required|string|max:255',
        'activa' => 'required',
    ]);

$clausula->update($data);

    return redirect()->route('clausulas.index')->with('success', 'Cláusula actualizada correctamente.');
}

}
