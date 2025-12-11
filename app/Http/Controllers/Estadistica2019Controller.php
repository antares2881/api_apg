<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Estadistica2019Controller extends Controller
{
    public function estadisticas2019(Request $request){

        $validator = \Validator::make($request->all(), [
            'municipio_id' => 'required',
            'corporacione_id' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $municipio = $request->municipio_id;
        $corporacion = $request->corporacione_id;

        $condicion_zona = '';
        $condicion_puesto = '';
        $condicion_candidato = '';
        
        if($request->zona != -1){
            $condicion_zona = "AND e.zona = $request->zona ";
        }

        if($request->puesto != -1){
            $condicion_puesto = "AND e.puesto = $request->puesto ";
        }

        if($request->candidato_id != -1){
            $condicion_candidato = "AND e.candidatoestadistica_id = $request->candidato_id ";
        }

        $estadisticas = DB::select("SELECT c.nombres, m.municipio, e.zona, e.nombre_puesto, SUM(e.votos) as votos
            FROM estadisticas2019 as e
            INNER JOIN municipios as m
            ON m.departamento_id = 13 AND e.municipio_id = m.id
            INNER JOIN candidatoestadisticas as c
            ON e.candidatoestadistica_id = c.id
            INNER JOIN partidoestadisticas as p
            ON c.partidoestadistica_id = p.id
            WHERE e.municipio_id = $municipio AND e.corporacione_id = $corporacion $condicion_zona $condicion_puesto $condicion_candidato
            GROUP BY e.nombre_puesto, c.nombres, m.municipio, e.zona
            ORDER BY c.nombres, zona ASC, e.nombre_puesto");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'estadisticas' => $estadisticas
        );
        return response()->json($data, $data['code']);
    }

    public function getPuestos($municipio, $zona){
        $puestos = DB::select("SELECT puesto, nombre_puesto 
            FROM estadisticas2019 
            WHERE municipio_id = $municipio AND zona = $zona
            GROUP BY puesto, nombre_puesto 
            ORDER BY puesto ASC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'puestos' => $puestos
        );
        return response()->json($data, $data['code']);
    }

    public function getZonas($municipio){
        $zonas = DB::select("SELECT zona FROM estadisticas2019 
            WHERE municipio_id = $municipio
            GROUP BY zona 
            ORDER BY zona ASC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'zonas' => $zonas
        );
        return response()->json($data, $data['code']);
    }
}
