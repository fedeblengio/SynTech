<?php

namespace Database\Factories;

use App\Models\agendaClaseVirtual;
use App\Models\alumnos;
use App\Models\listaClaseVirtual;
use Illuminate\Database\Eloquent\Factories\Factory;

class listaClaseVirtualFactory extends Factory
{
    protected $model = listaClaseVirtual::class;
    public function definition()
    {
        return [
            'idClase' => agendaClaseVirtual::factory(),
            'idAlumnos' => $this->faker->randomElement(alumnos::pluck('id')),
            'asistencia' => $this->faker->boolean(),
        ];
    }
}
