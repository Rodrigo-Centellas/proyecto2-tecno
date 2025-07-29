<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NroCuenta extends Model
{
    protected $fillable = ['banco', 'nro_cuenta','es_activa'];
    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'nro_cuenta_id');
    }
}
