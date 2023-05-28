<?php

namespace App\Helpers;

use App\Http\Resources\MaicolCollection;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Resources\Json\JsonResource;

class MaicolHelper {
    
    public static function Data(JsonResource $data=null,
                                int $code=200,
                                $state=true,
                                $message='Operación realizada satisfactoriamente.'){
        
        if ($data ==null || !$data) {
            $data = new MaicolCollection([]);
        }
        
        return $data->additional([
            'state' => $state,
            'code' => $code,
            'message' => $message])
        ->response()->setStatusCode($code);

    //luego en el controlador siempre hay que pasar un Resource SIEMPRE. Para valores fuera de recursos, hay que conertilos utilizando el MaicolResource y pasando como parámetro un arreglo [] con el o los valores
    // return MaicolHelper::Data(new MaicolCollection([$token->token]),true, 201, null);
    }

    public static function TimeZone(string $dateTime, string $timeZoneTo='America/Havana', string $timeZoneFrom='UTC', string $format='Y-m-d H:i:s'){
        $timeZoneFrom = new DateTimeZone($timeZoneFrom);
        $dateTimeFrom = new DateTime($dateTime, $timeZoneFrom);

        $timeZoneTo = new DateTimeZone($timeZoneTo);
        $dateTimeTo = $dateTimeFrom;
        $dateTimeTo->setTimezone($timeZoneTo);

        return $dateTimeTo->format($format);
    }
}

