<?php

namespace Database\Factories;

use App\Models\grupos;
use App\Models\materia;
use App\Models\profesores;
use App\Models\Tarea;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfesorTareaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'idMateria' => materia::factory(),
            'idGrupo' => grupos::factory(),
            'idTareas' => Tarea::factory(),
            'idProfesor' =>  $this->faker->randomElement(profesores::pluck('id'))
        ];
    }
}
