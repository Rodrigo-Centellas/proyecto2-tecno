<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clausula extends Model
{
    protected $fillable = ['descripcion', 'activa'];

public function contrato_clausulas()
{
    return $this->belongsToMany(Contrato::class, 'contrato_clausulas', 'clausula_id', 'contrato_id');
}
    protected $casts = [
        'activa' => 'boolean',
    ];

}
