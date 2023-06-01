<?php

namespace App\Http\Controllers;

use App\Helpers\MaicolHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\RolResource;
use App\Models\Rol;

class RolController extends Controller
{
    public function index(){
        $rol = Rol::first();//Model
        $roles = Rol::all();//Collection
        $roles_pag = Rol::paginate(2);//LengthAwarePaginator
        

        $rol_res = new RolResource(Rol::first());//JsonResource
        $roles_res = RolResource::collection(Rol::all());//JsonResource + ResourceCollection
        $roles_pag_res = RolResource::collection(Rol::paginate(2));//JsonResource + ResourceCollection
        
        $arreglo_simple = ['nombre' => 'maicol', 'apellido' => 'almarales'];//arreglo de un elemento
        $arreglo_mult = [['nombre' => 'maicol', 'apellido' => 'almarales'], ['nombre' => 'marlon', 'apellido' => 'tamayo']];//arreglo de varios elementos
        
        return MaicolHelper::Data(null);
    }
}
