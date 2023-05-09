<?php

namespace App\Http\Controllers;

use App\Models\Dato;
use Illuminate\Http\Request;

class DatoController extends Controller
{
    public function index(){
        return Dato::all();
    }
}
