<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Paciente extends Model
{
    use Notifiable;

    const ID_PACIENTE = 1;

    protected $table = 'paciente';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function routeNotificationForFcm()
    {
        return $this->token_telefono;
    }

    public function usuario() {
        return $this->belongsTo('App\Models\Usuario');
    }

    public function recetas()
    {
        return $this->hasMany('App\Models\Receta');
    }

    public function citas()
    {
        return $this->hasMany('App\Models\Cita');
    }


}
