<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    protected $table = 'receta';

    protected $hidden = [
        'medico_id', 'farmacia_id', 'paciente_id'
    ];

    public function paciente()
    {
        return $this->belongsTo('App\Models\Paciente');
    }

    public function medico()
    {
        return $this->belongsTo('App\Models\Medico');
    }

    public function farmacia()
    {
        return $this->belongsTo('App\Models\Farmacia');
    }
}
