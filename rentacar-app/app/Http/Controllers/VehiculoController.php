<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VehiculoController extends Controller
{
public function index(Request $request)
{
    $search = $request->input('search');

    $vehiculosQuery = Vehiculo::query();

    if ($search) {
        $searchTerm = strtolower($search);
        $vehiculosQuery->where(function ($query) use ($searchTerm) {
            $query->whereRaw('LOWER(placa) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(marca) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(COALESCE(modelo, \'\')) LIKE ?', ["%{$searchTerm}%"]);
        });
    }

    $vehiculos = $vehiculosQuery->paginate(10)->appends(['search' => $search])->through(function ($vehiculo) {
        $vehiculo->url_imagen = $vehiculo->url_imagen
            ? asset('storage/' . $vehiculo->url_imagen)
            : null;
        return $vehiculo;
    });

    return Inertia::render('Vehiculos/Index', [
        'vehiculos' => $vehiculos,
        'filters' => ['search' => $search],
    ]);
}


    public function create()
    {
        return Inertia::render('Vehiculos/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'placa' => 'required|string|unique:vehiculos,placa|max:255',
            'marca' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'estado' => 'required|string|max:255',
            'monto_garantia' => 'required|numeric',
            'precio_dia' => 'required|numeric',
            'tipo' => 'required|string|max:255',
            'url_imagen' => 'nullable|image|max:2048', // máximo 2MB
        ]);

        if ($request->hasFile('url_imagen')) {
            $data['url_imagen'] = $request->file('url_imagen')->store('vehiculos', 'public');
        }

        Vehiculo::create($data);

        return redirect()->route('vehiculos.index');
    }

    public function edit(Vehiculo $vehiculo)
    {
        $vehiculo->url_imagen = $vehiculo->url_imagen
            ? asset('storage/' . $vehiculo->url_imagen)
            : null;

        return Inertia::render('Vehiculos/Edit', [
            'vehiculo' => $vehiculo,
        ]);
    }

    public function update(Request $request, Vehiculo $vehiculo)
    {
        $data = $request->validate([
            'placa' => 'required|string|max:255|unique:vehiculos,placa,' . $vehiculo->id,
            'marca' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'estado' => 'required|string|max:255',
            'monto_garantia' => 'required|numeric',
            'precio_dia' => 'required|numeric',
            'tipo' => 'required|string|max:255',
            'url_imagen' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('url_imagen')) {
            // Eliminar imagen anterior si existe
            if ($vehiculo->url_imagen && Storage::disk('public')->exists($vehiculo->url_imagen)) {
                Storage::disk('public')->delete($vehiculo->url_imagen);
            }

            // Guardar nueva imagen
            $data['url_imagen'] = $request->file('url_imagen')->store('vehiculos', 'public');
        }

        $vehiculo->update($data);

        return redirect()->route('vehiculos.index');
    }

    public function destroy(Vehiculo $vehiculo)
    {
        // Eliminar imagen del disco si existe
        if ($vehiculo->url_imagen && Storage::disk('public')->exists($vehiculo->url_imagen)) {
            Storage::disk('public')->delete($vehiculo->url_imagen);
        }

        $vehiculo->delete();

        return redirect()->route('vehiculos.index');
    }

public function show()
{
    $vehiculos = Vehiculo::all()->map(function ($vehiculo) {
        $vehiculo->url_imagen = $vehiculo->url_imagen
            ? asset('storage/' . $vehiculo->url_imagen)
            : null;
        return $vehiculo;
    });

    return Inertia::render('Vehiculos/Mostrar', [
        'vehiculos' => $vehiculos,
    ]);
}
}
