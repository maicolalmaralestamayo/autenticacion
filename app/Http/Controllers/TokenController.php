<?php

namespace App\Http\Controllers;

use App\Helpers\MaicolHelper;
use App\Http\Resources\TokenResource;
use App\Models\Token;
use App\Models\Usuario;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TokenController extends Controller
{
    //conformar el token
    public function conformarToken($algoritmo, $tipo, $emisor, $idUsuario, $dispositivo, $fechaCreacion, $fechaValidez,  $fechaExpiracion, $idToken){
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

    public function login(Request $request){
        //validar usuario y contraseña
        $usuario = Usuario::where('email', $request->correo)->first();
        $passwd = Hash::check($request->passwd, $usuario->passwd);
        if (!$usuario || !$passwd) {
            return MaicolHelper::Data(null, 401, false, 'Usuario y/o contraseña inválidos.');
        }
        
        //cerrar sesión previamente abierta (si existe)
        Token:: where('usuario_id', $usuario->id)->
                where('dispositivo', $request->terminal)->delete();
        
        //abrir una nueva sesión
        $token = new Token;
        $token->usuario_id = $usuario->id;
        $token->dispositivo = Str::lower($request->terminal);
        $token->timezone = $request->zona_horaria? : 'America/Havana';
        $token->validez_ini = $request->inicio? : env('VALIDEZ_INI', '+1 min');
        $token->validez_inter = $request->intermedio? : env('VALIDEZ_INTER', '+30 min');
        $token->validez_fin = $request->fin? : env('VALIDEZ_FIN', '+1 day');
        $token->save();

        //conformar el token
        $validezIni = new DateTime($token->created_at);
        $validezIni->modify($token->validez_ini);

        $validezFin = new DateTime($token->created_at);
        $validezFin->modify($token->validez_fin);

        $token->token = $this->conformarToken(env('ALGORITMO', 'sha256'), env('TIPO', 'JWT'), env('EMISOR', 'cdasi'), $token->usuario_id, $token->dispositivo, $token->created_at->format('U'), $validezIni->format('U'), $validezFin->format('U'), $token->id);
        $token->save();
        
        //devolver el token ya conformado
        return MaicolHelper::Data(new TokenResource($token), 201, true, 'Inicio de sesión satisfactorio.');
    }

    public function checkLogin(Request $request,&$code, &$message){
        //procesar token
        $tokenFormateado = str_replace('Bearer ', '',$request->header('Authorization'));

        $tokenFragment = explode('.',$tokenFormateado, 3);
        $headerToken = $tokenFragment[0];
        $payloadToken = $tokenFragment[1];
        $signatureTokenRequest = $tokenFragment[2];

        //verificar que el token no esté corrupto
        $secretKeyApk = env('SECRET_KEY');
        
        $headerTokenDecod = json_decode(base64_decode($headerToken));
        $alg = $headerTokenDecod->alg;
        
        $unsignedToken = $headerToken.'.'.$payloadToken;
        $signatureToken = hash_hmac($alg, $unsignedToken, $secretKeyApk);
        
        if ($signatureTokenRequest != $signatureToken) {
            $code = 400;
            $message = 'Petición inválida.';
            return false;
        }

        // verificar la existencia del token en la BD
        $tokenBD = Token::where('token', $tokenFormateado)->first();
        if (!$tokenBD) {
            $code = 409;
            $message = 'Sesión inválida.';
            return false;
        }

        //realizar verificaciones de tiempo
        $now = now();

        //si el token tiene validez inicial (con respecto a la fecha de creación)
        $validezIni = new DateTime($tokenBD->created_at);
        $validezIni->modify($tokenBD->validez_ini);
        if ($now < $validezIni) {
            $code = 425;
            $message = 'Inicie sesión en otro momento.';
            return false;
        }

        //si el token tiene validez intermedia (con respecto a la fecha de uso)
        $validezInter = $tokenBD->used_at? new DateTime($tokenBD->used_at) : new DateTime($now);      
        $validezInter->modify($tokenBD->validez_inter);
        if ($now > $validezInter) {
            $code = 410;
            $tokenBD->delete();
            $message = 'Por seguridad cerramos su sesión después de varios minutos sin utilizar la aplicación.';
            return false;
        }

        //si el token tiene validez final (con respecto a la fecha de creación)
        $validezFin = new DateTime($tokenBD->created_at);
        $validezFin->modify($tokenBD->validez_fin);
        if ($now > $validezFin) {
            $tokenBD->delete();
            $code = 410;
            $message = 'Por seguridad cerramos su sesión despúes de varias horas.';
            return false;
        }

        //si llegó la ejecución hasta aquí es que todo está OK. 
        $tokenBD->used_at = $now;//Se actualiza la última vez que se utilizó el token
        $tokenBD->save();
        return true;
    }

    public function logout(Request $request){
        $tokenFormateado = str_replace('Bearer ', '',$request->header('Authorization'));
        $token = Token::where('token', $tokenFormateado)->first();
        $token->delete();
        return MaicolHelper::Data(null, 200, true, 'Sesión cerrada satisfactoriamente.');
    }
}