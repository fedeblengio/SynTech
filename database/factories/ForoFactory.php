<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ForoFactory extends Factory
{
    public function definition()
    {
        return [
           "informacion" => $this->faker->text(10),
        ];
    }
}
