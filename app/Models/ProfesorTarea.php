<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfesorTarea extends Model
{
    use HasFactory;
    protected $table = 'profesor_crea_tareas';

    protected $fillable = [
        'idProfesor',
        'idGrupo',
        'idMateria',
        'idTareas',
    ];
}
