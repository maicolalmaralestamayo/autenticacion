<?php

namespace App\Exceptions;

use App\Helpers\MaicolHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use LogicException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    // public function render($request, Throwable $exception)
    // {   
    //     $fichero = explode('\\', $exception->getFile());

    //     if ($exception) {
    //         return MaicolHelper::Data(null, 500, false, end($fichero).'|'.$exception->getLine().'|'.$exception->getMessage());
    //     }
        
    //     return false;
    // }

}
