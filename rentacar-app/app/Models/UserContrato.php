<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserContrato extends Model
{
    protected $fillable = ['user_id', 'contrato_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }
}
