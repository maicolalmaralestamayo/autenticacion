<?php

namespace App\Http\Middleware;

use App\Http\Controllers\TokenController;
use Closure;
use Illuminate\Http\Request;

class AutenticacionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        return response(['data' => $request->body]);
        
        if (TokenController::checkLogin($request)) {
            return $next($request);
        } else {
            return Response('Debe autenticarse primero.');
        } 
    }
}