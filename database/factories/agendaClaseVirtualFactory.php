<?php

namespace Database\Factories;

use App\Models\grupos;
use App\Models\materia;
use App\Models\profesores;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class agendaClaseVirtualFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'idProfesor' => $this->faker->randomElement(profesores::pluck('id')),
            'idMateria' => materia::factory(),
            'idGrupo' => grupos::factory()->create()->idGrupo,
            'fecha_inicio' => Carbon::now(),
            'fecha_fin' => Carbon::now()->addHour(),
        ];
    }
}
