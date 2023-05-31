<?php

namespace App\Http\Controllers;

use App\Helpers\MaicolHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\MaicolCollection;
use App\Http\Resources\RolResource;
use App\Models\Rol;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index(){
        //valores simples
        $rol = new RolResource(Rol::first()) ; //un recurso
        $numero = 3;
        $texto = 'texto';
        $arreglo_simple = ['nombre' => 'maicol', 'apellido' => 'almarales'];

        //valores múltiples sin pasar por el recurso
        $roles = Rol::paginate(2);  //colleccion
        $arreglo_mult = [['nombre' => 'maicol', 'apellido' => 'almarales'], ['nombre' => 'marlon', 'apellido' => 'tamayo']];
        
        return MaicolHelper::prueba([$arreglo_simple]);
    }
}
