<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FixedQrPagoController extends Controller
{
    /**
     * Devuelve el QR fijo como data-uri.
     */
 public function generar(Pago $pago)
{
    try {
        if ($pago->estado !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'El pago no está pendiente y no se puede mostrar el QR fijo.'
            ], 400);
        }

        // Ruta al archivo QR en resources
        $path = resource_path('images/qr.jpg');
        
        // Verificar si el archivo existe
        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el QR fijo en resources/images/qr.jpg'
            ], 404);
        }

        // Leer el contenido del archivo
        $contents = file_get_contents($path);
        $base64 = base64_encode($contents);
        
        // Detectar el tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);

        return response()->json([
            'success' => true,
            'qr_image' => "data:{$mime};base64,{$base64}",
            'transaction_id' => null,
            'expiration_date' => null,
            'message' => 'QR fijo cargado correctamente',
        ]);
    } catch (\Throwable $e) {
        Log::error('FixedQrPagoController::generar', [
            'pago_id' => $pago->id,
            'error' => $e->getMessage(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Error interno al cargar el QR fijo',
        ], 500);
    }
}

    /**
     * Marca el pago como pagado usando el flujo del QR fijo.
     */
    public function marcarPagado(Pago $pago, Request $request)
    {
        try {
            if ($pago->estado === 'pagado') {
                return response()->json([
                    'success' => false,
                    'message' => 'El pago ya está marcado como pagado.'
                ], 400);
            }

            DB::transaction(function () use ($pago) {
                $pago->update([
                    'estado' => 'pagado',
                    'metodo_pago' => 'qr',
                    // Como es fijo no hay transaction real, podés dejar pagofacil_transaction_id null
                ]);

                // Si tenés lógica de notificaciones reuse la función existente si está accesible:
                if (method_exists($this, 'procesarNotificacionPago')) {
                    $this->procesarNotificacionPago($pago, 'completed');
                } else {
                    // Si tu modelo o helper centraliza notificaciones, llamalo aquí.
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Pago marcado como pagado correctamente',
                'pago' => $pago->fresh(),
            ]);
        } catch (\Throwable $e) {
            Log::error('FixedQrPagoController::marcarPagado', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar el pago como pagado',
            ], 500);
        }
    }
}
