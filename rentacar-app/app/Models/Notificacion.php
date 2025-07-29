<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $fillable = [
        'fecha', 
        'mensaje', 
        'tipo', 
        'user_id'
    ];

    protected $casts = [
        'fecha' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para notificaciones por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para notificaciones recientes (últimas 24 horas)
     */
    public function scopeRecientes($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }

    /**
     * Scope para notificaciones de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha', now()->toDateString());
    }

    /**
     * Accessor para formatear el tiempo transcurrido
     */
    public function getTiempoTranscurridoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Accessor para el ícono según el tipo
     */
    public function getIconoAttribute()
    {
        return match($this->tipo) {
            'pago_exitoso' => '💰',
            'pago_fallido' => '❌',
            'pago_pendiente' => '⏰',
            'contrato_vencido' => '📋',
            'mantenimiento' => '🔧',
            'sistema' => '🔔',
            default => '📢'
        };
    }

    /**
     * Accessor para el color según el tipo
     */
    public function getColorAttribute()
    {
        return match($this->tipo) {
            'pago_exitoso' => 'green',
            'pago_fallido' => 'red',
            'pago_pendiente' => 'yellow',
            'contrato_vencido' => 'orange',
            'mantenimiento' => 'blue',
            'sistema' => 'gray',
            default => 'blue'
        };
    }

    /**
     * Crear notificación de pago
     */
    public static function crearNotificacionPago($pago, $tipo = 'pago_exitoso')
    {
        $usuarios = User::role(['Administrador', 'Empleado Operativo'])->get();

        foreach ($usuarios as $usuario) {
            self::create([
                'user_id' => $usuario->id,
                'fecha' => now()->toDateString(),
                'tipo' => $tipo,
                'mensaje' => self::generarMensajePago($pago, $tipo)
            ]);
        }
    }

    /**
     * Generar mensaje personalizado para pagos
     */
    private static function generarMensajePago($pago, $tipo)
    {
        return match($tipo) {
            'pago_exitoso' => "Pago exitoso de $" . number_format($pago->monto, 2) . " procesado correctamente",
            'pago_fallido' => "Pago de $" . number_format($pago->monto, 2) . " ha fallado",
            'pago_pendiente' => "Pago de $" . number_format($pago->monto, 2) . " está pendiente de confirmación",
            default => "Actualización en pago de $" . number_format($pago->monto, 2)
        };
    }
}