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
    public static function Token($algoritmo, $tipo, $emisor, $idUsuario, $dispositivo, $fechaCreacion, $fechaValidez,  $fechaExpiracion, $idToken){
        $headerToken = json_encode([ 'alg' => $algoritmo,
                                'typ' => $tipo]);

        $payloadToken = json_encode(['iss' => $emisor,
                                'sub' => $idUsuario,
                                'aud' => $dispositivo,
                                'exp' => $fechaExpiracion,
                                'nbf' => $fechaValidez,
                                'iat' => $fechaCreacion,
                                'jti' => $idToken
                                ]);

        $secretKeyApk = env('SECRET_KEY');
        $unsignedToken = base64_encode($headerToken).'.'.base64_encode($payloadToken);
        $signatureToken = hash_hmac('sha256', $unsignedToken, $secretKeyApk);
        
        $token = $unsignedToken.'.'.$signatureToken;
        
        return $token;
    }

    public static function login(Request $request, Usuario $usuario){
        //si previamente existe un token para un usuario con un dispositivo, SE ELIMINA
        $token = Token::where('usuario_id', $usuario->id)->
                        where('dispositivo', $request->dispositivo)->first();
        if ($token) {$token->delete();}
        
        //si no existe el token (o se eliminó), SE CREA UNO NUEVO, con el campo TOKEN null por defecto
        $token = new Token;
        $token->usuario_id = $usuario->id;//OBLIGATORIO
        $token->dispositivo = Str::lower($request->dispositivo);//OBLIGATORIO
        $token->comienzo = $request->comienzo? : now();//llenado por defecto
        $token->validez_larga = $request->validez_larga? : env('VALIDEZ_LARGA', '+1 day');//llenado por defecto
        $token->validez_corta = $request->validez_corta? : env('VALIDEZ_CORTA', '+30 min');//llenado por defecto
        $token->save();

        //se conforma el campo TOKEN con la información fija (expiración larga, etc.)
        $validezLarga = new DateTime($token->comienzo);
        $validezLarga->modify($token->validez_larga);
        $token->token = static::Token(env('ALGORITMO', 'sha256'), env('TIPO', 'JWT'), env('EMISOR', 'cdasi'), $token->usuario_id, $token->dispositivo, strtotime($token->created_at), strtotime($token->comienzo), strtotime($validezLarga->format('Y-m-d H:i:s')), $token->id);
        $token->save();
        
        //se devuelve el token
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
        //obtener token enviado en la cabecera
        $tokenRequest = str_replace('Bearer ', '',$request->header('Authorization'));//por norma, los JWT se envían con la palabra Bearer delante, y hay que eliminarla
        if (!$tokenRequest) {
            $message = 'Petición sin token.';
            return false;
        }

        //fragmentación en partes del token que viajó en la petición
        $tokenRequest = explode('.',$tokenRequest, 3);
        $headerToken = $tokenRequest[0];
        $payloadToken = $tokenRequest[1];
        $signatureToken = $tokenRequest[2];
        $unsignedToken = $headerToken.'.'.$payloadToken;
        
        //decodificación del token
        $headerTokenDecod = json_decode(base64_decode($headerToken));
        $payloadTokenDecod = json_decode(base64_decode($payloadToken));

        $message = base64_decode($headerToken);
        return false;

        //verificación de la integridad del token en la petición
        $secretKeyApk = env('SECRET_KEY');
        $alg = $headerTokenDecod->alg;
        $signatureToken2 = hash_hmac($alg, $unsignedToken, $secretKeyApk);
        if ($signatureToken != $signatureToken2) {
            $message = 'Token corrupto en la petición.';
            return false;
        }

        // obtener el token de la BD
        $tokenBD = Token::  where('usuario_id', $payloadTokenDecod->sub)->
                            where('dispositivo', $payloadTokenDecod->aud)->
                            where('comienzo', date('Y-m-d H:i:s', $payloadTokenDecod->nbf))->
                            where('created_at', date('Y-m-d H:i:s', $payloadTokenDecod->iat))->
                            where('id', $payloadTokenDecod->jti)->first();

        // $message = base64_decode($payloadToken);
        // $message = now()->toDateTimeString();
        // return false;
        
        //si no existe el token en la BD
        if (!$tokenBD) {
            $message = 'Token corrupto en la BD.';
            // $message = base64_decode($payloadToken);
            return false;
        }

        //realizar otras verificaciones de tiempo
        $now = now()->toDateTimeString();

        //si el tiempo de envío del token es antes del planificado
        if ($now < $tokenBD->comienzo) {
            $message = 'El token aún no está activado.';
            return false;
        }

        //si el token perdió la validez larga
        $validezLarga = new DateTime($tokenBD->comienzo);
        $validezLarga->modify($tokenBD->validez_larga);
        if ($now > $validezLarga) {
            $tokenBD->delete();
            $message = 'El token expiró por larga duración y se eliminó.';
            return false;
        }

        //si el token perdió la validez corta
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