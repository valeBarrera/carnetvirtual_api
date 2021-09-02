<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    //
    protected $table = 'cita';

    protected $hidden = [
        'medico_id', 'cita_id', 'paciente_id'
    ];

    public function paciente()
    {
        return $this->belongsTo('App\Models\Paciente');
    }

    public function medico()
    {
        return $this->belongsTo('App\Models\Medico');
    }

    public function meson()
    {
        return $this->belongsTo('App\Models\Meson');
    }

    public function incidencias()
    {
        return $this->belongsToMany(Incidencia::class, 'cita_has_incidencia', 'cita_id', 'incidencia_id');
    }
}
