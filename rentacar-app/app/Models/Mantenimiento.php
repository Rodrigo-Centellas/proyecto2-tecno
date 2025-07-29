<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    protected $fillable = ['descripcion', 'nombre'];

    public function vehiculomantenimientos()
    {
        return $this->hasMany(VehiculoMantenimiento::class, 'mantenimiento_id');
    }
}
