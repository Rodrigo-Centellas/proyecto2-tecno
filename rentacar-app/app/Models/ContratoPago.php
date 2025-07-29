<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoPago extends Model
{
    protected $fillable = ['contrato_id', 'pago_id'];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }
}
