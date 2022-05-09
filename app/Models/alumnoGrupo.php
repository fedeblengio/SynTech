<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class alumnoGrupo extends Model
{
    use HasFactory;
    protected $table = 'alumnos_pertenecen_grupos';
}
