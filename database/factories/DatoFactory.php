<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DatoFactory extends Factory
{
    public function definition()
    {
        return [
            'dato'=> $this->faker->text()
        ];
    }
}
