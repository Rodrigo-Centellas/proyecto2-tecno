<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $fillable = [
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'frecuencia_pago_id',
        'nro_cuenta_id',
        'vehiculo_id'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_contratos', 'contrato_id', 'user_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function frecuenciapago()
    {
        return $this->belongsTo(FrecuenciaPago::class, 'frecuencia_pago_id');
    }

    public function nrocuenta()
    {
        return $this->belongsTo(NroCuenta::class, 'nro_cuenta_id');
    }



    public function usercontratos()
    {
        return $this->hasMany(UserContrato::class, 'contrato_id');
    }

public function contrato_clausulas()
{
    return $this->belongsToMany(Clausula::class, 'contrato_clausulas', 'contrato_id', 'clausula_id');
}


    public function contratopagos()
    {
        return $this->hasMany(ContratoPago::class, 'contrato_id');
    }
}
