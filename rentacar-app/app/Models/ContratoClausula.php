<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoClausula extends Model
{
    protected $fillable = ['contrato_id', 'clausula_id'];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function clausula()
    {
        return $this->belongsTo(Clausula::class, 'clausula_id');
    }
}
