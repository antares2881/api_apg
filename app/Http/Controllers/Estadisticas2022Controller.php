<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Estadisticas2022Controller extends Controller
{
    
    public function estadisticas2022($corporacion, $dpto, $tipo_reporte, $mun, $par, $can, $puesto){

        if($tipo_reporte == 1){
            $select = '';
            $inner = '';
            $group = '';
            $order = '';
            $limit = 'LIMIT 30';
        }else{
            $select = 'mu.municipio, m.PUESNOMBRE,';
            $inner = 'INNER JOIN municipios as mu ON m.MUN = mu.id AND m.DEP = mu.departamento_id';
            $group = 'mu.municipio, m.PUESNOMBRE,';
            $order = ',mu.municipio ASC, m.PUESNOMBRE ASC';
            $limit = '';
        }

        if($mun != -1){
            $municipio = "AND m.MUN = $mun";
        }else{
            $municipio = '';
        }

        if($puesto != -1){
            $puesto = "AND m.PUESNOMBRE = '$puesto'";
        }else{
            $puesto = '';
        }

        if($par != -1){
            $partido = "AND m.PAR = $par";
        }else{
            $partido = '';
        }

        if($can != -1){
            $candidato = "AND m.CAN = $can";
        }else{
            $candidato = '';
        }

        $estadisticas = DB::select("SELECT $select p.partido, m.CAN, m.CANNOMBRE, SUM(m.VOTOS) as total FROM mmv_congreso_2022 as m 
            $inner
            INNER JOIN partidos2022 as p ON m.PAR = p.id
            WHERE m.CORCODIGO = ? AND m.DEP = ? $municipio $partido $candidato $puesto
            GROUP BY $group p.partido, m.CAN, m.CANNOMBRE
            ORDER BY total DESC $order 
            $limit", [$corporacion, $dpto]); //Estadisticas mayor votacion x departamento.

        $data = array(
            'status' => 'success',
            'code' => 200,
            'estadisticas' => $estadisticas
        );

        return response()->json($data, $data['code']);
    }

    public function getCandidatos($dpto, $corporacion, $partido){
        if($partido == -1){
            $condicion = '';
        }else{
            $condicion = "AND m.par = $partido";
        }
        $candidatos = DB::select("SELECT m.CAN, m.CANNOMBRE FROM mmv_congreso_2022 as m
            INNER JOIN partidos2022 as p ON m.PAR = p.id
            WHERE m.DEP = ? AND m.CORCODIGO = ? $condicion
            GROUP BY m.CAN, m.CANNOMBRE
            ORDER BY m.CAN, m.CANNOMBRE", [$dpto, $corporacion]);

        $data = array(
            'status' => 'success',
            'code' => 200,
            'candidatos' => $candidatos
        );

        return response()->json($data, $data['code']);        
    }

    public function getCorporaciones(){

        $corporaciones = DB::select("SELECT * FROM corporacionescongreso2022");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'corporaciones' => $corporaciones
        );

        return response()->json($data, $data['code']);
    }

    public function getPartidos($dpto, $corporacion){
        $partidos = DB::select('SELECT m.PAR, p.partido FROM mmv_congreso_2022 as m
            INNER JOIN partidos2022 as p ON m.PAR = p.id
            WHERE m.DEP = ? AND m.CORCODIGO = ?
            GROUP BY m.PAR, p.partido
            ORDER BY m.PAR, p.partido', [$dpto, $corporacion]);

        $data = array(
            'status' => 'success',
            'code' => 200,
            'partidos' => $partidos
        );

        return response()->json($data, $data['code']);
    }

    public function getPuestos($dpto, $mcpio){
        $puestos = DB::select('SELECT PUESNOMBRE FROM mmv_congreso_2022 WHERE DEP = ? AND MUN = ? 
        GROUP BY PUESNOMBRE
        ORDER BY PUESNOMBRE ASC', [$dpto, $mcpio]);

        $data = array(
            'status' => 'success',
            'code' => 200,
            'puestos' => $puestos
        );

        return response()->json($data, $data['code']);
    }

    public function regiones(){
        $regiones = DB::select("SELECT region FROM regiones GROUP BY region ORDER BY region ASC");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'regiones' => $regiones
        );

        return response()->json($data, $data['code']);
    }

    public function votacion_por_municipio($corporacion = 2){

        if($corporacion == 2){
            $votacion = DB::select("SELECT r.id, r.region, m.id as municipio_id, m.municipio, p.id as partido_id, p.partido, c.CAN, c.CANNOMBRE, SUM(c.VOTOS) as votacion
                FROM mmv_congreso_2022 as c 
                INNER JOIN municipios as m ON c.MUN = m.id AND m.departamento_id = 13
                INNER JOIN partidos2022 as p ON c.PAR = p.id
                            INNER JOIN regiones as r ON m.id = r.municipio_id
                WHERE c.DEP = 13 AND c.CORCODIGO = $corporacion AND ((p.id = 1 AND c.CAN = 101) OR (p.id = 2 AND c.CAN = 101) OR (p.id = 2 AND c.CAN = 105) OR (p.id = 8 AND c.CAN = 101) OR (p.id = 8 AND c.CAN = 105))
                GROUP BY r.id, r.region, m.id, m.municipio, p.id, p.partido,  c.CAN, c.CANNOMBRE
                ORDER BY m.id ASC, votacion DESC");
        }else{
            $votacion = DB::select("SELECT r.id, r.region, m.id as municipio_id, m.municipio, p.id as partido_id, p.partido, c.CAN, c.CANNOMBRE, SUM(c.VOTOS) as votacion
                FROM mmv_congreso_2022 as c 
                INNER JOIN municipios as m ON c.MUN = m.id AND m.departamento_id = 13
                INNER JOIN partidos2022 as p ON c.PAR = p.id
                            INNER JOIN regiones as r ON m.id = r.municipio_id
                WHERE c.DEP = 13 AND c.CORCODIGO = $corporacion AND ((p.id = 8 AND c.CAN = 5) OR (p.id = 8 AND c.CAN = 6) OR (p.id = 8 AND c.CAN = 8) OR (p.id = 8 AND c.CAN = 14) OR (p.id = 2 AND c.CAN = 7) OR (p.id = 2 AND c.CAN = 11) OR (p.id = 1 AND c.CAN = 2))
                GROUP BY r.id, r.region, m.id, m.municipio, p.id, p.partido,  c.CAN, c.CANNOMBRE
                ORDER BY m.id ASC, votacion DESC");
        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'votacion' => $votacion
        );

        return response()->json($data, $data['code']);
    }
}
