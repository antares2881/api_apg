<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cargo;

class CargoController extends Controller
{
    public function index(){
        $cargos = Cargo::all();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'cargos' => $cargos
        );
        return response()->json($data, $data['code']);
    }
}
