<?php

namespace Database\Factories;

use App\Models\MaterialPublico;
use App\Models\usuarios;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialPublicoFactory extends Factory
{
    protected $model  = MaterialPublico::class;
    public function definition()
    {
        return [
            'idUsuario' =>  $this->faker->randomElement(usuarios::pluck('id')),
            'titulo' => $this->faker->text(20),
            'mensaje' => $this->faker->text(100),
            'imgEncabezado'=>'encabezadoPredeterminado.jpg'
        ];
    }
}
