<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Turno;

class TurnoController extends Controller
{
    public function show($fecha){
        /* $turnos = DB::select("SELECT t.id, t.estado_id, t.fecha, t.hora_inicio, t.hora_fin, a.id as agenda_id, a.lugar, a.persona, a.observacion FROM turnos as t 
            LEFT JOIN agendas as a ON a.turno_id = t.id 
            WHERE fecha =  '". $fecha ."'
            ORDER BY t.fecha ASC"); */
        $turnos = Turno::with('agenda')->where('fecha', $fecha)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'turnos' => $turnos
        );
        return response()->json($data, $data['code']);
    }
}
