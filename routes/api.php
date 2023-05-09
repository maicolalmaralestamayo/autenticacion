<?php

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
    
    // Route::get('usuarios/{cant}', 'index');
    // Route::get('personas/show/{modelo}', 'show');
    // Route::post('personas/show_request/{cant}', 'show_request');
    // Route::put('personas/{modelo}', 'update');
    // Route::delete('personas/{modelo}', 'destroy');
});