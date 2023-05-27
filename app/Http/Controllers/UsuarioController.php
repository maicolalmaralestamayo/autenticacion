<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public TokenController $token;
    
    public function registrar(Request $request){
        $usuario = new Usuario;
        $usuario->nomb1 = $request->nomb1;
        $usuario->nomb2 = $request->nomb2;
        $usuario->apell1 = $request->apell1;
        $usuario->apell2 = $request->apell2;
        $usuario->carne = $request->carne;
        $usuario->email = $request->email;
        $usuario->nick = $request->nick;
        $usuario->passwd = Hash::make($request->passwd);
        $usuario->save();
        return $usuario;
    }
}
