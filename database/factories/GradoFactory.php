<?php

namespace Database\Factories;
use App\Models\Carrera;
use App\Models\Grado;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradoFactory extends Factory
{
    protected $model = Grado::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'grado'=>$this->faker->randomElement(['1er Semestre','2do Semestre','3er Semestre']),
            'carrera_id' => Carrera::factory(),
        ];
    }
}
