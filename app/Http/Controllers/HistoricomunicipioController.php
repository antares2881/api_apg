<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class HistoricomunicipioController extends Controller
{
    public function historico(Request $request){

        $dpto = $request->dpto;
        $mcpio = $request->mcpio;
        $zona = $request->zona;
        $puesto = $request->puesto;
        $corporacion = $request->corporacion;

        $historico = DB::select("SELECT * 
            FROM historiomunicipios 
            WHERE departamento_id = $dpto AND municipio_id = $mcpio AND zona = '" .$zona. "' AND puesto = '" .$puesto. "' AND corporacione_id = $corporacion ");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'historico' => $historico[0]
        );

        return response()->json($data, $data['code']);
    }
}
