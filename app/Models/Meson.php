<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meson extends Model
{
    const ID_MESON= 2;

    protected $table = 'meson';

    protected $hidden = [
        'usuario_id', 'created_at', 'updated_at',
    ];
}
