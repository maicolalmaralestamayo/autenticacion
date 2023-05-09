<?php

namespace App\Http\Middleware;

use App\Http\Controllers\TokenController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AutenticacionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (TokenController::checkLogin($request)) {
            return $next($request);
        } else {
            return Response('Debe autenticarse primero.');
        } 
    }
}