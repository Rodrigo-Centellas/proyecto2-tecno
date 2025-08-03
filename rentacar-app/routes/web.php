<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VehiculoMantenimientoController;
//
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\GaranteController;
use App\Http\Controllers\FrecuenciaPagoController;
use App\Http\Controllers\NroCuentaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReportePagoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClausulaController;
use App\Http\Controllers\FixedQrPagoController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PagoQrController;
//

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth','role:Administrador,Empleado Operativo', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/reportes', fn() => Inertia::render('Reportes/Index'))->name('reportes.index');
    Route::get('/reportes/pagos', [ReportePagoController::class, 'index'])->name('reportes.pagos.index');
    Route::get('/reportes/pagos/pdf', [ReportePagoController::class, 'exportPdf'])->name('reportes.pagos.pdf');
    Route::get('/reportes/pagos/excel', [ReportePagoController::class, 'exportExcel'])->name('reportes.pagos.excel');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('mantenimientos', MantenimientoController::class);
    Route::resource('vehiculos', VehiculoController::class);
    Route::resource('registro-mantenimientos', VehiculoMantenimientoController::class);
    Route::get('/vehiculos/show', [VehiculoController::class, 'show'])->name('vehiculos.show');
    Route::resource('reservas', ReservaController::class);

    //
    Route::resource('contratos', ContratoController::class);
    Route::resource('garantes', GaranteController::class);
    Route::resource('frecuencia-pagos', FrecuenciaPagoController::class);
    Route::resource('nro-cuentas', NroCuentaController::class);
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('clausulas', ClausulaController::class);

    // === RUTAS DE PAGOS ACTUALIZADAS ===
    
    // IMPORTANTE: Las rutas específicas DEBEN ir ANTES del resource para evitar conflictos
    
    // Rutas específicas de pagos (ANTES del resource)
    Route::get('/pagos/{pago}/pagar', [PagoController::class, 'pagar'])
        ->name('pagos.pagar');

    /* Generar pagos desde otras entidades */
    Route::post('/pagos/generar-desde-reserva/{reserva}',   [PagoController::class, 'generarDesdeReserva'])
        ->name('pagos.desdeReserva');
    Route::post('/pagos/generar-desde-contrato/{contrato}', [PagoController::class, 'generarDesdeContrato'])
        ->name('pagos.desdeContrato');

    /* ---------- Lógica QR (PagoQrController) ---------- */
    Route::post('/pagos/{pago}/qr',  [PagoQrController::class, 'generarQR'])
        ->name('pagos.qr.generar');
    Route::post('/pagos/qr/verificar', [PagoQrController::class, 'verificarPago'])
        ->name('pagos.qr.verificar');
    Route::get ('/pagos/qr/confirmacion', [PagoQrController::class, 'confirmacion'])
        ->name('pagos.qr.confirmacion');
    Route::post('/pagos/{pago}/efectivo', [PagoController::class, 'pagarEfectivo'])
    ->name('pagos.efectivo');
    // Resource routes para pagos (DESPUÉS de las rutas específicas)
    Route::resource('pagos', PagoController::class)->except(['create', 'edit']);
    Route::post('/pagos/{pago}/qr-fijo', [FixedQrPagoController::class, 'generar'])->name('pagos.qr-fijo');
Route::post('/pagos/{pago}/marcar-pagado', [FixedQrPagoController::class, 'marcarPagado'])->name('pagos.marcar-pagado');
    
    // === FIN RUTAS DE PAGOS ===

    // 🔔 RUTAS DE NOTIFICACIONES (CORREGIDAS)
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::get('/api/notificaciones/recientes', [NotificacionController::class, 'obtenerRecientes'])->name('api.notificaciones.recientes');
    Route::get('/api/notificaciones/conteo', [NotificacionController::class, 'conteoRecientes'])->name('api.notificaciones.conteo');
    Route::delete('/api/notificaciones/limpiar', [NotificacionController::class, 'limpiarAntiguas'])->name('api.notificaciones.limpiar')->middleware('role:Administrador');

    Route::get('/search', [GlobalSearchController::class, 'search'])->name('global.search');
});

// Callback público de PagoFácil (sin autenticación)
Route::post('/pagos/callback', [PagoController::class, 'callback'])->name('pagos.callback');

require __DIR__ . '/auth.php';