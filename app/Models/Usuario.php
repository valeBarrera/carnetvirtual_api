<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;

    protected $table = 'usuario';

    protected $hidden = [
        'password', 'token', 'created_at', 'updated_at',
    ];

    public function rol() {
        return $this->belongsTo('App\Models\Rol');
    }

    public function meson()
    {
        return $this->hasOne('App\Models\Meson');
    }

    public function paciente() {
        return $this->hasOne('App\Models\Paciente');
    }

    public function farmacia()
    {
        return $this->hasOne('App\Models\Farmacia');
    }

    public function medico()
    {
        return $this->hasOne('App\Models\Medico');
    }

    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }

}
