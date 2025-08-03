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
use Illuminate\Support\Facades\DB;

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
    // DEBUG: Verificar timezone
    Log::info('DEBUG TIMEZONE:', [
        'app_timezone' => config('app.timezone'),
        'server_time' => now()->toDateTimeString(),
        'carbon_today' => Carbon::today()->toDateString(),
        'carbon_now' => Carbon::now()->toDateTimeString(),
        'php_date' => date('Y-m-d H:i:s'),
        'fecha_recibida' => $request->fecha,
    ]);

    // Agregar logging para debug
    Log::info('Datos recibidos en store:', $request->all());

    try {
        // Validación manual más específica
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'fecha' => 'required|date',
        ]);

        // Validación adicional de fecha - debe ser al menos mañana
        $fechaSeleccionada = Carbon::parse($request->fecha);
        $hoy = Carbon::today();
        $manana = Carbon::tomorrow();
        
        Log::info('Comparación de fechas:', [
            'fecha_seleccionada' => $fechaSeleccionada->toDateString(),
            'hoy' => $hoy->toDateString(),
            'manana' => $manana->toDateString(),
            'es_manana_o_posterior' => $fechaSeleccionada->gte($manana)
        ]);

        if ($fechaSeleccionada->lt($manana)) {
            $validator->errors()->add('fecha', 'La fecha de devolución debe ser mañana o posterior.');
        }

        if ($validator->fails()) {
            Log::error('Validación falló:', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }
        Log::info('Validación pasada correctamente');
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Error de validación:', [
            'errors' => $e->errors(),
            'failed_rules' => $e->validator->failed()
        ]);
        throw $e;
    }

    try {
        $vehiculo = Vehiculo::findOrFail($request->vehiculo_id);

        // SOLUCIÓN DEFINITIVA: Usar el mismo timezone que el frontend
        // El frontend usa timezone local, backend debe usar el mismo
        
        // Obtener fecha "hoy" desde el frontend (en la práctica, desde la perspectiva del usuario)
        $fechaUsuario = $request->input('fecha_usuario', Carbon::today()->toDateString());
        $fechaInicio = Carbon::parse($fechaUsuario); 
        $fechaDevolucion = Carbon::parse($request->fecha);
        
        // Si no se envía fecha_usuario, calcular basado en la fecha seleccionada
        if (!$request->has('fecha_usuario')) {
            // Si selecciona mañana, significa que hoy es un día antes
            $fechaInicio = $fechaDevolucion->copy()->subDay();
        }
        
        $dias = $fechaInicio->diffInDays($fechaDevolucion);
        
        Log::info('Fechas y días calculados:', [
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_devolucion' => $fechaDevolucion->toDateString(),
            'dias_alquiler' => $dias,
            'timezone_app' => config('app.timezone'),
            'explicacion' => "Alquiler desde {$fechaInicio->toDateString()} hasta {$fechaDevolucion->toDateString()} = {$dias} día(s)"
        ]);

        // Validación: mínimo debe ser 1 día (ya validado arriba, pero por seguridad)
        if ($dias < 1) {
            Log::warning('Días calculados son < 1', ['dias' => $dias]);
            return back()->withErrors([
                'fecha' => 'Debe seleccionar al menos 1 día de alquiler.',
            ]);
        }

        // Calcular monto
        $monto = $dias * $vehiculo->precio_dia;
        
        Log::info('Monto calculado:', [
            'dias' => $dias,
            'precio_dia' => $vehiculo->precio_dia,
            'monto_total' => $monto
        ]);

        // Usar transacción para asegurar consistencia
        DB::beginTransaction();

        try {
            // Crear reserva
            $reserva = Reserva::create([
                'vehiculo_id' => $vehiculo->id,
                'fecha' => $request->fecha,
                'estado' => 'Pendiente De pago',
                'user_id' => Auth::id(),
            ]);

            Log::info('Reserva creada:', ['reserva_id' => $reserva->id]);

            // Actualizar estado del vehículo
            $vehiculo->estado = 'Reservado';
            $vehiculo->save();

            Log::info('Vehículo actualizado:', ['vehiculo_id' => $vehiculo->id, 'nuevo_estado' => 'Reservado']);

            // Crear el pago con fechas consistentes
            $pago = Pago::create([
                'desde' => $fechaInicio, // Hoy
                'fecha' => now(),
                'hasta' => $fechaDevolucion, // Fecha de devolución
                'estado' => 'pendiente',
                'monto' => $monto,
                'tipo_pago' => 'reserva',
                'reserva_id' => $reserva->id,
                'contrato_id' => null,
            ]);

            Log::info('Pago creado:', ['pago_id' => $pago->id]);

            DB::commit();

            Log::info('Transacción completada exitosamente');

            return redirect()->route('reservas.index')->with('success', 'Reserva y pago creados correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en transacción de reserva:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors([
                'general' => 'Error al crear la reserva. Por favor intenta nuevamente.'
            ]);
        }

    } catch (\Exception $e) {
        Log::error('Error general en store de reserva:', [
            'error' => $e->getMessage(),
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()->withErrors([
            'general' => 'Error inesperado. Por favor intenta nuevamente.'
        ]);
    }
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
    DB::transaction(function () use ($reserva) {
        // Borrar pagos asociados primero
        $reserva->pagos()->delete();

        // Liberar vehículo
        $reserva->vehiculo->update(['estado' => 'Disponible']);

        // Finalmente borrar la reserva
        $reserva->delete();
    });

    return redirect()->route('reservas.index')->with('success', 'Reserva eliminada correctamente.');
}

}
