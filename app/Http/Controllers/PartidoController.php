<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partido;

class PartidoController extends Controller
{
    public function index(){

        $partidos = Partido::all();

        $data = array(
            'code' => 200,
            'status' => 'success',
            'partidos' => $partidos
        );

        return response()->json($data, $data['code']);
    }

    public function show($id){
        $partido = Partido::find($id);
        $data = array(
            'status' => 'success',
            'code' => 200,
            'partido' => $partido
        );
        return response()->json($data, $data['code']);
    }
}
