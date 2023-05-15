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
        $random= $this->faker->unique()->randomNumber($nbDigits = 8);
        return [
            'id' => $random,
            'Cedula_Alumno' => $random,
        ];
    }
}
