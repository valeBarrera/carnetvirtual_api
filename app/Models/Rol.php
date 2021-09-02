<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{

    protected $table = 'rol';

    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function usuarios(){
        return $this->hasMany('App\Models\Rol');
    }
}
