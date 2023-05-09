<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TokenController extends Controller
{
    public static function Token(&$valorToken){
        $textoPlano = Str::random(64);
        $valorToken = [ 'textoPlano' => $textoPlano,
                        'textoCifrado' => Hash::make($textoPlano)];
    }

    public static function login(Request $request, Usuario $usuario){
        $token = Token::where('usuario_id', $usuario->id)->
                        where('dispositivo', $request->dispositivo)->first();

        if (!$token) {
            $token = new Token;
            $token->usuario_id = $usuario->id;
            $token->dispositivo = Str::lower($request->dispositivo);
        }
        
        static::token($valorToken);
        $token->token = $valorToken['textoCifrado'];
        $token->save();
        
        $valorToken['id'] = $token->id;
        return $valorToken['id'].'|'.$valorToken['textoPlano'];
    }

    public static function logout(Request $request){
        $token = Token::where('id', $request->id)->
                        where('dispositivo', Str::lower($request->dispositivo))->
                        where('usuario_id', $request->usuario_id)->first();

        if ($token && Hash::check($request->token, $token->token)) {
            $token->delete();
            return $token;
        }else {
            return 'Datos de sesión incorrectos.';
        }
    }

    public static function checkLogin(Request $request){
        $token = Token::where('id', $request->id)->first();

        if ($token) {
            # code...
        }
    }
}