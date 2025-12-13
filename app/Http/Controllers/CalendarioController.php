<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Calendario;

class CalendarioController extends Controller
{
    public function desactivar($fecha){
        $calendario = DB::table('calendarios')->where('fecha', $fecha)->update(['desactivar_notificacion' => 1]);
        $data = array(
            'status' => 'success',
            'code' => 200,
            'calendaro' => $calendario
        );
        return response()->json($data, $data['code']);
    }

    public function index($anio, $mes){
        $calendarios = Calendario::where([
            ['anio', $anio],
            ['mes', $mes],
        ])
        ->orderBy('fecha', 'asc')
        ->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'calendarios' => $calendarios
        );
        return response()->json($data, $data['code'], [], JSON_UNESCAPED_UNICODE);
    }

    public function shows(Request $request){
        $calendarios = Calendario::where([
            ['fecha', $request->fecha],
            // ['prioridad', '>=', 3],
            ['desactivar_notificacion',  0]
        ])->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            '$calendarios' => $calendarios
        );
        return response()->json($data, $data['code']);
    }
}
