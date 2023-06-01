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
        $roles = Rol::all(); //un recurso
        $roles_pag = Rol::paginate(2);  //colleccion
        $rol = Rol::first();  //colleccion

        $roles_res = RolResource::collection(Rol::all()); //un recurso
        $roles_pag_res = RolResource::collection(Rol::paginate(2));  //colleccion
        $rol_res = new RolResource(Rol::first());  //colleccion
        
        $numero = 3;
        $texto = 'texto';
        $arreglo_simple = ['nombre' => 'maicol', 'apellido' => 'almarales'];
        $arreglo_mult = [['nombre' => 'maicol', 'apellido' => 'almarales'], ['nombre' => 'marlon', 'apellido' => 'tamayo']];
        
        return MaicolHelper::prueba($rol);
    }
}
