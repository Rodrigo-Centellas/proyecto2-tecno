<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $fillable = ['placa', 'marca', 'modelo', 'url_imagen', 'estado', 'monto_garantia', 'precio_dia', 'tipo',];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'vehiculo_id');
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'vehiculo_id');
    }

    public function vehiculomantenimientos()
    {
        return $this->hasMany(VehiculoMantenimiento::class, 'vehiculo_id');
    }
}
