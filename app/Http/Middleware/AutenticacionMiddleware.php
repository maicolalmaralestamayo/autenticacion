<?php

namespace App\Http\Middleware;

use App\Helpers\MaicolHelper;
use App\Http\Controllers\TokenController;
use Closure;
use Illuminate\Http\Request;

class AutenticacionMiddleware
{
    public function handle(Request $request, Closure $next)
    {   
        $token = new TokenController;
        return $token->checkLogin($request, $code, $message)? $next($request) : MaicolHelper::Encap(null, $code, false, $message);
    }
}