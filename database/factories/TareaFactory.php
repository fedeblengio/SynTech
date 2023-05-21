<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TareaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'titulo' => $this->faker->text(5),
            'descripcion' => $this->faker->text(10),
            'fecha_vencimiento' => Carbon::now()->addDays(2),
        ];
    }
}
