<?php

namespace Database\Factories;

use App\Models\alumnos;
use App\Models\Tarea;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlumnoReHacerTareaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $tarea = Tarea::factory()->create();
        return [
            'idTareas' => $tarea->id,
            'idTareasNueva' =>$tarea->id,
            'idAlumnos' => $this->faker->randomElement(alumnos::pluck('id')),
            'calificacion' => $this->faker->randomNumber(2,10),
            'mensaje'=> $this->faker->text(10),
        ];
    }
}
