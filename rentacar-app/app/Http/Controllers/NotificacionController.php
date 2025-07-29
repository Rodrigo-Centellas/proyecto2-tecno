<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificacionController extends Controller
{
    /**
     * Mostrar todas las notificaciones
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $tipo = $request->get('tipo');

        $notificaciones = Notificacion::query()
            ->with('user:id,name,email')
            ->when($search, fn($q) =>
                $q->where('mensaje', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($query) => 
                    $query->where('name', 'like', "%{$search}%")
                  )
            )
            ->when($tipo, fn($q) => $q->where('tipo', $tipo))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Agregar atributos calculados
        $notificaciones->getCollection()->transform(function ($notificacion) {
            $notificacion->icono = $notificacion->icono;
            $notificacion->color = $notificacion->color;
            $notificacion->tiempo_transcurrido = $notificacion->tiempo_transcurrido;
            return $notificacion;
        });

        return Inertia::render('Notificaciones/Index', [
            'notificaciones' => $notificaciones,
            'filters' => $request->only(['search', 'tipo']),
            'tipos' => $this->getTiposNotificacion(),
        ]);
    }

    /**
     * API: Obtener notificaciones recientes para polling
     */
    public function obtenerRecientes(Request $request)
    {
        $usuario = $request->user();
        
        // Solo usuarios con roles específicos pueden ver notificaciones
        if (!$usuario->hasAnyRole(['Administrador', 'Empleado Operativo'])) {
            return response()->json([
                'notificaciones' => [],
                'total' => 0
            ]);
        }

        // Obtener timestamp de la última consulta
        $ultimaConsulta = $request->get('ultima_consulta');
        
        $query = Notificacion::query()
            ->where('user_id', $usuario->id)
            ->orderBy('created_at', 'desc');

        // Si hay timestamp, obtener solo las nuevas
        if ($ultimaConsulta) {
            $query->where('created_at', '>', $ultimaConsulta);
        } else {
            // Primera consulta: últimas 24 horas
            $query->where('created_at', '>=', now()->subDay());
        }

        $notificaciones = $query->limit(10)->get();

        // Agregar atributos calculados
        $notificaciones->transform(function ($notificacion) {
            return [
                'id' => $notificacion->id,
                'tipo' => $notificacion->tipo,
                'mensaje' => $notificacion->mensaje,
                'fecha' => $notificacion->fecha,
                'icono' => $notificacion->icono,
                'color' => $notificacion->color,
                'tiempo_transcurrido' => $notificacion->tiempo_transcurrido,
                'created_at' => $notificacion->created_at->toISOString()
            ];
        });

        return response()->json([
            'notificaciones' => $notificaciones,
            'total' => $notificaciones->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * API: Obtener conteo de notificaciones recientes
     */
    public function conteoRecientes(Request $request)
    {
        $usuario = $request->user();
        
        if (!$usuario->hasAnyRole(['Administrador', 'Empleado Operativo'])) {
            return response()->json(['total' => 0]);
        }

        $total = Notificacion::where('user_id', $usuario->id)
            ->recientes()
            ->count();

        return response()->json(['total' => $total]);
    }

    /**
     * API: Crear notificación de prueba (solo para testing)
     */
    public function crearPrueba(Request $request)
    {
        if (!$request->user()->hasRole('Administrador')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $notificacion = Notificacion::create([
            'user_id' => $request->user()->id,
            'fecha' => now()->toDateString(),
            'tipo' => 'sistema',
            'mensaje' => 'Notificación de prueba generada a las ' . now()->format('H:i:s')
        ]);

        return response()->json([
            'success' => true,
            'notificacion' => [
                'id' => $notificacion->id,
                'tipo' => $notificacion->tipo,
                'mensaje' => $notificacion->mensaje,
                'icono' => $notificacion->icono,
                'color' => $notificacion->color,
                'tiempo_transcurrido' => $notificacion->tiempo_transcurrido
            ]
        ]);
    }

    /**
     * Obtener tipos de notificación disponibles
     */
    private function getTiposNotificacion()
    {
        return [
            'pago_exitoso' => 'Pagos Exitosos',
            'pago_fallido' => 'Pagos Fallidos',
            'pago_pendiente' => 'Pagos Pendientes',
            'contrato_vencido' => 'Contratos Vencidos',
            'mantenimiento' => 'Mantenimiento',
            'sistema' => 'Sistema'
        ];
    }

    /**
     * Limpiar notificaciones antiguas (comando para cron)
     */
    public function limpiarAntiguas()
    {
        $eliminadas = Notificacion::where('created_at', '<', now()->subDays(30))->delete();
        
        return response()->json([
            'success' => true,
            'eliminadas' => $eliminadas,
            'mensaje' => "Se eliminaron {$eliminadas} notificaciones antiguas"
        ]);
    }
}