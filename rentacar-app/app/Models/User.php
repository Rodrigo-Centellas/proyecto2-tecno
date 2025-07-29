<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'apellido',
        'ci',
        'domicilio',
        'telefono',
        'documento_frontal_path',
        'documento_trasero_path',
    
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'user_id');
    }

    public function contratos()
    {
        return $this->belongsToMany(Contrato::class, 'user_contratos', 'user_id', 'contrato_id');
    }

    public function usercontratos()
    {
        return $this->hasMany(UserContrato::class, 'user_id');
    }

    public function notificacions()
    {
        return $this->hasMany(Notificacion::class, 'user_id');
    }

    /**
     * Accessor para obtener la URL completa del documento frontal
     */
    public function getDocumentoFrontalUrlAttribute()
    {
        return $this->documento_frontal_path 
            ? asset('storage/' . $this->documento_frontal_path) 
            : null;
    }

    /**
     * Accessor para obtener la URL completa del documento trasero
     */
    public function getDocumentoTraseroUrlAttribute()
    {
        return $this->documento_trasero_path 
            ? asset('storage/' . $this->documento_trasero_path) 
            : null;
    }

    /**
     * Accessor para obtener la URL completa del certificado de antecedentes
     */

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}