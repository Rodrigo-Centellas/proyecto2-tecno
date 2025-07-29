<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrecuenciaPago extends Model
{
    protected $fillable = ['frecuencia_dias', 'nombre'];

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'frecuencia_pago_id');
    }
}
