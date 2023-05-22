<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\Usuario;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TokenController extends Controller
{
    public static function Token($algoritmo, $tipo, $emisor, $idUsuario, $dispositivo, $fechaCreacion, $fechaValidez,  $fechaExpiracion, $idToken){
        $headerToken = json_encode(['alg' => $algoritmo,
                                    'typ' => $tipo]);

        $payloadToken = json_encode([   'iss' => $emisor,
                                        'sub' => $idUsuario,
                                        'aud' => $dispositivo,
                                        'exp' => $fechaExpiracion,
                                        'nbf' => $fechaValidez,
                                        'iat' => $fechaCreacion,
                                        'jti' => $idToken]);

        $secretKeyApk = env('SECRET_KEY');
        $unsignedToken = base64_encode($headerToken).'.'.base64_encode($payloadToken);
        $signatureToken = hash_hmac('sha256', $unsignedToken, $secretKeyApk);
        
        $token = $unsignedToken.'.'.$signatureToken;
        
        return $token;
    }

    public static function formatearToken($tokenRequest, &$tokenFormateado, &$message){
        $tokenFormateado = str_replace('Bearer ', '',$tokenRequest);

        if (!$tokenFormateado) {
            $message = 'Formato de token inválido.';
            return false;
        }else {
            $message = 'Formato de token válido.';
            return true;
        }
    }

    public static function fragmentarToken($tokenFormateado, &$headerToken, &$payloadToken, &$signatureToken, &$message){
        $tokenFragment = explode('.',$tokenFormateado, 3);
        try {
            $headerToken = $tokenFragment[0];
            $payloadToken = $tokenFragment[1];
            $signatureToken = $tokenFragment[2];

            $message = 'Token fragmentado correctamente.';
            return true;
        } catch (\Throwable $th) {
            $message = 'Imposible fragmentar el token.';
            return true;
        }
    }

    public static function decodif64ToJsonToken($fragmento, &$fragmentoDecodif, &$message){
        try {
            $fragmentoDecodif = json_decode(base64_decode($fragmento));
            $message = 'Fragmento decodificado correctamente.';
            return true;
        } catch (\Throwable $th) {
            $message = 'Imposible decodificar frangmento de token.';
            return true;
        }
        
    }

    public static function ProcesarToken(&$tokenRequest, &$signatureToken, &$unsignedToken, &$headerTokenDecod, &$payloadTokenDecod, &$message){
        $tokenRequest = str_replace('Bearer ', '',$tokenRequest);
        if (!$tokenRequest) {
            $message = 'Formato de token inválido.';
            return false;
        } else {
            $tokenFragment = explode('.',$tokenRequest, 3);
            $headerToken = $tokenFragment[0];
            $payloadToken = $tokenFragment[1];
            $signatureToken = $tokenFragment[2];

            $unsignedToken = $headerToken.'.'.$payloadToken;
            $headerTokenDecod = json_decode(base64_decode($headerToken));
            $payloadTokenDecod = json_decode(base64_decode($payloadToken));

            $message = 'Formato de token válido.';
            return true;
        }
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
        $token->comienzo = $request->comienzo? : now();//opcional pero llenado obligatorio por defecto
        $token->validez_larga = $request->validez_larga? : env('VALIDEZ_LARGA', '+1 day');//idem
        $token->validez_corta = $request->validez_corta? : env('VALIDEZ_CORTA', '+30 min');//idem
        $token->save();

        //se conforma el campo TOKEN con la información fija (expiración larga, etc.)
        $validezLarga = new DateTime($token->comienzo);
        $validezLarga->modify($token->validez_larga);
        $token->token = static::Token(env('ALGORITMO', 'sha256'), env('TIPO', 'JWT'), env('EMISOR', 'cdasi'), $token->usuario_id, $token->dispositivo, $token->created_at->format('U'), $token->comienzo->format('U'), $validezLarga->format('U'), $token->id);
        $token->save();
        
        return $token->token;
    }

    public static function logout(Request $request, &$message){
        
        
        
        $token = Token::where('id', $request->id)->
                        where('dispositivo', Str::lower($request->dispositivo))->
                        where('usuario_id', $request->usuario_id)->
                        first();

        if ($token) {
            $message = 'Sesión cerrada correctamente.';
            $token->delete();
            return true;
        }else {
            $message = 'Datos de cierre de sesión incorrectos.';
            return false;
        }
    }

    public static function checkLogin(Request $request, &$message){
        //procesar token
        static::formatearToken($request->header('Authorization'), $tokenFormateado, $message);
        static::fragmentarToken($tokenFormateado, $headerToken, $payloadToken, $signatureToken, $message);
        $unsignedToken = $headerToken.'.'.$payloadToken;
        static::decodif64ToJsonToken($headerToken, $headerTokenDecod, $message);
        static::decodif64ToJsonToken($payloadToken, $payloadTokenDecod, $message);
        
        $secretKeyApk = env('SECRET_KEY');
        $alg = $headerTokenDecod->alg;
        
        $signatureToken2 = hash_hmac($alg, $unsignedToken, $secretKeyApk);
        if ($signatureToken != $signatureToken2) {
            $message = 'Petición inválida.';
            return false;
        }

        // verificar la existencia del token en la BD y la correspondencia entra la información
        //que porta y la información almacenada en la BD
        $tokenBD = Token::  where('token', $tokenFormateado)->
                            where('usuario_id', $payloadTokenDecod->sub)->
                            where('dispositivo', $payloadTokenDecod->aud)->
                            where('comienzo', date('c', $payloadTokenDecod->nbf))->
                            where('created_at', date('c', $payloadTokenDecod->iat))->
                            where('id', $payloadTokenDecod->jti)->
                            first();
        
        //si no existe el token en la BD (o no hay correspondencia entre los datos de la petición y la BD)
        if (!$tokenBD) {
            $message = 'Respuesta inválida.';
            return false;
        }

        //realizar verificaciones de tiempo
        $now = now();

        //si el tiempo de envío del token es antes del planificado
        if ($now < $tokenBD->comienzo) {
            $message = 'Todavía es imposible inciar su sesión.';
            return false;
        }

        //si el token perdió la validez larga
        $validezLarga = new DateTime($tokenBD->comienzo);
        $validezLarga->modify($tokenBD->validez_larga);
        if ($now > $validezLarga) {
            $tokenBD->delete();
            $message = 'Por seguridad cerramos su sesión después de varias horas.';
            return false;
        }

        //si el token perdió la validez corta
        $validezCorta = $tokenBD->used_at? new DateTime($tokenBD->used_at) : new DateTime($now);      
        $validezCorta->modify($tokenBD->validez_corta);
        if ($now > $validezCorta) {
            $tokenBD->delete();
            $message = 'Por seguridad cerramos su sesión después de varios minutos sin utilizar la aplicación.';
            return false;
        }

        //si llegó la ejecución hasta aquí es que todo está OK. 
        $tokenBD->used_at = $now;//Se actualiza la última vez que se utilizó el token
        $tokenBD->save();

        return true;
    }
}