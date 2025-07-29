<?php
namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Vehiculo;
use App\Models\Pago;
use Carbon\Traits\ToStringFormat;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class ReservaController extends Controller
{
public function index(Request $request)
{
    try {
        $search = $request->input('search');
        $user = auth()->user();

        $reservasQuery = Reserva::with(['user', 'vehiculo'])
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('apellido', 'like', "%{$search}%")
                      ->orWhere('ci', 'like', "%{$search}%")
                      ->orWhere('telefono', 'like', "%{$search}%");
                })
                ->orWhereHas('vehiculo', function ($q) use ($search) {
                    $q->where('placa', 'like', "%{$search}%")
                      ->orWhere('tipo', 'like', "%{$search}%")
                      ->orWhere('marca', 'like', "%{$search}%")
                      ->orWhere('modelo', 'like', "%{$search}%");
                })
                ->orWhere('id', 'like', "%{$search}%")
                ->orWhere('estado', 'like', "%{$search}%")
                ->orWhereDate('fecha', 'like', "%{$search}%");
            });

        // 🔒 FILTRO POR ROL: Si es cliente, solo sus reservas
        if ($user->hasRole('Cliente')) {
            $reservasQuery->where('user_id', $user->id);
        }
        // Si es Administrador o Empleado Operativo, ve todas las reservas (no agregamos filtro)

        $reservas = $reservasQuery
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Reservas/Index', [
            'reservas' => $reservas,
            'filters' => [
                'search' => $search,
            ],
            'userRole' => $user->getRoleNames()->first(), // Para usar en el frontend si es necesario
        ]);

    } catch (\Exception $e) {
        Log::error('Error en índice de reservas: ' . $e->getMessage(), [
            'user_id' => auth()->id(),
            'search' => $search ?? null,
            'error' => $e->getTraceAsString()
        ]);
        
        return Inertia::render('Reservas/Index', [
            'reservas' => collect([]),
            'filters' => [
                'search' => null,
            ],
            'userRole' => auth()->user()->getRoleNames()->first(),
        ]);
    }
}


public function create(Request $request)
{
    $vehiculoSeleccionado = null;

    if ($request->has('vehiculo_id')) {
        $vehiculoSeleccionado = Vehiculo::find($request->vehiculo_id);
        
        // 🔧 Construir la URL completa de la imagen
        if ($vehiculoSeleccionado && $vehiculoSeleccionado->url_imagen) {
            $vehiculoSeleccionado->url_imagen = asset('storage/' . $vehiculoSeleccionado->url_imagen);
        }
    }

    return Inertia::render('Reservas/Create', [
        'vehiculos' => Vehiculo::all()->map(function ($vehiculo) {
            // También aplicar la transformación a todos los vehículos por si acaso
            $vehiculo->url_imagen = $vehiculo->url_imagen
                ? asset('storage/' . $vehiculo->url_imagen)
                : null;
            return $vehiculo;
        }),
        'vehiculoSeleccionado' => $vehiculoSeleccionado,
    ]);
}

public function store(Request $request)
{
    $request->validate([
        'vehiculo_id' => 'required|exists:vehiculos,id',
        'fecha' => 'required|date|after:today', // la fecha debe ser futura
    ]);

    $vehiculo = Vehiculo::findOrFail($request->vehiculo_id);

    // 🟢 Reserva es desde mañana (hoy + 1) hasta la fecha seleccionada
    $inicio = Carbon::tomorrow(); // mañana
    $fin = Carbon::parse($request->fecha)->startOfDay(); // fecha seleccionada por el usuario

    if ($inicio->gt($fin)) {
        return back()->withErrors([
            'fecha' => 'La fecha debe ser mayor a mañana para que la reserva tenga al menos un día.',
        ]);
    }

    // ✅ Calcular días (incluyendo la fecha fin)
    $dias = $inicio->diffInDays($fin) + 1;

    // 💸 Calcular monto
    $monto = $dias * $vehiculo->precio_dia;

    // 📌 Crear reserva
    $reserva = Reserva::create([
        'vehiculo_id' => $vehiculo->id,
        'fecha' => $request->fecha, // esta es la fecha "hasta"
        'estado' => 'Pendiente De pago',
        'user_id' => Auth::id(),
    ]);

    // 🟡 Actualizar estado del vehículo
    $vehiculo->estado = 'Reservado';
    $vehiculo->save();

    // 🧾 Crear el pago
    Pago::create([
        'desde' => $inicio,
        'fecha' => now(),
        'hasta' => $fin,
        'estado' => 'pendiente',
        'monto' => $monto,
        'tipo_pago' => 'reserva',
        'reserva_id' => $reserva->id,
        'contrato_id' => null,
    ]);

    return redirect()->route('reservas.index')->with('success', 'Reserva y pago creados correctamente.');
}


    public function show($id)
    {
        $reserva = Reserva::with(['vehiculo', 'user'])->findOrFail($id);
        return Inertia::render('Reservas/Show', ['reserva' => $reserva]);
    }

    public function edit(Reserva $reserva)
    {
        return Inertia::render('Reservas/Edit', [
            'reserva' => $reserva,
        ]);
    }

    public function update(Request $request, Reserva $reserva)
    {
        $request->validate([
            'estado' => 'required|string',
            'fecha' => 'required|date',
        ]);

        $reserva->update($request->only('estado', 'fecha'));

        return redirect()->route('reservas.index');
    }

    public function destroy(Reserva $reserva)
    {
        $reserva->delete();

        return redirect()->route('reservas.index');
    }
}
