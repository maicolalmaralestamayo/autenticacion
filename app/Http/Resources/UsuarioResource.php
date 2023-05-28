<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UsuarioResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'identificador'=> $this->id,
            'nick_name'=> $this->nick,
            'carne_civil' => $this->carne,
            'primer_nombre' => Str::lower($this->nomb1),
            'segundo_nombre' => Str::lower($this->nomb2),
            'primer_apellido' => Str::lower($this->apell1),
            'segundo_apellido' => Str::lower($this->apell2),
            'correo'=> $this->email
        ];
    }
}
