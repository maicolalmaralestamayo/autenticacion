<?php

namespace App\Http\Resources;

use App\Helpers\MaicolHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'token'=> $this->token,
            'id_usuario'=> $this->usuario_id,
            'terminal' => $this->dispositivo,
            'zona_horaria' => $this->timezone,
            'creacion'=> MaicolHelper::TimeZone($this->created_at, $this->timezone),
            'inicio'=> MaicolHelper::TimeZone(date_modify($this->created_at, $this->validez_ini), $this->timezone),
            'fin'=> MaicolHelper::TimeZone(date_modify($this->created_at, $this->validez_fin), $this->timezone),
            'intermedio'=> $this->validez_inter,
        ];
    }
}
