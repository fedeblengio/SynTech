<?php

namespace Database\Factories;

use App\Models\Foro;
use App\Models\grupos;
use App\Models\materia;
use App\Models\profesores;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfesorForoGrupoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'idForo' => Foro::factory(),
            'idMateria' => materia::factory(),
            'idGrupo' => grupos::factory(),
            'idProfesor' =>  $this->faker->randomElement(profesores::pluck('id')),
        ];
    }
}
