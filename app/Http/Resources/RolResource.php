<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RolResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'identificador'=> $this->id,
            'nomb'=> $this->nombre,
            'descrip' => $this->descripcion
        ];
    }
}
