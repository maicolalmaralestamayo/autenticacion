<?php

use App\Http\Controllers\DatoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(UsuarioController::class)->group(function(){
    Route::post('usuarios/registrar', 'registrar');
});

Route::controller(TokenController::class)->group(function(){
    Route::put('usuarios/login', 'login');
    Route::delete('usuarios/logout', 'logout')->middleware('autenticacion');
});

Route::controller(DatoController::class)->group(function(){
    Route::get('datos', 'index')->middleware('autenticacion');
});

Route::controller(RolController::class)->group(function(){
    Route::get('roles', 'index');
    Route::get('roles/{cant}', 'index_pag');
    Route::get('roles/show/{modelo}', 'show');
    Route::post('roles/show_request/{cant}', 'show_request');

    Route::post('roles', 'store');
    Route::put('roles/{modelo}', 'update');
    Route::delete('roles/{modelo}', 'destroy');
});