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
        return $request;
        
        if ($request->buscar != null) {
            $modelo = MaicolHelper::Buscar($request->buscar, Rol::all());
        } else {
            $modelo = 'maicol';
        }
        
        return $modelo;

        if ($modelo->isEmpty()) {
            return MaicolHelper::Data(null, 404, false, 'No se encontraron datos.');
        } else {
            $modelo = $modelo->toQuery()->paginate($request->cant? : 0);
            $recurso = RolResource::collection($modelo);
            return MaicolHelper::Data($recurso, 200, true, 'Consulta satisfactoria.');
        }
    }

    public function crear(Request $request){
        $id = [];
        foreach ($request->crear as $key => $value) {
            $modelo = Rol::create($value);
            array_push($id, $modelo->id);
        }
        $recurso = RolResource::collection($modelo->orWhere('id', $id)->paginate($request->meta['cant']? : 0));
        return MaicolHelper::Data($recurso, 200, true, 'Creación satisfactoria.');
    }
    
    public function update(Request $request){
        $modelo = MaicolHelper::Buscar($request->buscar, Rol::all());

        if ($modelo->isEmpty()) {
            return MaicolHelper::Data(null, 404, false, 'No se encontraron datos para actualizar.');
        } else {
            $modelo->toQuery()->update($request->actualizar);
            $recurso = RolResource::collection($modelo->toQuery()->paginate($request->meta['cant']? : 0));
            return MaicolHelper::Data($recurso, 200, true, 'Actualización satisfactoria.');
        }
    }

    public function delete(Request $request){
        $modelo = MaicolHelper::Buscar($request->buscar, Rol::all());

        if ($modelo->isEmpty()) {
            return MaicolHelper::Data(null, 404, false, 'No se encontraron datos para eliminar.');
        } else {
            $recurso = RolResource::collection($modelo->toQuery()->paginate($request->meta['cant']? : 0));
            $modelo->toQuery()->delete();
            return MaicolHelper::Data($recurso, 200, true, 'Eliminación satisfactoria.');
        }
    }
}