<?php

namespace Database\Factories;

use App\Models\Foro;
use App\Models\usuarios;
use Illuminate\Database\Eloquent\Factories\Factory;

class datosForoFactory extends Factory
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
            'idUsuario' => $this->faker->randomElement(usuarios::pluck('id')),
            'mensaje' => $this->faker->text(),
        ];
    }
}
