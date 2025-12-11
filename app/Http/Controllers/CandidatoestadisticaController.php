<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CandidatoestadisticaController extends Controller
{
    public function getCandidatos($corporacion, $municipio, $partido){
        if($partido == -1){
            $condicion = '';
        }else{
            $condicion = "AND partidoestadistica_id = $partido ";
        }
        $candidatos = DB::select("SELECT * FROM candidatoestadisticas 
            WHERE corporacione_id = $corporacion AND municipio_id = $municipio $condicion ");
        
        $data = array(
            'status' => 'success',
            'code' => 200,
            'candidatos' => $candidatos
        );

        return response()->json($data, $data['code']);
    }
}
