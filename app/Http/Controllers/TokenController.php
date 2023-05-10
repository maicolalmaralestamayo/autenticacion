<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\Usuario;
use DateTime;
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

    public static function checkLogin(Request $request, &$message){
        //obtener fecha y hora actual
        $now = now();
        
        //obtener el token de la Base de Datos
        $tokenBD = Token::where('id', $request->header('id'))->first();
        
        //obtener token enviado en la cabecera
        $tokenHeader = $request->header('Authorization');
        $tokenHeader = str_replace('Bearer ', '', $tokenHeader);

        //si no existe el token en la BD
        if (!$tokenBD) {
            $message = 'El token no existe.';
            return false;
        }
        //si el token de la cebecera no coincide con el token en la Base de Datos
        if (!Hash::check($tokenHeader, $tokenBD->token)) {
            $message = 'El token existe pero no coincide.';
            return false;
        }
        //si el tiempo de envío del token es antes del planificado como "comienzo" en la BD
        if ($now < $tokenBD->comienzo) {
            $message = 'El token aún no está en uso.';
            return false;
        }
        //si el tiempo de envío del token es después del planificado como "duracion_larga" en la BD 
        $fechaValida = new DateTime($tokenBD->uso);
        $fechaValida->modify($tokenBD->duracion_larga);
        if ($now > $fechaValida) {
            $message = 'El token expiró por larga duración.';
            return false;
        }
        //si el tiempo de envío del token es después del planificado como "duracion_corta" en la BD
        $fechaValida = new DateTime($tokenBD->uso);
        $fechaValida->modify($tokenBD->duracion_corta);
        if ($now > $fechaValida) {
            $message = 'El token expiró por corta duración.';
            return false;
        }

        //si llegó la ejecución hasta aquí es que todo está OK. Se actualiza la última vez que se utilizó el token
        $tokenBD->uso = $now;
        $tokenBD->save();
        $message = 'Todo correcto.';
        return true;
    }
}