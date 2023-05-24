<?php

namespace Database\Factories;


use App\Models\token;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;


class tokenFactory extends Factory
{
    protected $model = token::class;
    public function definition()
    {
        return [
            'token' => Str::random(64),
            'fecha_vencimiento'=> Carbon::now()->addYear()
        ];
    }
}
