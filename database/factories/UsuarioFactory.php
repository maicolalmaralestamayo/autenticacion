<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    public function definition()
    {
        return [
            'nomb1'=>  Str::lower($this->faker->firstName()),
            'nomb2'=> Str::lower($this->faker->firstName()),
            'apell1'=> Str::lower($this->faker->lastName()),
            'apell2'=> Str::lower($this->faker->lastName()),
            'nick'=> $this->faker->uuid(),
            'carne'=> $this->faker->regexify('[0-9]{11}'),
            'email'=> Str::lower($this->faker->email()),
            'passwd'=> Hash::make('user*2023')
        ];
    }
}
