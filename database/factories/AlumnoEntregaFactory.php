<?php

namespace Database\Factories;

use App\Models\alumnos;
use App\Models\Tarea;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlumnoEntregaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'idTareas' => Tarea::factory(),
            'idAlumnos' => $this->faker->randomElement(alumnos::pluck('id')),
            'calificacion' => $this->faker->randomNumber(2,10),
            'mensaje_profesor' => $this->faker->text(10),
            'mensaje'=> $this->faker->text(10),
            're_hacer' => 0
        ];
    }
}
