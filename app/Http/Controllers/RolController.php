<?php

namespace App\Http\Controllers;

use App\Helpers\MaicolHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\RolResource;
use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function show(Request $request){
        $modelo = $request->show? MaicolHelper::Buscar($request->show, Rol::all()) : Rol::all();
        
        if ($modelo->isEmpty()) {
            return MaicolHelper::Data(null, 404, false, 'No se encontraron datos para mostrar.');
        } else {
            $paginado = $modelo->toQuery()->paginate($request->meta['pag']? : 0);
            $recurso = RolResource::collection($paginado);
            return MaicolHelper::Data($recurso, 200, true, 'Consulta satisfactoria.');
        }
    }

    public function store(Request $request){
        $id = [];
        foreach ($request->store as $key => $value) {
            $modelo = Rol::create($value);
            array_push($id, $modelo->id);
        }
        $paginado = $modelo->where('id', $id)->paginate($request->meta['pag']? : 0);
        $recurso = RolResource::collection($paginado);
        return MaicolHelper::Data($recurso, 200, true, 'Creación satisfactoria.');
    }
    
    public function update(Request $request){
        $modelo = $request->show['id'] === '*'? Rol::all() : MaicolHelper::Buscar($request->show, Rol::all());
        $cant = $modelo->count();

        if ($cant == 0) {
            return MaicolHelper::Data(null, 404, false, 'No se encontraron datos para actualizar.');
        } else {
            $modelo->toQuery()->update($request->update);
            return MaicolHelper::Data(null, 200, true, 'Actualización satisfactoria.  Registros actualizados: '.$cant);
        }
    }

    // OK
    public function delete(Request $request){
        if ($request->meta['all'] === true) {
            $modelo = Rol::all();
        } else {
            $modelo = MaicolHelper::Buscar($request->show, Rol::all());
        }
        $cant = $modelo->count();

        if ($cant > 0) {
            if ($request->meta['all'] === true && $request->meta['reset'] === true) {
                $modelo->toQuery()->truncate();
            } else {
                $modelo->toQuery()->delete();
            }
        }
        
        return MaicolHelper::Data(null, 200, true, 'Eliminación satisfactoria.  Registros eliminados: '.$cant);
    }
}