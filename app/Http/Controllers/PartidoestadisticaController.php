<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartidoestadisticaController extends Controller
{
    public function index(){
        $partidos = DB::select("SELECT * FROM partidoestadisticas");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'partidos' => $partidos
        );
        return response()->json($data, $data['code']);
    }
}
