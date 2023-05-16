<?php

namespace Database\Factories;
use App\Models\grupos;
use App\Models\Grado;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class gruposFactory extends Factory
{
    protected $model = grupos::class;
    public function definition()
    {
        return [
            'idGrupo' => Str::random(4),
            'grado_id' => Grado::factory(),
            'nombreCompleto' => $this->faker->word,
            'anioElectivo'=> Carbon::now()->addYear()
        ];
    }
}