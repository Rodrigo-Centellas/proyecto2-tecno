<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    protected $table = 'visitas'; // por si tu tabla no sigue la convención

    protected $fillable = [
        'pagina',
        'contador',
    ];
}
