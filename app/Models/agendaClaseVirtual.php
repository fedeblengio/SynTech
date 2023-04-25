<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class agendaClaseVirtual extends Model
{
    use HasFactory;
    protected $table = 'agenda_clase_virtual';

    protected $fillable = [
        'idProfesor',
        'idMateria',
        'idGrupo',
        'fecha_inicio',
        'fecha_fin',
    ];


}
