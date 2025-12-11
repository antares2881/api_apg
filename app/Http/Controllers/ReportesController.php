<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Candidato;

class ReportesController extends Controller
{
    public function reporteFirmasUsuarios(Request $request){ //ok

        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;
        if($request->role == 1){
            $candidato = '';
        }else{
            $candidato = 'u.candidato_id = '. $request->candidato . ' AND';
        }

        $registros = DB::select("SELECT f.user_id, u.username, COUNT(f.user_id) as cantidad FROM firmas as f
            INNER JOIN users as u
            ON f.user_id = u.id
            WHERE $candidato f.fecha_firma BETWEEN ' " . $fecha1. " ' AND ' " . $fecha2. " '
            GROUP BY f.user_id ORDER BY cantidad DESC"
        );
        $data = array(
            'status' => 'success',
            'code' => 200,
            'registros' => $registros
        );
        return response()->json($data, $data['code']);
    }

    public function reporteFirmasRepetidos(Request $request){ //ok

        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;
        $candidato = $request->candidato;

        $registros = DB::select("SELECT f.*, u.username, r.nombres as nombre_recolector, r.apellidos as apellidos_recolector FROM firmas as f
            INNER JOIN users as u
            ON f.user_id = u.id
            INNER JOIN recolectores as r
            ON f.recolectore_id = r.id
            WHERE f.cedula IN (
                SELECT cedula FROM firmas 
                    GROUP BY cedula
                    HAVING COUNT(*)>1
            ) AND u.candidato_id = $candidato AND f.fecha_firma BETWEEN ' " . $fecha1. " ' AND ' " . $fecha2. " '
            ORDER BY f.cedula, f.fecha_firma
            LIMIT 100
        ");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'registros' => $registros
        );

        return response()->json($data, $data['code']);
        
    }

    public function reporteFirmasNoRegion(Request $request){ //ok

        $candidato = $request->candidato;

        $corporacion = DB::select("SELECT * FROM candidatos WHERE id = $candidato");

        if($corporacion[0]->corporacione_id == 6 || $corporacion[0]->corporacione_id == 7){
            $condicion = 'f.departamento_id <> c.departamento_id';
        }else{
            $condicion = '(f.departamento_id <> c.departamento_id OR f.municipio_id <> c.municipio_id)';
        }

        $registros = DB::select("SELECT f.cedula, f.nombres, f.apellidos, f.fecha_firma, d.departamento, m.municipio, u.username FROM firmas AS f
            INNER JOIN users AS u
            ON f.user_id = u.id
            INNER JOIN departamentos as d
            ON f.departamento_id = d.id
            INNER JOIN municipios as m
            ON f.municipio_id = m.id AND f.departamento_id = m.departamento_id
            INNER JOIN candidatos as c
            ON u.candidato_id = c.id
            WHERE u.candidato_id = $candidato AND f.observacione_id = 2
            ORDER BY d.departamento ASC, m.municipio ASC");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'registros' => $registros
        );
        return response()->json($data, $data['code']);
    }

    public function reporteFirmasGeneral($candidato){ //ok

        $corporacion = DB::select("SELECT * FROM candidatos WHERE id = $candidato");

        if($corporacion[0]->corporacione_id == 6 || $corporacion[0]->corporacione_id == 7){
            $condicion = '';
        }else{
            $condicion = 'AND f.municipio_id = c.municipio_id';
        }

        $registros = DB::select("SELECT f.cedula, f.nombres, f.apellidos, f.fecha_firma, u.username, m.municipio, f.puesto, f.mesa
            FROM firmas as f
            INNER JOIN users as u
            ON f.user_id = u.id
            INNER JOIN municipios as m
            ON f.departamento_id = m.departamento_id AND f.municipio_id = m.id
            INNER JOIN candidatos as c
            ON u.candidato_id = c.id
            WHERE u.candidato_id = $candidato AND f.observacione_id = 1
        ");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'registros' => $registros
        );

        return response()->json($data, $data['code']);

    }

    public function reporteFirmasRecolector(Request $request){ //ok

        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;
        $candidato = $request->candidato;

        $registros = DB::select("SELECT r.nombres, r.apellidos, COUNT(f.recolectore_id) as total 
            FROM firmas as f 
            INNER JOIN users as u
            ON f.user_id = u.id
            INNER JOIN recolectores as r
            ON f.recolectore_id = r.id
            WHERE u.candidato_id = $candidato AND f.fecha_firma BETWEEN ' ".$fecha1." ' AND '" .$fecha2." '
            GROUP BY r.nombres, r.apellidos
            ORDER BY total DESC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'registros' => $registros
        );
        return response()->json($data, $data['code']);
    }

    public function reporteFirmasValidas(Request $request){

        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;
        $candidato = $request->candidato;
        $filtro = $request->filtro;

        $lugar = Candidato::find($candidato);
        $dpto = $lugar->departamento_id;
        $mcpio = $lugar->municipio_id;

        if($fecha1 == 0 || $fecha2 == 0){
            $condicion_fecha = '';
        }else{
            $condicion_fecha = " AND f.fecha_firma BETWEEN ' " . $fecha1. " ' AND ' " . $fecha2. " ' ";
        }

        if($filtro == 0){
            $registros = DB::select("SELECT o.observacion, COUNT(f.observacione_id) as total 
                FROM firmas as f 
                INNER JOIN observaciones as o ON f.observacione_id = o.id
                WHERE f.departamento_id = $dpto AND f.municipio_id = $mcpio $condicion_fecha
                GROUP BY o.observacion
            ");
        }else if($filtro == 1){
            $registros = DB::select("SELECT u.username, COUNT(user_id) as total FROM firmas as f 
                INNER JOIN users as u ON f.user_id = u.id 
                WHERE u.candidato_id = $candidato AND f.departamento_id = $dpto AND f.municipio_id = $mcpio $condicion_fecha
                GROUP BY u.username
                ORDER BY total DESC
            ");
        }else{
            $recolectore_id = $request->recolectore_id;

            if($recolectore_id == 0){
                $condicion_recolector = '';
            }else{
                $condicion_recolector = "AND f.recolectore_id = $recolectore_id";
            }

            $registros = DB::select("SELECT r.nombres, r.apellidos, COUNT(f.recolectore_id) as total 
                FROM firmas as f 
                INNER JOIN recolectores as r ON f.recolectore_id = r.id
                WHERE r.candidato_id = $candidato AND f.departamento_id = $dpto AND f.municipio_id = $mcpio $condicion_fecha $condicion_recolector
                GROUP BY r.nombres, r.apellidos
                ORDER BY total DESC
            ");
        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'registros' => $registros
        );

        return response()->json($data, $data['code']);
    }

    public function reporteFirmasNoValidas(Request $request){

        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;
        $candidato = $request->candidato;
        $filtro = $request->filtro;

        if($fecha1 == 0 || $fecha2 == 0){
            $condicion_fecha = '';
        }else{
            $condicion_fecha = " AND f.fecha_firma BETWEEN ' " . $fecha1. " ' AND ' " . $fecha2. " ' ";
        }

        if($filtro == 0){
            $registros = DB::select("SELECT o.observacion, COUNT(f.observacione_id) as total 
                FROM firmas as f 
                INNER JOIN observaciones as o ON f.observacione_id = o.id
                WHERE f.observacione_id = 3 $condicion_fecha
                GROUP BY o.observacion
            ");
        }else if($filtro == 1){
            $registros = DB::select("SELECT u.username, COUNT(user_id) as total FROM firmas as f 
                INNER JOIN users as u ON f.user_id = u.id 
                WHERE u.candidato_id = $candidato AND f.observacione_id = 3 $condicion_fecha
                GROUP BY u.username
                ORDER BY total DESC
            ");
        }else{
            $recolectore_id = $request->recolectore_id;

            if($recolectore_id == 0){
                $condicion_recolector = '';
            }else{
                $condicion_recolector = "AND f.recolectore_id = $recolectore_id";
            }

            $registros = DB::select("SELECT r.nombres, r.apellidos, COUNT(f.recolectore_id) as total 
                FROM firmas as f 
                INNER JOIN recolectores as r ON f.recolectore_id = r.id
                WHERE r.candidato_id = $candidato AND f.observacione_id = 3 $condicion_fecha $condicion_recolector
                GROUP BY r.nombres, r.apellidos
                ORDER BY total DESC
            ");
        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'registros' => $registros
        );

        return response()->json($data, $data['code']);
    }

    public function reporteVotantesRepetidos($corporacion){

        $condicion_general = "";
        $condicion_in = "";
        $candidato = Auth::user()->candidato_id;

        if($corporacion == 5){
            $condicion_general = "lv.candidato_id = $candidato AND ";
            $condicion_in = "WHERE candidato_id = $candidato ";
        }

        $repetidos = DB::select("SELECT lv.id, lv.nombres, lv.apellidos, c.nombres as candidato, l.nombres as nombre_lider, l.apellidos as apellidos_lider, lv.created_at as fecha_ingreso
            FROM listadovotantes as lv
            INNER JOIN candidatos as c ON lv.candidato_id = c.id
            INNER JOIN lideres as l ON lv.lidere_id = l.id
            WHERE $condicion_general lv.id
            IN (SELECT id
            FROM listadovotantes
            $condicion_in
            GROUP BY id
            HAVING count(id) >1)
            ORDER BY nombres, fecha_ingreso");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'repetidos' => $repetidos
        );

        return response()->json($data, $data['code']);

    }
}
