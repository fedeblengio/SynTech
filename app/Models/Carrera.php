<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrera extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'nombre',
        'plan',
        'categoria',
    ];

    use HasFactory;
    public function grado(){
        return $this->hasMany(Grado::class);
    }
    
}
