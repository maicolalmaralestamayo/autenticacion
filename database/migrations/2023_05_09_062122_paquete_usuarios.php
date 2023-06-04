<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->datetime('created_at');
            $table->datetime('updated_at')->nullable()->default(null);
            $table->index(['email', 'nick', 'carne']);
        });

        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained()->cascadeOnDelete();
            $table->string('dispositivo', 100);
           
            $table->string('timezone', 100)->default('America/Havana');
            $table->datetime('created_at');
            $table->datetime('updated_at')->nullable()->default(null);
            $table->datetime('used_at')->nullable()->default(null);

            $table->string('validez_ini', 50)->default(env('VALIDEZ_INI', '+0 min'));
            $table->string('validez_inter', 50)->default(env('VALIDEZ_INTER', '+30 min'));
            $table->string('validez_fin', 50)->default(env('VALIDEZ_FIN', '+1 day'));

            $table->text('token')->nullable()->default(null);

            $table->unique(['usuario_id', 'dispositivo']);
            $table->index(['usuario_id', 'dispositivo']);
        });

        Schema::create('rols', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 100)->nullable()->default(null);
            $table->datetime('created_at');
            $table->datetime('updated_at')->nullable()->default(null);
            
            $table->index('nombre');
        });

        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('permiso_id');
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 100)->nullable()->default(null);
            $table->index('nombre');
            $table->datetime('created_at');
            $table->datetime('updated_at')->nullable()->default(null);
        });

        Schema::create('datos', function (Blueprint $table) {
            $table->id();
            $table->string('dato');
            $table->datetime('created_at');
            $table->datetime('updated_at')->nullable()->default(null);
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('tokens');
        Schema::dropIfExists('datos');

        Schema::dropIfExists('rols');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('rol_permiso');
    }
};
