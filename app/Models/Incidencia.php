<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{

    protected $table = 'incidencia';

    protected $hidden = [
        'updated_at',
    ];

    public function citas()
    {
        return $this->belongsToMany(Cita::class, 'cita_has_incidencia', 'incidencia_id', 'cita_id');
    }

}
