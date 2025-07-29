<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\User;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use GuzzleHttp\Client;

class PagoController extends Controller
{
public function index(Request $request)
{
    $search = $request->input('search');
    $user   = auth()->user();

    // 1) Eager-load todas las relaciones que vas a usar en la vista
    $pagosQuery = Pago::with([
        'reserva.user',
        'reserva.vehiculo',
        'contrato.users',
        'contrato.vehiculo',
    ])
    // 2) Filtro de búsqueda (id, cliente, placa)
    ->when($search, function($q) use ($search) {
        $q->where('id', 'like', "%{$search}%")
          ->orWhereHas('reserva.user', function($q2) use ($search){
              $q2->where('name', 'like', "%{$search}%")
                 ->orWhere('email', 'like', "%{$search}%");
          })
          ->orWhereHas('reserva.vehiculo', function($q2) use ($search){
              $q2->where('placa', 'like', "%{$search}%");
          })
          ->orWhereHas('contrato.users', function($q2) use ($search){
              $q2->where('name', 'like', "%{$search}%")
                 ->orWhere('email', 'like', "%{$search}%");
          })
          ->orWhereHas('contrato.vehiculo', function($q2) use ($search){
              $q2->where('placa', 'like', "%{$search}%");
          });
    });

    // 3) Filtrar según rol Cliente (por contrato O reserva)
    if ($user->hasRole('Cliente')) {
        $pagosQuery->where(function($q){
            $q->whereHas('contrato.users', function($q2){
                $q2->where('user_id', auth()->id());
            })
            ->orWhereHas('reserva', function($q3){
                $q3->where('user_id', auth()->id());
            });
        });
    }
    // (Admin/Operativo ven todo, así que no añadimos nada más)

    // 4) Ejecutar, ordenar y transformar para la vista
    $pagos = $pagosQuery
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($pago) {
            // Crear un objeto normalizado que funcione con tu vista actual
            $pagoData = $pago->toArray();
            
            // Si es una reserva, "simular" que tiene contrato para que la vista funcione
            if ($pago->reserva && !$pago->contrato) {
                $pagoData['contrato'] = [
                    'users' => [
                        [
                            'name' => $pago->reserva->user->name ?? 'Usuario Desconocido',
                            'email' => $pago->reserva->user->email ?? '',
                            'apellido' => $pago->reserva->user->apellido ?? ''
                        ]
                    ],
                    'vehiculo' => [
                        'placa' => $pago->reserva->vehiculo->placa ?? 'Placa Desconocida',
                        'tipo' => $pago->reserva->vehiculo->tipo ?? '',
                        'marca' => $pago->reserva->vehiculo->marca ?? '',
                        'modelo' => $pago->reserva->vehiculo->modelo ?? ''
                    ]
                ];
            }
            
            // Si es contrato pero no tiene datos, llenar con valores por defecto
            if ($pago->contrato && (!$pago->contrato->users || $pago->contrato->users->isEmpty())) {
                $pagoData['contrato']['users'] = [
                    [
                        'name' => 'Usuario Desconocido',
                        'email' => '',
                        'apellido' => ''
                    ]
                ];
            }
            
            if ($pago->contrato && !$pago->contrato->vehiculo) {
                $pagoData['contrato']['vehiculo'] = [
                    'placa' => 'Vehículo Desconocido',
                    'tipo' => '',
                    'marca' => '',
                    'modelo' => ''
                ];
            }
            
            // Si no hay ni contrato ni reserva, crear estructura básica
            if (!$pago->contrato && !$pago->reserva) {
                $pagoData['contrato'] = [
                    'users' => [
                        [
                            'name' => 'Cliente Desconocido',
                            'email' => '',
                            'apellido' => ''
                        ]
                    ],
                    'vehiculo' => [
                        'placa' => 'Vehículo Desconocido',
                        'tipo' => '',
                        'marca' => '',
                        'modelo' => ''
                    ]
                ];
            }
            
            return $pagoData;
        });

    return Inertia::render('Pagos/Index', [
        'pagos'    => $pagos,
        'filters'  => ['search' => $search],
        'userRole' => $user->getRoleNames()->first(),
    ]);
}




    /**
     * Generar QR para pago usando la misma estructura que ReserveController
     */
  
      public function generarQR(Request $request, $pagoId)
    {
        try {
            Log::info('PagoController::generarQR - Iniciando generación de QR', [
                'pago_id' => $pagoId,
                'user_id' => Auth::id(),
                'request_type' => $request->expectsJson() ? 'AJAX' : 'WEB'
            ]);

            $pago = Pago::findOrFail($pagoId);
            
            // Verificar que el pago esté pendiente
            if ($pago->estado !== 'pendiente') {
                $errorMessage = 'Este pago ya no está disponible para procesar.';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                
                return redirect()->back()->withErrors(['qr_error' => $errorMessage]);
            }

            // Obtener usuario asociado al pago
            $usuario = $this->obtenerUsuarioPago($pago);
            
            if (!$usuario) {
                $errorMessage = 'No se pudo determinar el usuario para este pago.';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                
                return redirect()->back()->withErrors(['qr_error' => $errorMessage]);
            }

            // Configuración PagoFácil (misma que ReserveController)
            $lcComerceID = env('PAGOFACIL_COMMERCE_ID', "d029fa3a95e174a19934857f535eb9427d967218a36ea014b70ad704bc6c8d1c");
            $lnMoneda = 2;
            $lnTelefono = $usuario->telefono ?? $usuario->phone ?? 70000000;
            $lcNombreUsuario = $usuario->name;
            $lnCiNit = $usuario->ci ?? $usuario->telefono ?? $usuario->phone ?? 0;
            $lcNroPago = "pago-" . $pago->id . "-" . rand(100000, 999999);
            $lnMontoClienteEmpresa = $pago->monto;
            $lcCorreo = $usuario->email;
            $lcUrlCallBack = url('/pagos/callback');
            $lcUrlReturn = url('/pagos/confirmacion');

            $laPedidoDetalle = [
                [
                    'Serial' => $pago->id,
                    'Producto' => $this->obtenerDescripcionProducto($pago),
                    'Cantidad' => 1,
                    'Precio' => $lnMontoClienteEmpresa,
                    'Descuento' => 0,
                    'Total' => $lnMontoClienteEmpresa,
                ]
            ];

            // API para generar QR (usando la misma URL que ReserveController)
            $lcUrl = "https://serviciostigomoney.pagofacil.com.bo/api/servicio/generarqrv2";
            $laHeader = ['Accept' => 'application/json'];
            $laBody = [
                "tcCommerceID" => $lcComerceID,
                "tnMoneda" => $lnMoneda,
                "tnTelefono" => $lnTelefono,
                'tcNombreUsuario' => $lcNombreUsuario,
                'tnCiNit' => $lnCiNit,
                'tcNroPago' => $lcNroPago,
                "tnMontoClienteEmpresa" => $lnMontoClienteEmpresa,
                "tcCorreo" => $lcCorreo,
                'tcUrlCallBack' => $lcUrlCallBack,
                "tcUrlReturn" => $lcUrlReturn,
                'taPedidoDetalle' => $laPedidoDetalle
            ];

            Log::info('PagoController::generarQR - Enviando datos a PagoFácil', [
                'url' => $lcUrl,
                'body' => $laBody
            ]);

            $loClient = new Client();
            $loResponse = $loClient->post($lcUrl, [
                'headers' => $laHeader,
                'json' => $laBody
            ]);

            $laResult = json_decode($loResponse->getBody()->getContents());
            
            Log::info('PagoController::generarQR - Respuesta de PagoFácil', [
                'result' => $laResult
            ]);

            if (!$laResult || !isset($laResult->values)) {
                throw new \Exception('Respuesta inválida de PagoFácil');
            }

            // Extraer datos del response de PagoFácil
            $laRawValues = explode(";", $laResult->values);
            $lcNumeroTransaccion = $laRawValues[0] ?? null;
            
            // Extraer QR del JSON anidado
            $laQrImage = null;
            $expirationDate = null;
            
            if (count($laRawValues) > 1) {
                $qrData = json_decode($laRawValues[1], true);
                if ($qrData && isset($qrData['qrImage'])) {
                    $laQrImage = $qrData['qrImage'];
                    $expirationDate = $qrData['expirationDate'] ?? null;
                }
            }
            
            if (!$laQrImage) {
                throw new \Exception('No se pudo extraer la imagen QR de la respuesta');
            }

            Log::info('PagoController::generarQR - QR extraído correctamente', [
                'transaction_id' => $lcNumeroTransaccion,
                'qr_length' => strlen($laQrImage),
                'expiration' => $expirationDate
            ]);

            // Actualizar pago con el ID de transacción solamente
            $pago->update([
                'pagofacil_transaction_id' => $lcNumeroTransaccion,
                'estado' => 'procesando'
            ]);

            Log::info('PagoController::generarQR - QR generado exitosamente', [
                'pago_id' => $pago->id,
                'transaction_id' => $lcNumeroTransaccion
            ]);

            $responseData = [
                'success' => true,
                'qr_image' => $laQrImage,
                'transaction_id' => $lcNumeroTransaccion,
                'expiration_date' => $expirationDate ?: now()->addHours(2)->toISOString(),
                'message' => 'QR generado exitosamente'
            ];

            Log::info('PagoController::generarQR - Preparando respuesta', [
                'response_data' => [
                    'success' => $responseData['success'],
                    'transaction_id' => $responseData['transaction_id'],
                    'qr_length' => strlen($responseData['qr_image']),
                    'expiration_date' => $responseData['expiration_date']
                ]
            ]);

            // Respuesta según el tipo de request
            if ($request->expectsJson()) {
                return response()->json($responseData);
            }
            
            // Para requests web, devolver con flash data
            return redirect()->back()->with('qr_data', $responseData);

        } catch (\Exception $e) {
            Log::error('PagoController::generarQR - Error al generar el QR', [
                'pago_id' => $pagoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $errorMessage = 'Error al generar QR: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()->withErrors(['qr_error' => $errorMessage]);
        }
    }
    

    /**
     * Verificar estado del pago (igual que ReserveController)
     */
    public function verificarPago(Request $request)
    {
        $numeroTransaccion = $request->numeroTransaccion;

        try {
            Log::info('PagoController::verificarPago - Verificando pago', [
                'numeroTransaccion' => $numeroTransaccion
            ]);

            $client = new Client();
            $response = $client->post('https://serviciostigomoney.pagofacil.com.bo/api/servicio/consultartransaccion', [
                'headers' => ['Accept' => 'application/json'],
                'json' => ['TransaccionDePago' => $numeroTransaccion]
            ]);

            $data = json_decode($response->getBody()->getContents());

            // Si el estado es 5 (completado), actualizar el pago en la base de datos
            if (isset($data->values) && $data->values->EstadoTransaccion == 5) {
                $pago = Pago::where('pagofacil_transaction_id', $numeroTransaccion)->first();
                if ($pago && $pago->estado !== 'pagado') {
                    $pago->update([
                        'estado' => 'pagado'
                        
                    ]);
                    
                    Log::info('PagoController::verificarPago - Pago actualizado a pagado', [
                        'pago_id' => $pago->id
                    ]);
                    

                     $this->procesarNotificacionPago($pago, 'success');
                    // Procesar lógica adicional
                    $this->procesarPagoExitoso($pago);
                }
            }

            return response()->json([
                'data' => $data->values,
            ]);

        } catch (\Exception $e) {
            Log::error('PagoController::verificarPago - Error al verificar pago', [
                'numeroTransaccion' => $numeroTransaccion,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'pagado' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Callback de PagoFácil
     */
    public function callback(Request $request)
    {
        Log::info('PagoController::callback - Callback de PagoFácil recibido', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip()
        ]);

        try {
            $pedidoId = $request->input('PedidoID');
            $estado = $request->input('Estado');
            $metodoPago = $request->input('MetodoPago');
            $fecha = $request->input('Fecha');
            $hora = $request->input('Hora');

            // Buscar el pago por transaction_id
            $pago = Pago::where('pagofacil_transaction_id', $pedidoId)->first();

            if (!$pago) {
                Log::error('PagoController::callback - Pago no encontrado', [
                    'pedido_id' => $pedidoId
                ]);
                
                return response()->json([
                    'error' => 1,
                    'estatus' => 0,
                    'message' => 'Pago no encontrado',
                    'values' => false
                ]);
            }

            DB::beginTransaction();

            // Actualizar estado del pago
            $estadoPago = $this->mapearEstadoPago($estado);
            
            $pago->update([
                'estado' => $estadoPago
            ]);

            $this->procesarNotificacionPago($pago, $estado);
           
            // Si el pago fue exitoso, ejecutar lógica adicional
            if ($estadoPago === 'pagado') {
                $this->procesarPagoExitoso($pago);
            }

            DB::commit();

            Log::info('PagoController::callback - Callback procesado exitosamente', [
                'pago_id' => $pago->id,
                'estado' => $estadoPago
            ]);

            return response()->json([
                'error' => 0,
                'estatus' => 1,
                'message' => 'Pago procesado correctamente',
                'values' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('PagoController::callback - Error en callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 1,
                'estatus' => 0,
                'message' => 'Error interno del servidor',
                'values' => false
            ]);
        }
    }

    /**
     * Página de confirmación de pago
     */
    private function procesarNotificacionPago($pago, $status)
    {
        try {
            // Determinar el tipo de notificación según el estado
            $tipoNotificacion = match($status) {
                '1', 'completed', 'success', 'COMPLETADO' => 'pago_exitoso',
                '0', 'failed', 'error', 'FALLIDO' => 'pago_fallido',
                'pending', 'processing', 'PENDIENTE' => 'pago_pendiente',
                'VENCIDO' => 'pago_fallido',
                'CANCELADO' => 'pago_fallido',
                default => 'pago_pendiente'
            };

            // Crear notificaciones para usuarios autorizados
            Notificacion::crearNotificacionPago($pago, $tipoNotificacion);

            Log::info("Notificación de pago creada: {$tipoNotificacion} para pago ID: {$pago->id}");

        } catch (\Exception $e) {
            Log::error('Error creando notificación de pago:', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function confirmacion(Request $request)
    {
        return Inertia::render('Pagos/Confirmacion', [
            'mensaje' => 'Su pago está siendo procesado. Recibirá una confirmación pronto.'
        ]);
    }

    /**
     * Obtener usuario asociado al pago
     */
    private function obtenerUsuarioPago($pago)
    {
        try {
            // Si hay un contrato, obtener el primer usuario
            if ($pago->contrato && $pago->contrato->users()->count() > 0) {
                return $pago->contrato->users()->first();
            }

            // Si hay una reserva, obtener el usuario
            if ($pago->reserva && $pago->reserva->user) {
                return $pago->reserva->user;
            }

            // Como fallback, usar el usuario autenticado
            return Auth::user();

        } catch (\Exception $e) {
            Log::error('PagoController::obtenerUsuarioPago - Error', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtener descripción del producto basada en el tipo de pago
     */
    private function obtenerDescripcionProducto($pago)
    {
        $descripciones = [
            'contrato' => 'Pago de Contrato',
            'garantia' => 'Pago de Garantía',
            'reserva' => 'Pago de Reserva'
        ];

        return $descripciones[$pago->tipo_pago] ?? 'Pago de Servicio';
    }

    /**
     * Mapear estado de PagoFácil a estado interno
     */
    private function mapearEstadoPago($estadoPagoFacil)
    {
        $mapeo = [
            'COMPLETADO' => 'pagado',
            'PENDIENTE' => 'procesando',
            'FALLIDO' => 'fallido',
            'VENCIDO' => 'vencido',
            'CANCELADO' => 'cancelado'
        ];

        return $mapeo[$estadoPagoFacil] ?? 'pendiente';
    }

    /**
     * Procesar lógica adicional cuando un pago es exitoso
     */
    private function procesarPagoExitoso($pago)
    {
        Log::info('PagoController::procesarPagoExitoso - Procesando pago exitoso', [
            'pago_id' => $pago->id,
            'tipo_pago' => $pago->tipo_pago
        ]);

        try {
            // Si es un pago de contrato, verificar si todos los pagos están completos
            if ($pago->tipo_pago === 'contrato' && $pago->contrato) {
                $pagosPendientes = $pago->contrato->contratopagos()
                    ->where('estado', '!=', 'pagado')
                    ->count();

                if ($pagosPendientes === 0) {
                    Log::info('PagoController::procesarPagoExitoso - Todos los pagos del contrato completados', [
                        'contrato_id' => $pago->contrato->id
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('PagoController::procesarPagoExitoso - Error', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}