<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AutenticacionProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        //
    }

    private function cargarFicheros(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/something.php' => config_path('something.php'),
            ],
            'package-name-config'
        );
    }
}
