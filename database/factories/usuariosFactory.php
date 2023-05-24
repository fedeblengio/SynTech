<?php

namespace Database\Factories;

use App\Models\usuarios;
use Illuminate\Database\Eloquent\Factories\Factory;

class usuariosFactory extends Factory
{   
    protected $model = usuarios::class;
   
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->randomNumber($nbDigits = 8),
            'nombre' => $this->faker->name,
            'email' =>$this->faker->unique()->safeEmail,
            'ou' => 'Alumno',
            'imagen_perfil' => "default_picture.png",
            'genero' => "",
        ];
    }
}
