<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Estadisticas2023Controller extends Controller
{
    public function estadisticas2023($corporacion, $municipio, $comuna, $puesto, $mesa, $partido, $candidato, $agrupacion){
    
        $condicion_comuna = '';
        $condicion_municipio = '';
        $condicion_candidato = '';
        $condicion_partido = '';
        $condicion_puesto = '';
        $condicion_mesa = '';

        if($municipio != -1){
            $condicion_municipio = "AND municipio = '". $municipio."'";
        }
        
        if($comuna != -1){
            $condicion_comuna = "AND cod_comuna = $comuna";
        }

        if($puesto != -1){
            $condicion_puesto = "AND puesto = '$puesto'";
        }

        if($mesa != -1){
            $condicion_mesa= "AND Mesa = $mesa";
        }

        if($partido != -1){
            $condicion_partido = "AND cod_partido = $partido";
        }

        if($candidato != -1){
            $condicion_candidato = "AND id_candidato = $candidato";
        }

        if($agrupacion == 1){
            $select = "comuna, puesto, Mesa,";
            $group_by = "comuna, puesto, Mesa, candidato, municipio, partido";
            $order_by = "comuna ASC,";
        }else{
            $group_by = "municipio, candidato, partido";
            $select = "";
            $order_by = "municipio, ";
        }

        $estadisticas = DB::select("SELECT municipio, $select candidato, partido, SUM(`Total Votos`) as votacion
            FROM mmv20231113
            WHERE corporacion = ? $condicion_municipio $condicion_comuna $condicion_partido $condicion_candidato $condicion_puesto $condicion_mesa
            GROUP BY $group_by 
            ORDER BY $order_by votacion DESC", ["$corporacion"]);

        $data = array(
            'status' => 'success',
            'code' => 200,
            'estadisticas' => $estadisticas
        );
        return response()->json($data, $data['code']);
    }

    public function getCorporacion($cod_dpto){
        $corporaciones = DB::select('SELECT corporacion FROM mmv20231113 
        
        GROUP BY corporacion
        ORDER BY corporacion ASC', [$cod_dpto]);
        $data = array(
            'status' => 'success',
            'code' => 200,
            'corporaciones' => $corporaciones
        );
        return response()->json($data, $data['code']);
    }

    public function getPuestos($departamento, $municipio){
        $puestos = DB::select("SELECT puesto FROM mmv20231113

            WHERE municipio = $municipio
            GROUP BY puesto
            ORDER BY puesto ASC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'puestos' => $puestos
        );
        return response()->json($data, $data['code']);
    }

    public function getMesas($puesto){
        $mesas = DB::select('SELECT m.Mesa FROM mmv20231113 as m 
            WHERE m.puesto = ?
            GROUP BY m.Mesa
            ORDER BY m.Mesa ASC', [$puesto]);
        $data = array(
            'status' => 'success',
            'code' => 200,
            'mesas' => $mesas
        );
        return response()->json($data, $data['code']);
    }

    public function getCandidatos($departamento, $municipio, $corporacion, $partido){

        $condicion_municipio = ($municipio == -1) ? '' : "AND municipio = $municipio";
        $condicion_partido = ($partido == -1) ? '' : "AND cod_partido = $partido";

        $candidatos = DB::select("SELECT candidato, id_candidato FROM mmv20231113 
        WHERE corporacion = ? $condicion_partido
        GROUP BY candidato, id_candidato
        ORDER BY candidato, id_candidato ASC", ["$corporacion"]);
        $data = array(
            'status' => 'success',
            'code' => 200,
            'candidatos' => $candidatos
        );
        return response()->json($data, $data['code']);
    }

    public function getComunas($departamento, $municipio){
        $comunas = DB::select("SELECT comuna, cod_comuna FROM mmv20231113 
            WHERE municipio = ?
            GROUP BY comuna, cod_comuna
            ORDER BY comuna, cod_comuna ASC", [$municipio]);
        $data = array(
            'status' => 'success',
            'code' => 200,
            'comunas' => $comunas
        );
        return response()->json($data, $data['code']);
    }

    public function getPartidos($departamento, $municipio){
        $condicion_municipio = ($municipio == -1) ? '' : "WHERE municipio = $municipio";
        $partidos = DB::select("SELECT cod_partido, partido FROM mmv20231113 
            $condicion_municipio
            GROUP BY cod_partido, partido
            ORDER BY cod_partido ASC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'partidos' => $partidos
        );
        return response()->json($data, $data['code']);
    }

    public function getMunicipios($cod_dpto, $corporacion){
        $municipios = DB::select('SELECT municipio FROM mmv20231113 
            WHERE corporacion = ?
            GROUP BY municipio
            ORDER BY municipio ASC', ["$corporacion"]);
        $data = array(
            'status' => 'success',
            'code' => 200,
            'municipios' => $municipios
        );
        return response()->json($data, $data['code']);
    }


}
