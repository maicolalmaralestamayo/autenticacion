<?php

use App\Http\Controllers\DatoController;
use App\Http\Controllers\UsuarioController;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(UsuarioController::class)->group(function(){
    Route::post('usuarios/registrar', 'registrar');
    Route::put('usuarios/login', 'login');
    Route::delete('usuarios/logout', 'logout');
});

Route::controller(DatoController::class)->group(function(){
    Route::get('datos', 'index')->middleware('autenticacion');
});