<?php

namespace Database\Factories;

use App\Models\materia;
use Illuminate\Database\Eloquent\Factories\Factory;

class materiaFactory extends Factory
{
    protected $model = materia::class;
    public function definition()
    {
        return [
            'nombre' => $this->faker->colorName." ".$this->faker->word,
        ];
    }
}
