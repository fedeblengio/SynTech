<?php

namespace Database\Factories;

use App\Models\alumnos;
use Illuminate\Database\Eloquent\Factories\Factory;

class alumnosFactory extends Factory
{
    protected $model = alumnos::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $padded_number = str_pad(mt_rand(1, 9999999), 1 - strlen('1'), '0', STR_PAD_LEFT);
        $randomID = "1". $padded_number;
   
        return [
            'id' => $randomID,
            'Cedula_Alumno' => $randomID,
        ];
    }
}
