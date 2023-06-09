<?php

namespace Database\Seeders;

use App\Models\Dato;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Usuario::factory()->create();
        Dato::factory(3)->create();
        Rol::factory(100)->create();
    }
}
