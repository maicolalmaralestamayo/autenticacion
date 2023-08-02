<?php

namespace App\Http\Controllers;

use App\Helpers\MaicolHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\RolResource;
use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index_show_request(Request $request){
        $modelo = Rol::all();

        if ($request->identificador) {
            $modelo = $modelo->where('id', $request->identificador);
        }
        if ($request->nomb) {
            $modelo = $modelo->where('nombre', $request->nomb);
        }
        if ($request->descrip) {
            $modelo = $modelo->where('descripcion', $request->descrip);
        }

        if ($modelo->isEmpty()) {
            return MaicolHelper::Data(null, 404, false, 'No se encontraron datos (Función index_show_request).');
        } else {
            $modelo = $modelo->toQuery()->paginate($request->cant? : 0);
            $recurso = RolResource::collection($modelo);
            return MaicolHelper::Data($recurso, 200, true, 'Operación realizada satisfactoriamente.');
        }
    }

    public function store(Request $request){
        $modelo = new Rol();
        $modelo->nombre = $request->nomb;
        $modelo->descripcion = $request->descrip? : null;
        $modelo->save();

        $recurso = new RolResource($modelo);
        return MaicolHelper::Data($recurso, 200, true, 'Rol creado satisfactoriamente.');
    }

    // public function store_several(Request $request){
    //     $arreglo = $request->toArray();
    //     foreach ($arreglo as $value) {
    //         $modelo = new Rol();
    //         $modelo->nombre = $value['nomb'];
    //         $modelo->descripcion = key_exists('descrip', $value)? $value['nomb'] : null;
    //         $modelo->save();
    //     }
    //     return MaicolHelper::Data(null, 200, true, 'Roles creados satisfactoriamente.');
    // }

    public function crear(Request $request){
        foreach ($request->crear as $key => $value) {
            // return $value;
            $modelo = Rol::create($value);
            
            // $modelo = $value;
            // $modelo->nombre = $value['nomb'];
            // $modelo->descripcion = key_exists('descrip', $value)? $value['nomb'] : null;
            // $modelo->save();
        }
    }
    
    public function update(Request $request){
        $modelo = MaicolHelper::Buscar($request, Rol::all());

        if ($modelo->isEmpty()) {
            return MaicolHelper::Data(null, 404, false, 'No se encontraron datos para actualizar.');
        } else {
            $modelo->toQuery()->update($request->actualizar);
            // $modelo->toQuery()->update(["nombre" => $request->actualizar['nomb'], 
            //                            ["descripcion" => $request->actualizar['descrip']]]); //actualizar haciendo cambio de nombre de tabla
            $recurso = RolResource::collection($modelo->toQuery()->paginate($request->meta['cant']? : 0));
            return MaicolHelper::Data($recurso, 200, true, 'Actualización satisfactoria.');
        }
    }

    public function delete(Request $request){
        $modelo = MaicolHelper::Buscar($request, Rol::all());

        if ($modelo->isEmpty()) {
            return MaicolHelper::Data(null, 404, false, 'No se encontraron datos para eliminar.');
        } else {
            $recurso = RolResource::collection($modelo->toQuery()->paginate($request->meta['cant']? : 0));
            $modelo->toQuery()->delete();
            return MaicolHelper::Data($recurso, 200, true, 'Eliminación satisfactoria.');
        }
    }
}