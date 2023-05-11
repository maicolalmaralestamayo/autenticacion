<?php

namespace App\Http\Middleware;

use App\Http\Controllers\TokenController;
use Closure;
use Illuminate\Http\Request;

class AutenticacionMiddleware
{
    public function handle(Request $request, Closure $next)
    {   
        if (TokenController::checkLogin($request, $message)) {
            return $next($request);
        } else {
            return Response($message);
        } 
    }
}