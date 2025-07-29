<?php

use App\Http\Controllers\NotificacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas API para pagos (con autenticación Sanctum)
Route::middleware(['auth:sanctum'])->prefix('pagos')->group(function () {
    Route::post('/{pago}/generar-qr', [PagoController::class, 'generarQR'])->name('api.pagos.generar-qr');
    Route::get('/{pago}/consultar-estado', [PagoController::class, 'consultarEstado'])->name('api.pagos.consultar-estado');
});

// Rutas API públicas para pagos (sin autenticación para compatibilidad)
Route::prefix('pagos')->group(function () {
    Route::post('/verificar-pago', [PagoController::class, 'verificarPago'])->name('api.pagos.verificar-pago');
});

// Callback de PagoFácil (público)
Route::post('/pagofacil/callback', [PagoController::class, 'callback'])->name('api.pagofacil.callback');

Route::middleware(['auth:sanctum'])->prefix('notificaciones')->group(function () {
    // Polling de notificaciones
    Route::get('/recientes', [NotificacionController::class, 'obtenerRecientes'])->name('api.notificaciones.recientes');
    
    // Conteo de notificaciones
    Route::get('/conteo', [NotificacionController::class, 'conteoRecientes'])->name('api.notificaciones.conteo');
    
    // Crear notificación de prueba (solo para testing)
    Route::post('/prueba', [NotificacionController::class, 'crearPrueba'])->name('api.notificaciones.prueba');
    
    // Limpiar notificaciones antiguas
    Route::delete('/limpiar', [NotificacionController::class, 'limpiarAntiguas'])->name('api.notificaciones.limpiar');
});