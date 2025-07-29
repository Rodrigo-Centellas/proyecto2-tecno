<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Vehiculo;
use App\Models\NroCuenta;
use App\Models\FrecuenciaPago;
use App\Models\Clausula;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Helpers\NumeroTextoHelper;

class ContratoController extends Controller
{
    // Mostrar el listado de contratos
public function index(Request $request)
{
    $search = $request->input('search');
    $user = Auth::user();

    $query = Contrato::with([
        'users',
        'frecuenciaPago',
        'nroCuenta',
        'vehiculo',
        'contrato_clausulas',
    ]);

    // FILTRO POR ROL: Solo clientes ven sus propios contratos
    if ($user->hasRole('Cliente')) {
        $query->whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    // FILTRO DE BÚSQUEDA
    if ($search) {
        $query->where(function ($mainQuery) use ($search, $user) {
            // Búsqueda por usuario
            $mainQuery->whereHas('users', function ($q) use ($search, $user) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('apellido', 'like', "%{$search}%");
                });
                
                // Si es cliente, mantener filtro de sus propios contratos
                if ($user->hasRole('Cliente')) {
                    $q->where('user_id', $user->id);
                }
            })
            // Búsqueda por vehículo
            ->orWhere(function ($subQuery) use ($search, $user) {
                $subQuery->whereHas('vehiculo', function ($q) use ($search) {
                    $q->where('placa', 'like', "%{$search}%");
                });
                
                // Si es cliente, mantener filtro de sus propios contratos
                if ($user->hasRole('Cliente')) {
                    $subQuery->whereHas('users', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                }
            })
            // Búsqueda por ID
            ->orWhere(function ($subQuery) use ($search, $user) {
                $subQuery->where('id', 'like', "%{$search}%");
                
                // Si es cliente, mantener filtro de sus propios contratos
                if ($user->hasRole('Cliente')) {
                    $subQuery->whereHas('users', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                }
            });
        });
    }

    $contratos = $query->orderBy('created_at', 'desc')->get();

    // Procesar URLs de imágenes
    $contratos->each(function ($contrato) {
        if ($contrato->vehiculo && $contrato->vehiculo->url_imagen) {
            $contrato->vehiculo->url_imagen = asset('storage/' . $contrato->vehiculo->url_imagen);
        }
    });

    return Inertia::render('Contratos/Index', [
        'contratos' => $contratos,
        'filters' => [
            'search' => $search,
        ],
        'user_context' => [
            'is_admin' => $user->hasRole('Administrador'),
            'is_empleado' => $user->hasRole('Empleado Operativo'),
            'is_cliente' => $user->hasRole('Cliente'),
            'showing_own_data' => $user->hasRole('Cliente'),
        ]
    ]);
}
    // Mostrar el formulario de creación de un nuevo contrato
    public function create(Request $request)
    {
        Log::info('ContratoController::create - Iniciando formulario de creación', [
            'vehiculo_id' => $request->get('vehiculo_id'),
            'user_id' => Auth::id()
        ]);

        try {
            $vehiculoSeleccionado = null;

            // 🚗 Obtener vehículo preseleccionado desde la URL
            if ($request->has('vehiculo_id')) {
                Log::info('ContratoController::create - Buscando vehículo preseleccionado', [
                    'vehiculo_id' => $request->vehiculo_id
                ]);

                $vehiculoSeleccionado = Vehiculo::find($request->vehiculo_id);
                
                if ($vehiculoSeleccionado) {
                    Log::info('ContratoController::create - Vehículo encontrado', [
                        'vehiculo' => $vehiculoSeleccionado->toArray()
                    ]);

                    // 🔧 Construir la URL completa de la imagen
                    if ($vehiculoSeleccionado->url_imagen) {
                        $vehiculoSeleccionado->url_imagen = asset('storage/' . $vehiculoSeleccionado->url_imagen);
                        Log::info('ContratoController::create - URL de imagen procesada', [
                            'url_imagen' => $vehiculoSeleccionado->url_imagen
                        ]);
                    }
                } else {
                    Log::warning('ContratoController::create - Vehículo no encontrado', [
                        'vehiculo_id' => $request->vehiculo_id
                    ]);
                }
            }

            // Obtener datos para el formulario
            Log::info('ContratoController::create - Obteniendo datos para formulario');

            $vehiculos = Vehiculo::all();
            Log::info('ContratoController::create - Vehículos obtenidos', ['count' => $vehiculos->count()]);

            $nroCuentas = NroCuenta::all();
            Log::info('ContratoController::create - Cuentas obtenidas', ['count' => $nroCuentas->count()]);

            $frecuenciasPago = FrecuenciaPago::all();
            Log::info('ContratoController::create - Frecuencias de pago obtenidas', ['count' => $frecuenciasPago->count()]);

            // Solo cláusulas activas
            $clausulas = Clausula::where('activa', true)->get();
            Log::info('ContratoController::create - Cláusulas activas obtenidas', ['count' => $clausulas->count()]);

            // Procesar URLs de imágenes para todos los vehículos
            $vehiculos = $vehiculos->map(function ($vehiculo) {
                $vehiculo->url_imagen = $vehiculo->url_imagen
                    ? asset('storage/' . $vehiculo->url_imagen)
                    : null;
                return $vehiculo;
            });

            Log::info('ContratoController::create - Datos preparados exitosamente');

            return Inertia::render('Contratos/Create', [
                'vehiculos' => $vehiculos,
                'nro_cuentas' => $nroCuentas,
                'frecuencia_pagos' => $frecuenciasPago,
                'clausulas' => $clausulas,
                'vehiculoSeleccionado' => $vehiculoSeleccionado,
            ]);
        } catch (\Exception $e) {
            Log::error('ContratoController::create - Error en formulario de creación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Error al cargar el formulario: ' . $e->getMessage()]);
        }
    }

    // Guardar un nuevo contrato// Reemplazar el método store en ContratoController.php

// Reemplazar el método store en ContratoController.php

public function store(Request $request)
{
    try {
        // Validación de los datos
        $validatedData = $request->validate([
            'estado' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'frecuencia_pago_id' => 'required|exists:frecuencia_pagos,id',
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'acepta_clausulas' => 'required|accepted',
            'pago_garantia_acepta' => 'required|accepted',
        ]);

        // Obtener cuenta principal automáticamente
        $cuentaPrincipal = NroCuenta::where('es_activa', true)->first();
        
        if (!$cuentaPrincipal) {
            return back()->withErrors(['error' => 'No se encontró una cuenta bancaria principal configurada']);
        }

        // Obtener datos necesarios para los cálculos
        $vehiculo = Vehiculo::findOrFail($request->vehiculo_id);
        $frecuenciaPago = FrecuenciaPago::findOrFail($request->frecuencia_pago_id);

        // Calcular días del contrato
        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);
        $diasContrato = $fechaInicio->diffInDays($fechaFin) + 1;

        // Validar que la frecuencia sea válida para la duración
        if ($frecuenciaPago->frecuencia_dias > $diasContrato) {
            return back()->withErrors([
                'frecuencia_pago_id' => "La frecuencia de pago '{$frecuenciaPago->nombre}' ({$frecuenciaPago->frecuencia_dias} días) no es válida para un contrato de {$diasContrato} días."
            ])->withInput();
        }

        // Calcular costos
        $costoTotal = $diasContrato * $vehiculo->precio_dia;
        
        // Calcular pagos de forma inteligente
        $pagosCalculados = $this->calcularPagos($fechaInicio, $fechaFin, $frecuenciaPago, $vehiculo->precio_dia);

        // Iniciar transacción de base de datos
        DB::beginTransaction();

        // Crear el contrato
        $contratoData = [
            'estado' => 'pendiente de aprobacion',
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'frecuencia_pago_id' => $request->frecuencia_pago_id,
            'nro_cuenta_id' => $cuentaPrincipal->id,
            'vehiculo_id' => $request->vehiculo_id,
        ];

        $contrato = Contrato::create($contratoData);

        // Aplicar todas las cláusulas activas automáticamente
        $clausulasActivas = Clausula::where('activa', true)->pluck('id')->toArray();
        
        if (!empty($clausulasActivas)) {
            $contrato->contrato_clausulas()->sync($clausulasActivas);
        }

        // Crear pago de garantía inmediato
        $pagoGarantiaData = [
            'desde' => now(),
            'fecha' => now(),
            'hasta' => now(),
            'estado' => 'pendiente',
            'monto' => $vehiculo->monto_garantia,
            'tipo_pago' => 'garantia',
            'contrato_id' => $contrato->id,
            'reserva_id' => null,
        ];

        Pago::create($pagoGarantiaData);

        // Crear todos los pagos del contrato
        foreach ($pagosCalculados as $pagoInfo) {
            $pagoData = [
                'desde' => $pagoInfo['desde'],
                'fecha' => $pagoInfo['desde'],
                'hasta' => $pagoInfo['hasta'],
                'estado' => 'pendiente',
                'monto' => $pagoInfo['monto'],
                'tipo_pago' => 'contrato',
                'contrato_id' => $contrato->id,
                'reserva_id' => null,
            ];

            Pago::create($pagoData);
        }

        // Actualizar estado del vehículo
        $vehiculo->update(['estado' => 'Alquilado']);

        // Asociar contrato al usuario actual
        if (method_exists($contrato, 'users')) {
            $contrato->users()->attach(Auth::id());
        }

        // Confirmar transacción
        DB::commit();

        return redirect()->route('contratos.index')->with('success', 'Contrato creado exitosamente. Proceda al área de pagos.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return back()->withErrors($e->errors())->withInput();

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Error al crear el contrato: ' . $e->getMessage()])->withInput();
    }
}

/**
 * Calcular todos los pagos del contrato de forma inteligente
 */
private function calcularPagos($fechaInicio, $fechaFin, $frecuenciaPago, $precioPorDia)
{
    $pagos = [];
    $fechaActual = $fechaInicio->copy();
    $diasTotales = $fechaInicio->diffInDays($fechaFin) + 1; // ✅ CORREGIDO: fechaInicio->diffInDays($fechaFin)
    $diasProcesados = 0;
    
    Log::info('ContratoController::calcularPagos - Iniciando cálculo', [
        'fecha_inicio' => $fechaInicio->format('Y-m-d'),
        'fecha_fin' => $fechaFin->format('Y-m-d'),
        'dias_totales' => $diasTotales,
        'frecuencia_dias' => $frecuenciaPago->frecuencia_dias,
        'precio_por_dia' => $precioPorDia
    ]);

    while ($diasProcesados < $diasTotales) {
        // Calcular cuántos días quedan
        $diasRestantes = $diasTotales - $diasProcesados;
        
        // Determinar cuántos días tendrá este pago
        $diasEstePago = min($frecuenciaPago->frecuencia_dias, $diasRestantes);
        
        // Calcular fechas de este pago
        $fechaHastaPago = $fechaActual->copy()->addDays($diasEstePago - 1);
        
        // Calcular monto de este pago
        $montoPago = $diasEstePago * $precioPorDia;
        
        $pagos[] = [
            'desde' => $fechaActual->copy(),
            'hasta' => $fechaHastaPago,
            'dias' => $diasEstePago,
            'monto' => $montoPago
        ];
        
        Log::info('ContratoController::calcularPagos - Pago calculado', [
            'pago_numero' => count($pagos),
            'desde' => $fechaActual->format('Y-m-d'),
            'hasta' => $fechaHastaPago->format('Y-m-d'),
            'dias' => $diasEstePago,
            'monto' => $montoPago,
            'dias_procesados' => $diasProcesados + $diasEstePago
        ]);
        
        // Avanzar para el siguiente pago
        $fechaActual->addDays($diasEstePago);
        $diasProcesados += $diasEstePago;
    }
    
    Log::info('ContratoController::calcularPagos - Cálculo completado', [
        'total_pagos' => count($pagos),
        'dias_procesados' => $diasProcesados,
        'dias_totales' => $diasTotales
    ]);
    
    return $pagos;
}

    // Mostrar el formulario de edición de un contrato existente
    public function edit(Contrato $contrato)
    {
        Log::info('ContratoController::edit - Iniciando edición de contrato', [
            'contrato_id' => $contrato->id
        ]);

        try {
            $contrato->load([
                'frecuenciaPago',
                'nroCuenta',
                'vehiculo',
                'contrato_clausulas',
            ]);

            // Procesar URL de imagen del vehículo
            if ($contrato->vehiculo && $contrato->vehiculo->url_imagen) {
                $contrato->vehiculo->url_imagen = asset('storage/' . $contrato->vehiculo->url_imagen);
            }

            // Procesar URLs de imágenes para todos los vehículos
            $vehiculos = Vehiculo::all()->map(function ($vehiculo) {
                $vehiculo->url_imagen = $vehiculo->url_imagen
                    ? asset('storage/' . $vehiculo->url_imagen)
                    : null;
                return $vehiculo;
            });

            Log::info('ContratoController::edit - Datos preparados para edición');

            return Inertia::render('Contratos/Edit', [
                'contrato' => $contrato,
                'vehiculos' => $vehiculos,
                'nro_cuentas' => NroCuenta::all(),
                'frecuencia_pagos' => FrecuenciaPago::all(),
                'clausulas' => Clausula::all(),
            ]);
        } catch (\Exception $e) {
            Log::error('ContratoController::edit - Error al cargar formulario de edición', [
                'contrato_id' => $contrato->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Error al cargar el contrato: ' . $e->getMessage()]);
        }
    }

    // Actualizar un contrato
public function update(Request $request, Contrato $contrato)
{
    try {
        // Validación restrictiva - solo campos permitidos
        $validatedData = $request->validate([
            'estado' => 'required|string|in:vigente,cumplido,vencido,pendiente de aprobacion',
            'fecha_fin' => 'nullable|date|after_or_equal:' . $contrato->fecha_inicio, // Solo si se permite modificar
        ]);

        // Verificar que el contrato puede ser editado
        if (in_array($contrato->estado, ['cumplido', 'cancelado'])) {
            return back()->withErrors(['error' => 'No se puede modificar un contrato que ya está cumplido o cancelado.']);
        }

        // Actualizar solo los campos permitidos
        $updateData = ['estado' => $validatedData['estado']];
        
        // Solo actualizar fecha_fin si se envió y es diferente
        if ($request->has('fecha_fin') && $request->fecha_fin !== $contrato->fecha_fin) {
            $updateData['fecha_fin'] = $validatedData['fecha_fin'];
            
            // Log de cambio crítico para auditoría
            Log::warning('Cambio de fecha de fin de contrato', [
                'contrato_id' => $contrato->id,
                'fecha_fin_anterior' => $contrato->fecha_fin,
                'fecha_fin_nueva' => $validatedData['fecha_fin'],
                'usuario' => auth()->id(),
                'timestamp' => now()
            ]);
        }

        $contrato->update($updateData);

        return redirect()->route('contratos.index')->with('success', 'Contrato actualizado exitosamente.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()->withErrors($e->errors())->withInput();
        
    } catch (\Exception $e) {
        Log::error('Error al actualizar contrato', [
            'contrato_id' => $contrato->id,
            'error' => $e->getMessage(),
            'usuario' => Auth()->id()
        ]);

        return back()->withErrors(['error' => 'Error al actualizar el contrato: ' . $e->getMessage()]);
    }
}

    // Eliminar un contrato

public function destroy(Contrato $contrato)
{
    try {
        DB::beginTransaction();
        
        Log::info('ContratoController::destroy - Iniciando eliminación', [
            'contrato_id' => $contrato->id
        ]);

        // 1. Verificar y eliminar pagos relacionados (tabla pagos)
        $pagosEliminados = DB::table('pagos')->where('contrato_id', $contrato->id)->delete();
        Log::info('Pagos eliminados: ' . $pagosEliminados);

        // 2. Eliminar relación contrato_clausulas (tabla pivot)
        $clausulasEliminadas = DB::table('contrato_clausulas')->where('contrato_id', $contrato->id)->delete();
        Log::info('Cláusulas eliminadas: ' . $clausulasEliminadas);

        // 3. Eliminar relación user_contratos (tabla pivot)
        $userContratosEliminados = DB::table('user_contratos')->where('contrato_id', $contrato->id)->delete();
        Log::info('User contratos eliminados: ' . $userContratosEliminados);

        // 4. Liberar el vehículo
        if ($contrato->vehiculo_id) {
            DB::table('vehiculos')
                ->where('id', $contrato->vehiculo_id)
                ->update(['estado' => 'Disponible']);
            Log::info('Vehículo liberado: ' . $contrato->vehiculo_id);
        }

        // 5. Eliminar el contrato
        $contrato->delete();
        Log::info('Contrato eliminado exitosamente');

        DB::commit();

        return redirect()->route('contratos.index')->with('success', 'Contrato eliminado exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Error al eliminar contrato', [
            'contrato_id' => $contrato->id,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return back()->withErrors(['error' => 'Error al eliminar el contrato: ' . $e->getMessage()]);
    }
}

    // En ContratoController.php - agregar método show
// Agregar al inicio del ContratoController


/**
 * Mostrar los detalles de un contrato específico
 */
public function show(Contrato $contrato)
{
    Log::info('ContratoController::show - Mostrando contrato', [
        'contrato_id' => $contrato->id,
        'user_id' => Auth::id()
    ]);

    try {
        // Cargar todas las relaciones necesarias
        $contrato->load([
            'frecuenciaPago',
            'nroCuenta', 
            'vehiculo',
            'contrato_clausulas',
            'users'
        ]);

        Log::info('ContratoController::show - Relaciones cargadas exitosamente');

        // Procesar URL de imagen del vehículo
        if ($contrato->vehiculo && $contrato->vehiculo->url_imagen) {
            $contrato->vehiculo->url_imagen = asset('storage/' . $contrato->vehiculo->url_imagen);
        }

        // Preparar datos adicionales para la vista
        $contratoData = $this->prepararDatosContrato($contrato);

        Log::info('ContratoController::show - Datos preparados exitosamente');

        return Inertia::render('Contratos/Show', [
            'contrato' => $contrato,
            'contratoData' => $contratoData,
        ]);

    } catch (\Exception $e) {
        Log::error('ContratoController::show - Error al mostrar contrato', [
            'contrato_id' => $contrato->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()->withErrors(['error' => 'Error al cargar el contrato: ' . $e->getMessage()]);
    }
}

/**
 * Preparar datos adicionales para el contrato
 */
private function prepararDatosContrato($contrato)
{
    $fechaInicio = Carbon::parse($contrato->fecha_inicio);
    $fechaFin = Carbon::parse($contrato->fecha_fin);
    $diasTotal = $fechaInicio->diffInDays($fechaFin) + 1;
    
    $montoTotal = 0;
    $montoGarantia = 0;
    
    if ($contrato->vehiculo) {
        $montoTotal = $diasTotal * $contrato->vehiculo->precio_dia;
        $montoGarantia = $contrato->vehiculo->monto_garantia;
    }

    return [
        'numero_contrato' => $this->generarNumeroContrato($contrato),
        'fecha_actual' => $this->formatearFechaActual(),
        'duracion_total' => $diasTotal,
        'monto_total' => $montoTotal,
        'monto_total_texto' => NumeroTextoHelper::aBolivianos($montoTotal),
        'monto_garantia_texto' => NumeroTextoHelper::aBolivianos($montoGarantia),
        'precio_dia_texto' => $contrato->vehiculo ? NumeroTextoHelper::aBolivianos($contrato->vehiculo->precio_dia) : '',
        'fecha_inicio_formateada' => $fechaInicio->locale('es')->isoFormat('DD [de] MMMM [del] YYYY'),
        'fecha_fin_formateada' => $fechaFin->locale('es')->isoFormat('DD [de] MMMM [del] YYYY'),
    ];
}

/**
 * Generar número de contrato
 */
private function generarNumeroContrato($contrato)
{
    $year = Carbon::parse($contrato->fecha_inicio)->format('Y');
    return sprintf('%06d-%s', $contrato->id, $year);
}

/**
 * Formatear fecha actual
 */
private function formatearFechaActual()
{
    return Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [del] YYYY');
}


}