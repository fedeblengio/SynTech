<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class profesores extends Model
{
    use HasFactory;
    use SoftDeletes;

 
    public function materia(){
        return $this->belongsToMany(materia::class, 'profesor_dicta_materia', 'idProfesor', 'idMateria')->withTimestamps();
    }

    public function asignarMaterias($materias,$idProfesor){
        $profesor = profesores::find($idProfesor);
        $profesor->materia()->sync($materias);
    }   

    public function usuario(){
        return $this->belongsTo(usuarios::class,'id', 'id');
    }

    public function grupos(){
        return $this->belongsToMany(grupos::class, 'grupos_tienen_profesor', 'idProfesor', 'idGrupo')->withTimestamps();
    }
}
