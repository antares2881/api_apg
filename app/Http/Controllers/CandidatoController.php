<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Candidato;

class CandidatoController extends Controller
{    
    public function delete($id){
        $candidato = Candidato::find($id)->delete();
        return 'ok';
    }

    public function index(Request $request){       
        
        // $candidatos = Candidato::with(['corporacion', 'partido'])->get();
        $candidatos = DB::select("SELECT c.*, cor.corporacion, p.partido, p.logo, d.departamento, m.municipio FROM candidatos as c
            INNER JOIN corporaciones as cor
            ON c.corporacione_id = cor.id
            INNER JOIN partidos as p
            ON c.partido_id = p.id
            INNER JOIN departamentos as d
            ON c.departamento_id = d.id
            LEFT JOIN municipios as m
            ON c.departamento_id = m.departamento_id AND c.municipio_id = m.id
            ORDER BY cor.corporacion DESC, c.nombres DESC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'candidatos' => $candidatos
        );
        return response()->json($data, $data['code']);
    }


    public function show($corporacione_id){   

        $condicion = "";   
        if( $corporacione_id == 5){
            $candidato_id = Auth::user()->candidato_id;
            $condicion = "WHERE c.id = $candidato_id";
        }

        $candidatos = DB::select("SELECT c.*, cor.corporacion, p.partido, p.logo, d.departamento, m.municipio FROM candidatos as c
            INNER JOIN corporaciones as cor
            ON c.corporacione_id = cor.id
            INNER JOIN partidos as p
            ON c.partido_id = p.id
            INNER JOIN departamentos as d
            ON c.departamento_id = d.id
            LEFT JOIN municipios as m
            ON c.departamento_id = m.departamento_id AND c.municipio_id = m.id
            $condicion
            ORDER BY cor.corporacion DESC, c.nombres DESC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'candidatos' => $candidatos
        );
        return response()->json($data, $data['code']);
    }

    public function store(Request $request){

        $validator = \Validator::make($request->all(), [
            'nombres' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'partido_id' => 'required',
            'corporacione_id' => 'required',
            'departamento_id' => 'required',
            'meta_votacion' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $candidato = new Candidato();
        $candidato->nombres = $request->nombres;
        $candidato->direccion = $request->direccion;
        $candidato->telefono = $request->telefono;
        $candidato->partido_id = $request->partido_id;
        $candidato->corporacione_id = $request->corporacione_id;
        $candidato->departamento_id = $request->departamento_id;
        $candidato->municipio_id = $request->municipio_id;
        $candidato->meta_votacion = $request->meta_votacion;

        $candidato->save();
        $id = $candidato->id;
        $new_candidato = DB::select("SELECT c.*, cor.corporacion, p.partido, p.logo, d.departamento, m.municipio FROM candidatos as c
            INNER JOIN corporaciones as cor
            ON c.corporacione_id = cor.id
            INNER JOIN partidos as p
            ON c.partido_id = p.id
            INNER JOIN departamentos as d
            ON c.departamento_id = d.id
            LEFT JOIN municipios as m
            ON c.departamento_id = m.departamento_id AND c.municipio_id = m.id
            WHERE c.id = $id");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'candidato' => $new_candidato
        );
        return response()->json($data, $data['code']);
    }

    public function update(Request $request, $id){

        $validator = \Validator::make($request->all(), [
            'nombres' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'partido_id' => 'required',
            'corporacione_id' => 'required',
            'departamento_id' => 'required',
            'meta_votacion' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $candidato = Candidato::find($id);
        $candidato->nombres = $request->nombres;
        $candidato->direccion = $request->direccion;
        $candidato->telefono = $request->telefono;
        $candidato->partido_id = $request->partido_id;
        $candidato->corporacione_id = $request->corporacione_id;
        $candidato->departamento_id = $request->departamento_id;
        $candidato->municipio_id = $request->municipio_id;
        $candidato->meta_votacion = $request->meta_votacion;

        $candidato->save();

        $new_candidato = DB::select("SELECT c.*, cor.corporacion, p.partido, p.logo, d.departamento, m.municipio FROM candidatos as c
            INNER JOIN corporaciones as cor
            ON c.corporacione_id = cor.id
            INNER JOIN partidos as p
            ON c.partido_id = p.id
            INNER JOIN departamentos as d
            ON c.departamento_id = d.id
            LEFT JOIN municipios as m
            ON c.departamento_id = m.departamento_id AND c.municipio_id = m.id
            WHERE c.id = $id");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'candidato' => $new_candidato
        );
        return response()->json($data, $data['code']);
    }

    public function votantes($corporacion, $candidato){

        $condicion = "";
        if($corporacion == 5){
            $condicion = "AND c.id = $candidato";
        }

        $votantes = DB::select("SELECT c.nombres, c.meta_votacion, COUNT(*) AS votantes 
        FROM candidatos as c 
                INNER JOIN listadovotantes as lv ON  c.id = lv.candidato_id        
                WHERE lv.observacione_id = 1 $condicion         
                GROUP BY c.nombres, c.meta_votacion");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'votantes' => $votantes
        );

        return response()->json($data, $data['code']);
    }

    public function miCandidato(){
        
        $candidato_id = Auth::user()->candidato_id;
        
        if(!$candidato_id){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No tienes un candidato asociado'
            );
            return response()->json($data, $data['code']);
        }

        $candidato = DB::select("SELECT c.*, cor.corporacion, p.partido, p.logo, d.departamento, m.municipio FROM candidatos as c
            INNER JOIN corporaciones as cor
            ON c.corporacione_id = cor.id
            INNER JOIN partidos as p
            ON c.partido_id = p.id
            INNER JOIN departamentos as d
            ON c.departamento_id = d.id
            LEFT JOIN municipios as m
            ON c.departamento_id = m.departamento_id AND c.municipio_id = m.id
            WHERE c.id = ?", [$candidato_id]);

        if(empty($candidato)){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Candidato no encontrado'
            );
            return response()->json($data, $data['code']);
        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'candidato' => $candidato[0]
        );

        return response()->json($data, $data['code']);
    }
}
