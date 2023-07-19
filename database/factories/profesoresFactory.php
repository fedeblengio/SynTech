<?php

namespace Database\Factories;

use App\Models\profesores;
use App\Models\usuarios;
use Illuminate\Database\Eloquent\Factories\Factory;

class profesoresFactory extends Factory
{
    protected $model = profesores::class;
    public function definition()
    {
        $padded_number = str_pad(mt_rand(1, 9999999), 1 - strlen('1'), '0', STR_PAD_LEFT);
        $randomID = "1". $padded_number;
     
        return [
            'id' =>  $randomID ,
            'Cedula_Profesor' =>  $randomID ,
        ];
    }
}
