<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolFactory extends Factory
{
    public function definition()
    {
        return [
            'nombre'=>  Str::lower($this->faker->word()),
            'descripcion'=> Str::lower($this->faker->word())
        ];
    }
}
