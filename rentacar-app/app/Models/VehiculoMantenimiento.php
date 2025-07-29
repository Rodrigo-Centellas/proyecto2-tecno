<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculoMantenimiento extends Model
{
    protected $fillable = ['fecha', 'monto', 'vehiculo_id', 'mantenimiento_id'];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }
}
