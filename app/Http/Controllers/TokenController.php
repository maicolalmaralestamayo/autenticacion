<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\Usuario;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TokenController extends Controller
{
    public static function Token($algoritmo, $tipo='JWT', $emisor='cdasi', $idUsuario, $dispositivo, $fechaExpiracion, $fechaValidez, $fechaCreacion, $idToken){
        $header = json_encode([ 'alg' => $algoritmo,
                                'typ' => $tipo]);

        $payload = json_encode(['iss' => $emisor,
                                'sub' => $idUsuario,
                                'aud' => $dispositivo,
                                'exp' => $fechaExpiracion,//numeric date
                                'bnf' => $fechaValidez,//numeric date
                                'iat' => $fechaCreacion,
                                'jti' => $idToken
                                ]);

        $secretKey = env('SECRET_KEY');
        $unsignedToken = base64_encode($header).'.'.base64_encode($payload);
        $signature = hash_hmac('sha256', $unsignedToken, $secretKey);
        $token = $unsignedToken.'.'.$signature;
        
        return $token;
    }

    public static function login(Request $request, Usuario $usuario){
        //si previamente existe un token para un usuario con un dispositivo, SE ELIMINA
        $token = Token::where('usuario_id', $usuario->id)->
                        where('dispositivo', $request->dispositivo)->first();
        if ($token) {$token->delete();}
        
        //si no existe el token, SE CREA, con el campo TOKEN NULL POR DEFECTO
        $token = new Token;
        $token->usuario_id = $usuario->id;//OBLIGATORIO
        $token->dispositivo = Str::lower($request->dispositivo);//OBLIGATORIO
        $token->comienzo = $request->comienzo;//OBLIGATORIO
        if ($request->validez_larga) {$token->validez_larga;}//NULLABLE PERO LLENO POR DEFECTO (.env)
        if ($request->validez_corta) {$token->validez_corta;}//NULLABLE PERO LLENO POR DEFECTO (.env)
        $token->save();
        
        //después de creado el token, SE ACTUALIZA EL CAMPO TOKEN
        $token->token = static::Token(env('ALGORTIMO', 'HS256'), env('TIPO', 'JWT'), env('EMISOR', 'cdasi'), $token->usuario_id, $token->dispositivo, strtotime($token->comienzo), strtotime($token->validez_larga)+10, now(), $token->id);
        $token->save();
        
        return $token->token;
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
        $now = now('+0400');
        
        //obtener el token de la BD
        $tokenBD = Token::where('id', $request->header('id'))->first();
        
        //obtener token enviado en la cabecera
        $tokenHeader = $request->header('Authorization');
        $tokenHeader = str_replace('Bearer ', '', $tokenHeader);

        //si no existe el token en la BD
        if (!$tokenBD) {
            $message = 'El token no existe.';
            return false;
        }
        //si el token de la cebecera no coincide con el token en la BD
        if (!Hash::check($tokenHeader, $tokenBD->token)) {
            $message = 'El token existe pero no coincide.';
            return false;
        }
        //si el tiempo de envío del token es antes del planificado como "comienzo" en la BD
        if ($now < $tokenBD->comienzo) {
            $message = 'El token aún no está activado.'.'||ahora: '.$now->format('Y-m-d H:i:s').'||token: '.$tokenBD->comienzo;
            return false;
        }

        //si el token es válido por un período largo de tiempo (ejemplo: 1 día [24 horas, 2 días])
        $validezLarga = new DateTime($tokenBD->comienzo);
        $validezLarga->modify($tokenBD->validez_larga);
        if ($now > $validezLarga) {
            $tokenBD->delete();
            $message = 'El token expiró por larga duración y se eliminó.';
            return false;
        }

        //si el tiempo es válido por un período corto de tiempo (ejemplo: 30 minutos [1 hora, 3 horas])
        if ($tokenBD->uso) {
            $validezCorta = new DateTime($tokenBD->uso);    
        }else {
            $validezCorta = new DateTime($now);
        }
        $validezCorta->modify($tokenBD->validez_corta);
        if ($now > $validezCorta) {
            $tokenBD->delete();
            $message = 'El token expiró por corta duración y se eliminó.';
            return false;
        }

        //si llegó la ejecución hasta aquí es que todo está OK. Se actualiza la última vez que se utilizó el token
        $tokenBD->uso = $now;
        $tokenBD->save();
        $message = 'Todo correcto.';
        return true;
    }
}