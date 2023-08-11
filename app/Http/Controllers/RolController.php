<?php

namespace App\Http\Controllers;

use App\Helpers\MaicolHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\RolResource;
use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    //OK
    public function show(Request $request){
        if ($request->meta['all'] === true) {
            $modelo = Rol::all();
        } else {
            $modelo = MaicolHelper::Buscar($request->filter, Rol::all());
        }
        $cant = $modelo->count();

        if ($cant > 0) {
            return MaicolHelper::Data($modelo, 200, true, 'Consulta satisfactoria.');
        } else {
            return MaicolHelper::Data(null, 404, false, 'No se encontraron datos para mostrar.');
        }
    }

    //OK
    public function store(Request $request){
        if ($request->meta['several'] === false) {
            $cant = 1;
            $modelo = Rol::create($request['store']);
        } else {
            $cant = 0;
            $modelo = [];
            foreach ($request->store as $value) {
                $cant++;
                $modelo[] = Rol::create($value);
            }
        }
        return MaicolHelper::Data($modelo, 200, true, 'Creación satisfactoria.  Registros creados: '.$cant);
    }
    
    //OK
    public function update(Request $request){
        if ($request->meta['all'] === true) {
            $modelo = Rol::all();
        } else {
            $modelo = MaicolHelper::Buscar($request->filter, Rol::all());
        }
        $cant = $modelo->count();
        
        if ($cant > 0) {
            $modelo->toQuery()->update($request->update);
            return MaicolHelper::Data(null, 200, true, 'Actualización satisfactoria.  Registros actualizados: '.$cant);
        } else {
            return MaicolHelper::Data(null, 200, true, 'No se encontraron datos para actualizar.');
        }
    }

    //OK
    public function delete(Request $request){
        if ($request->meta['all'] === true) {
            $modelo = Rol::all();
        } else {
            $modelo = MaicolHelper::Buscar($request->filter, Rol::all());
        }
        $cant = $modelo->count();

        if ($cant > 0) {
            if ($request->meta['all'] === true && $request->meta['reset'] === true) {
                $modelo->toQuery()->truncate();
            } else {
                $modelo->toQuery()->delete();
            }
            return MaicolHelper::Data(null, 200, true, 'Eliminación satisfactoria.  Registros eliminados: '.$cant);
        } else {
            return MaicolHelper::Data(null, 200, true, 'No se encontraron datos para eliminar.');
        }
    }
}