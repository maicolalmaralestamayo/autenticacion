<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nomb1', 50);
            $table->string('nomb2', 50)->nullable();
            $table->string('apell1', 50);
            $table->string('apell2', 50);
            $table->string('nick', 50)->unique();
            $table->string('email', 50)->unique();
            $table->string('carne', 11)->unique();
            $table->string('passwd');
            $table->timestamps();
            $table->index(['email', 'nick', 'carne']);
        });

        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained()->cascadeOnDelete();
            $table->string('dispositivo', 100);
            $table->text('token')->nullable()->default(null);
            $table->datetime('comienzo');
            $table->string('validez_larga', 50)->default(env('VALIDEZ_LARGA', '+1 day'));
            $table->string('validez_corta', 50)->default(env('VALIDEZ_CORTA', '+30 min'));
            $table->timestamp('uso')->nullable()->default(null);
            $table->timestamps();
            $table->unique(['usuario_id', 'dispositivo']);
            $table->index(['usuario_id', 'dispositivo']);
        });

        Schema::create('datos', function (Blueprint $table) {
            $table->id();
            $table->string('dato');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('tokens');
        Schema::dropIfExists('datos');
    }
};
