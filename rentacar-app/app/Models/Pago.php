<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
        const METODO_QR = 'qr';
    const METODO_EFECTIVO = 'efectivo';
    protected $fillable = [
        'desde',
        'fecha',
        'hasta',
        'estado',
        'monto',
        'tipo_pago',
        'metodo_pago',
        'reserva_id',
        'contrato_id',
        'pagofacil_transaction_id',// ✅ Asegúrate de incluirlo aquí
    ];

    // Pago pertenece a una reserva (si aplica)
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

     public function pagos()
{
    return $this->hasMany(Pago::class, 'reserva_id');
}
    // Pago pertenece a un contrato (si aplica)
    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    // Relación adicional si estás usando una tabla intermedia contratopagos (opcional)
    public function contratopagos()
    {
        return $this->hasMany(ContratoPago::class, 'pago_id');
    }

        public function isEfectivo(): bool {
        return $this->metodo_pago === self::METODO_EFECTIVO;
    }

    public function isQr(): bool {
        return $this->metodo_pago === self::METODO_QR;
    }
}
