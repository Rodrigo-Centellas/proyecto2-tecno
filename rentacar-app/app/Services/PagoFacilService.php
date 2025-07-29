<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagoFacilService
{
    private $baseUrl;
    private $tokenService;
    private $tokenSecret;
    private $commerceId;
    private $accessToken;

       protected $pagoFacilService;


    public function __construct()
    {
        $this->baseUrl = env('PAGOFACIL_BASE_URL', 'https://serviciostigomoney.pagofacil.com.bo/api');
        $this->tokenService = env('PAGOFACIL_TOKEN_SERVICE');
        $this->tokenSecret = env('PAGOFACIL_TOKEN_SECRET');
        $this->commerceId = env('PAGOFACIL_COMMERCE_ID');
        
        Log::info('PagoFacilService::__construct', [
            'base_url' => $this->baseUrl,
            'tiene_token_service' => !empty($this->tokenService),
            'tiene_token_secret' => !empty($this->tokenSecret),
            'tiene_commerce_id' => !empty($this->commerceId)
        ]);
    }

    /**
     * Autenticar con PagoFacil
     */
    private function autenticar()
    {
        if ($this->accessToken) {
            return true;
        }

        try {
            Log::info('PagoFacilService::autenticar - Iniciando autenticación');

            $response = Http::timeout(30)->post($this->baseUrl . '/servicio/login', [
                'TokenService' => $this->tokenService,
                'TokenSecret' => $this->tokenSecret
            ]);

            $data = $response->json();

            Log::info('PagoFacilService::autenticar - Respuesta recibida', [
                'status_code' => $response->status(),
                'response_data' => $data
            ]);

            if ($response->successful() && isset($data['error']) && $data['error'] == 0) {
                $this->accessToken = $data['values'];
                Log::info('PagoFacilService::autenticar - Autenticación exitosa');
                return true;
            }

            Log::error('PagoFacilService::autenticar - Error en autenticación', [
                'response' => $data
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('PagoFacilService::autenticar - Excepción en autenticación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Generar QR para pago
     */
    public function generarQR($pago, $usuario)
    {
        try {
            Log::info('PagoFacilService::generarQR - Iniciando generación de QR', [
                'pago_id' => $pago->id,
                'usuario_id' => $usuario->id
            ]);

            // Autenticar primero
            if (!$this->autenticar()) {
                return [
                    'success' => false,
                    'message' => 'Error de autenticación con PagoFácil'
                ];
            }

            // Preparar datos del pedido
            $pedidoDetalle = [
                [
                    'Serial' => 1,
                    'Producto' => $this->obtenerDescripcionProducto($pago),
                    'Cantidad' => 1,
                    'Precio' => (string) $pago->monto,
                    'Descuento' => 0,
                    'Total' => (string) $pago->monto
                ]
            ];

            $datosQR = [
                'tcCommerceID' => $this->commerceId,
                'tcNroPago' => (string) $pago->id,
                'tcNombreUsuario' => $usuario->name,
                'tnCiNit' => $usuario->ci ?? 0,
                'tnTelefono' => $usuario->telefono ?? 70000000,
                'tcCorreo' => $usuario->email,
                'tcCodigoClienteEmpresa' => (string) $usuario->id,
                'tnMontoClienteEmpresa' => (string) $pago->monto,
                'tnMoneda' => 2, // 2 = Bolivianos
                'tcUrlCallBack' => url('/pagos/callback'),
                'tcUrlReturn' => url('/pagos/confirmacion'),
                'taPedidoDetalle' => $pedidoDetalle
            ];

            Log::info('PagoFacilService::generarQR - Datos preparados', [
                'datos_qr' => $datosQR
            ]);

            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . '/servicio/pagoqr', $datosQR);

            $data = $response->json();

            Log::info('PagoFacilService::generarQR - Respuesta de PagoFácil', [
                'status_code' => $response->status(),
                'response_data' => $data
            ]);

            if ($response->successful() && isset($data['error']) && $data['error'] == 0) {
                // Procesar la respuesta exitosa
                $values = $data['values'];
                
                // Verificar si values es un string JSON
                if (is_string($values)) {
                    // Separar el ID de transacción del JSON
                    $parts = explode(';', $values, 2);
                    $transactionId = $parts[0];
                    
                    if (count($parts) > 1) {
                        $qrData = json_decode($parts[1], true);
                        
                        if ($qrData && isset($qrData['qrImage'])) {
                            return [
                                'success' => true,
                                'transaction_id' => $transactionId,
                                'qr_id' => $qrData['id'] ?? null,
                                'qr_image' => $qrData['qrImage'],
                                'expiration_date' => $qrData['expirationDate'] ?? null,
                                'message' => 'QR generado exitosamente'
                            ];
                        }
                    }
                } elseif (is_array($values) && isset($values['qrImage'])) {
                    // Si values ya es un array
                    return [
                        'success' => true,
                        'transaction_id' => $values['id'] ?? $pago->id,
                        'qr_id' => $values['id'] ?? null,
                        'qr_image' => $values['qrImage'],
                        'expiration_date' => $values['expirationDate'] ?? null,
                        'message' => 'QR generado exitosamente'
                    ];
                }

                Log::error('PagoFacilService::generarQR - Formato de respuesta inesperado', [
                    'values' => $values
                ]);

                return [
                    'success' => false,
                    'message' => 'Formato de respuesta inesperado de PagoFácil'
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Error desconocido al generar QR'
            ];

        } catch (\Exception $e) {
            Log::error('PagoFacilService::generarQR - Excepción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Consultar estado de transacción
     */
    public function consultarTransaccion($transactionId)
    {
        try {
            Log::info('PagoFacilService::consultarTransaccion - Iniciando consulta', [
                'transaction_id' => $transactionId
            ]);

            $response = Http::timeout(30)->post($this->baseUrl . '/servicio/consultartransaccion', [
                'TransaccionDePago' => $transactionId
            ]);

            $data = $response->json();

            Log::info('PagoFacilService::consultarTransaccion - Respuesta recibida', [
                'status_code' => $response->status(),
                'response_data' => $data
            ]);

            if ($response->successful() && isset($data['error']) && $data['error'] == 0) {
                $values = $data['values'];
                
                return [
                    'success' => true,
                    'estado_pago' => $values['estadoPago'] ?? 0,
                    'motivo' => $values['motivo'] ?? 0,
                    'transaccion_tigo' => $values['transaccionTigo'] ?? '',
                    'message_estado' => $values['messageEstado'] ?? '',
                    'message' => 'Consulta realizada correctamente'
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Error al consultar transacción'
            ];

        } catch (\Exception $e) {
            Log::error('PagoFacilService::consultarTransaccion - Excepción', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validar datos del callback
     */
    public function validarCallback($data)
    {
        $camposRequeridos = ['PedidoID', 'Estado', 'MetodoPago', 'Fecha', 'Hora'];
        
        foreach ($camposRequeridos as $campo) {
            if (!isset($data[$campo]) || empty($data[$campo])) {
                Log::warning('PagoFacilService::validarCallback - Campo faltante', [
                    'campo' => $campo,
                    'data' => $data
                ]);
                return false;
            }
        }
        
        return true;
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
}