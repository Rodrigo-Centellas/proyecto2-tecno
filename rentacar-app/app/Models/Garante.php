<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Garante extends Model
{
    protected $fillable = ['apellido', 'ci', 'domicilio', 'nombre', 'telefono'];

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'garante_id');
    }
}
