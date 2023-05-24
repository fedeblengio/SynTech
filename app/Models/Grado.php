<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grado extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'grado',
        'carrera_id',
    ];
    use HasFactory;
    public function carrera(){
        return $this->belongsTo(Carrera::class);
    }

    public function materias(){
        return $this->belongsToMany(materia::class, 'carrera_tiene_materias', 'grado_id', 'materia_id')->withTimestamps();
    }


    public function grupos(){
        return $this->hasMany(grupos::class);
    }
}
