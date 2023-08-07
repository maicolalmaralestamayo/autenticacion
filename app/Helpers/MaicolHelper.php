<?php

namespace App\Helpers;

use App\Http\Resources\MaicolCollection;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class MaicolHelper {
    
    public static function Data($data=null, int $code=200, $state=true, $message='Operación realizada satisfactoriamente.'){
        if ($data instanceof Model || $data instanceof Collection || is_array($data) || $data == null) {
            $data = collect($data);
            $data = new MaicolCollection($data);
        }

        if ($data instanceof LengthAwarePaginator) {
            $data = new MaicolCollection($data);
        }

        return $data->additional([
            'state' => $state,
            'code' => $code,
            'message' => $message])
        ->response()->setStatusCode($code);

        // if ($data instanceof LengthAwarePaginator) {
        //     return 'es un modelo';
        // }

        // if ($data instanceof Model) {
        //     return 'es un modelo';
        // }

        // if ($data instanceof Collection) {
        //     return 'es una colección';
        // }

        // if ($data instanceof ResourceCollection) {
        //     return 'es un resource collection';
        // }

        // if ($data instanceof JsonResource) {
        //     return 'es un resource';
        // }
        
        // if (is_array($data)) {
        //     return 'es un arreglo';
        // }
    }

    public static function TimeZone(string $dateTime, string $timeZoneTo='America/Havana', string $timeZoneFrom='UTC', string $format='Y-m-d H:i:s'){
        $timeZoneFrom = new DateTimeZone($timeZoneFrom);
        $dateTimeFrom = new DateTime($dateTime, $timeZoneFrom);

        $timeZoneTo = new DateTimeZone($timeZoneTo);
        $dateTimeTo = $dateTimeFrom;
        $dateTimeTo->setTimezone($timeZoneTo);

        return $dateTimeTo->format($format);
    }

    public static function Buscar($request, $modelo){
        foreach ($request as $key => $value) {
            $modelo = $modelo->where($key, $value);
        }
        return $modelo;
    }
}