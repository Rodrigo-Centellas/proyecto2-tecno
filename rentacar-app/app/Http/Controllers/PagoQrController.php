<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB; // si no está al tope del archivo

use App\Models\Pago;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class PagoQrController extends Controller
{
private function obtenerAccessToken(): string
{
    // Cacheamos por 55 minutos
    return Cache::remember('pagofacil_access_token', 55 * 60, function () {
        // Hardcodeado según tu .env
        $tokenService = '51247fae280c20410824977b0781453df59fad5b23bf2a0d14e884482f91e09078dbe5966e0b970ba696ec4caf9aa5661802935f86717c481f1670e63f35d5041c31d7cc6124be82afedc4fe926b806755efe678917468e31593a5f427c79cdf016b686fca0cb58eb145cf524f62088b57c6987b3bb3f30c2082b640d7c52907';
        $tokenSecret  = '9E7BC239DDC04F83B49FFDA5';

        $client = new Client();
        $response = $client->post('https://serviciostigomoney.pagofacil.com.bo/api/servicio/login', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'TokenService' => $tokenService,
                'TokenSecret'  => $tokenSecret,
            ],
            'timeout' => 15,
        ]);

        $body = $response->getBody()->getContents();
        Log::info('PagoQrController::obtenerAccessToken - respuesta login raw', ['raw' => $body]);

        $decoded = json_decode($body);
        if (!$decoded || !isset($decoded->values) || ($decoded->error ?? 1) == 1) {
            $msg = $decoded->messageSistema ?? $decoded->message ?? 'Error en login PagoFácil';
            throw new \Exception("Login PagoFácil fallido: {$msg}");
        }

        return $decoded->values; // access token
    });
}

public function generarQR(Request $request, Pago $pago)
{
    try {
        $this->autorizarPendiente($pago);
        $usuario = $this->obtenerUsuarioPago($pago) ?? Auth::user();

        // Monto de prueba en local
        $monto = app()->isLocal() ? 0.01 : (float) $pago->monto;

        // Construir payload igual que en ReserveController que funciona
        $payload = [
            "tcCommerceID"            => 'd029fa3a95e174a19934857f535eb9427d967218a36ea014b70ad704bc6c8d1c',
            "tnMoneda"                => 2,
            "tnTelefono"              => (int) preg_replace('/\D/', '', $usuario->phone ?? '70000000'),
            "tcNombreUsuario"         => $usuario->name,
            "tnCiNit"                => (int) preg_replace('/\D/', '', $usuario->ci ?? $usuario->nit ?? '0'),
            "tcNroPago"              => "PAG-{$pago->id}-" . rand(100000, 999999),
            "tnMontoClienteEmpresa"  => $monto,
            "tcCorreo"               => $usuario->email,
            "tcUrlCallBack"          => url('/pagos/qr/callback'),
            "tcUrlReturn"            => url('/pagos/qr/confirmacion'),
            "taPedidoDetalle"        => [[
                'Serial'    => (string) $pago->id,
                'Producto'  => $this->obtenerDescripcionProducto($pago),
                'Cantidad'  => "1",
                'Precio'    => (string) $monto,
                'Descuento' => "0",
                'Total'     => (string) $monto,
            ]],
        ];

        Log::info('PagoQrController::generarQR - Enviando a PagoFácil (generarqrv2)', [
            'payload' => $payload,
            'pago_id' => $pago->id,
            'user_id' => Auth::id(),
        ]);

        $client = new \GuzzleHttp\Client();
        $response = $client->post(
            'https://serviciostigomoney.pagofacil.com.bo/api/servicio/generarqrv2',
            [
                'headers' => ['Accept' => 'application/json'], // sin Content-Type
                'json'    => $payload,
                'timeout' => 30,
            ]
        );

        $rawBody = $response->getBody()->getContents();
        Log::info('PagoQrController::generarQR - Response raw generarqrv2', [
            'raw' => $rawBody,
            'pago_id' => $pago->id,
        ]);

        $result = json_decode($rawBody);

        if (!$result) {
            throw new \Exception('No se pudo decodificar JSON de PagoFácil. Raw: ' . substr($rawBody, 0, 1000));
        }

        if ((isset($result->error) && $result->error == 1) || !isset($result->values) || empty($result->values)) {
            $detalle = $result->messageSistema ?? $result->message ?? 'Sin detalle'; 
            throw new \Exception("PagoFácil rechazó la solicitud. Detalle: {$detalle}");
        }

        $parts = explode(';', $result->values);
        if (count($parts) < 2) {
            throw new \Exception('Formato inesperado en values: ' . $result->values);
        }

        $nroTx = $parts[0];
        $jsonQr = $parts[1];
        $qrData = json_decode($jsonQr);
        if (!($qrData?->qrImage)) {
            throw new \Exception('No se encontró qrImage en la respuesta: ' . $jsonQr);
        }

        // Actualizar pago
        $pago->update([
            'pagofacil_transaction_id' => $nroTx,
            'estado'                   => 'procesando',
        ]);

        Log::info('PagoQrController::generarQR - Éxito generarqrv2', [
            'pago_id'        => $pago->id,
            'transaction_id' => $nroTx,
        ]);

        return response()->json([
            'success'           => true,
            'qrImage'           => $qrData->qrImage,
            'qr_image'          => $qrData->qrImage,
            'numeroTransaccion' => $nroTx,
            'transaction_id'    => $nroTx,
            'expiration_date'   => $qrData->expirationDate ?? now()->addMinutes(10)->toISOString(),
            'message'           => 'QR generado exitosamente',
        ]);
    } catch (\Throwable $e) {
        Log::error('PagoQrController::generarQR', [
            'pago_id' => $pago->id,
            'error'   => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: 'Error desconocido al generar QR',
        ], 500);
    }
}



    public function verificarPago(Request $request)
    {
        $nro = $request->numeroTransaccion;

        try {
            $client   = new Client();
            $response = $client->post(
                'https://serviciostigomoney.pagofacil.com.bo/api/servicio/consultartransaccion',
                [
                    'headers' => ['Accept' => 'application/json'],
                    'json'    => ['TransaccionDePago' => $nro],
                ]
            );

            $data = json_decode($response->getBody()->getContents());

            if (isset($data->values) && $data->values->EstadoTransaccion == 5) {
                $pago = Pago::where('pagofacil_transaction_id', $nro)->first();
                if ($pago && $pago->estado !== 'pagado') {
                    $pago->update(['estado' => 'pagado']);
                    $this->procesarNotificacionPago($pago, 'success');
                    $this->procesarPagoExitoso($pago);
                }
            }

            return response()->json(['data' => $data->values ?? $data]);

        } catch (\Throwable $e) {
            Log::error('PagoQrController::verificarPago', [
                'nro'   => $nro,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'pagado' => false,
                'error'  => $e->getMessage(),
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        try {
            $pedidoId = $request->input('PedidoID');
            $estado   = $request->input('Estado');

            $pago = Pago::where('pagofacil_transaction_id', $pedidoId)->firstOrFail();

            DB::transaction(function () use ($estado, $pago) {
                $pago->update(['estado' => $this->mapearEstadoPago($estado)]);
                $this->procesarNotificacionPago($pago, $estado);
                if ($pago->estado === 'pagado') {
                    $this->procesarPagoExitoso($pago);
                }
            });

            return ['error' => 0, 'estatus' => 1, 'message' => 'OK', 'values' => true];

        } catch (\Throwable $e) {
            Log::error('PagoQrController::callback', ['error' => $e->getMessage()]);

            return ['error' => 1, 'estatus' => 0, 'message' => 'Error', 'values' => false];
        }
    }

    public function confirmacion()
    {
        return inertia('Pagos/Confirmacion', [
            'mensaje' => 'Su pago está siendo procesado. Recibirá una confirmación pronto.',
        ]);
    }

    /* ============================================================
     |  HELPERS
     * ============================================================*/
    private function autorizarPendiente(Pago $pago): void
    {
        if ($pago->estado !== 'pendiente') {
            abort(400, 'Este pago ya no está disponible para procesar.');
        }

        if (Auth::user()->hasRole('Cliente')) {
            $tieneAcceso =
                ($pago->contrato && $pago->contrato->users()->where('user_id', Auth::id())->exists()) ||
                ($pago->reserva  && $pago->reserva->user_id === Auth::id());

            abort_unless($tieneAcceso, 403, 'No autorizado.');
        }
    }

    private function obtenerUsuarioPago($pago)
    {
        if ($pago->contrato && $pago->contrato->users()->count() > 0) {
            return $pago->contrato->users()->first();
        }
        if ($pago->reserva && $pago->reserva->user) {
            return $pago->reserva->user;
        }
        return Auth::user();
    }

    private function obtenerDescripcionProducto($pago)
    {
        return [
            'contrato'  => 'Pago de Contrato',
            'garantia'  => 'Pago de Garantía',
            'reserva'   => 'Pago de Reserva',
        ][$pago->tipo_pago] ?? 'Pago de Servicio';
    }

    private function mapearEstadoPago($estadoPF)
    {
        return [
            'COMPLETADO' => 'pagado',
            'PENDIENTE'  => 'procesando',
            'FALLIDO'    => 'fallido',
            'VENCIDO'    => 'vencido',
            'CANCELADO'  => 'cancelado',
        ][$estadoPF] ?? 'pendiente';
    }

    private function procesarNotificacionPago($pago, $status)
    {
        try {
            $tipo = match($status) {
                '1', 'completed', 'success', 'COMPLETADO' => 'pago_exitoso',
                '0', 'failed', 'error', 'FALLIDO'        => 'pago_fallido',
                'pending', 'processing', 'PENDIENTE'     => 'pago_pendiente',
                'VENCIDO', 'CANCELADO'                   => 'pago_fallido',
                default                                  => 'pago_pendiente',
            };
            Notificacion::crearNotificacionPago($pago, $tipo);
        } catch (\Throwable $e) {
            Log::error('Error creando notificación', ['pago_id' => $pago->id, 'error' => $e->getMessage()]);
        }
    }

    private function procesarPagoExitoso($pago)
    {
        try {
            if ($pago->tipo_pago === 'contrato' && $pago->contrato) {
                $pendientes = $pago->contrato->contratopagos()
                    ->where('estado', '!=', 'pagado')
                    ->count();

                if ($pendientes === 0) {
                    Log::info('Todos los pagos del contrato completados', ['contrato_id' => $pago->contrato->id]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('procesarPagoExitoso', ['pago_id' => $pago->id, 'error' => $e->getMessage()]);
        }
    }
}
