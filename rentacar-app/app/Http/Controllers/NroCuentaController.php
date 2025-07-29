<?php

namespace App\Http\Controllers;

use App\Models\NroCuenta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NroCuentaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $nroCuentas = NroCuenta::query()
            ->when($search, fn($q) => $q
                ->whereRaw('LOWER(banco) LIKE ?', ['%'.strtolower($search).'%'])
                ->orWhereRaw('LOWER(nro_cuenta::text) LIKE ?', ['%'.strtolower($search).'%'])
            )
            ->get();

        return Inertia::render('NroCuentas/Index', [
            'nroCuentas' => $nroCuentas,
            'filters'    => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('NroCuentas/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'banco'       => 'required|string|max:255',
            'nro_cuenta'  => 'required|integer',
            'es_activa'   => 'required|boolean',
        ]);

        NroCuenta::create($data);

        return redirect()->route('nro-cuentas.index')->with('success', 'Número de cuenta creado.');
    }

    public function edit(NroCuenta $nroCuenta)
    {
        return Inertia::render('NroCuentas/Edit', [
            'nroCuenta' => $nroCuenta,
        ]);
    }

    public function update(Request $request, NroCuenta $nroCuenta)
    {
        $data = $request->validate([
            'banco'       => 'required|string|max:255',
            'nro_cuenta'  => 'required|integer',
            'es_activa'   => 'required|boolean',
        ]);

        $nroCuenta->update($data);

        return redirect()->route('nro-cuentas.index')->with('success', 'Número de cuenta actualizado.');
    }

    public function destroy(NroCuenta $nroCuenta)
    {
        $nroCuenta->delete();

        return redirect()->route('nro-cuentas.index')->with('success', 'Número de cuenta eliminado.');
    }
}
