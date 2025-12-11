<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Jurado;

class JuradoController extends Controller
{
    public function agrupados(){

        $candidato = Auth::user()->candidato_id;
        $jurados = DB::select("SELECT m.municipio, j.nombre_puesto, COUNT(j.nombre_puesto) as total, j.zona, j.puesto
            FROM jurados as j
            INNER JOIN municipios as m ON j.departamento_id = m.departamento_id AND j.municipio_id = m.id
            INNER JOIN departamentos as d ON j.departamento_id = d.id
            WHERE j.candidato_id = $candidato
            GROUP BY m.municipio, j.nombre_puesto, j.zona, j.puesto
            ORDER BY m.municipio, zona ASC, puesto ASC");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'jurados' => $jurados
        );

        return response()->json($data, $data['code']);
    }

    public function index(){
        $jurados = Jurado::where('candidato_id', 2)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'jurados' => $jurados
        );
        return response()->json($data, $data['code']);
    }

    public function show($cedula){

        $jurado = Jurado::find($cedula);
        $data = array(
            'status' => 'success',
            'code' => 200,
            'jurado' => $jurado
        );
        return response()->json($data, $data['code']);

    }

    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
            'nom1' => 'required',
            'ape1' => 'required',
            'direccion' => 'required',
            'celular' => 'required|min:10',
            'correo' => 'required|unique:jurados|email|string|max:255',
            'neducativo_id' => 'required',
            'filiacionpolitica_id' => 'required',
            'tipoempleado_id' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $jurado = new Jurado();
        $jurado->id = $request->id;
        $jurado->nom1 = $request->nom1;
        $jurado->nom2 = $request->nom2;
        $jurado->ape1 = $request->ape1;
        $jurado->ape2 = $request->ape2;
        $jurado->direccion = $request->direccion;
        $jurado->telefono = $request->telefono;
        $jurado->celular = $request->celular;
        $jurado->correo = $request->correo;
        $jurado->departamento_id = $request->dpto;
        $jurado->municipio_id = $request->mcpio;
        $jurado->neducativo_id = $request->neducativo_id;
        $jurado->filiacionpolitica_id = $request->filiacionpolitica_id;
        $jurado->tipoempleado_id = $request->tipoempleado_id;
        $jurado->zona = $request->zona;
        $jurado->puesto = $request->puesto;
        $jurado->nombre_puesto = $request->nombre_puesto;
        $jurado->candidato_id = Auth::user()->candidato_id;
        $jurado->user_id = Auth::user()->id;

        $jurado->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'jurado' => $jurado
        );

        return response()->json($data, $data['code']);
    }

    public function update(Request $request, $id){

        $validator = \Validator::make($request->all(), [
            'id' => 'required',
            'nom1' => 'required',
            'ape1' => 'required',
            'direccion' => 'required',
            'celular' => 'required|min:10',
            'correo' => 'required|email|string|max:255',
            'neducativo_id' => 'required',
            'filiacionpolitica_id' => 'required',
            'tipoempleado_id' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $jurado = Jurado::find($id);

        $jurado->nom1 = $request->nom1;
        $jurado->nom2 = $request->nom2;
        $jurado->ape1 = $request->ape1;
        $jurado->ape2 = $request->ape2;
        $jurado->direccion = $request->direccion;
        $jurado->telefono = $request->telefono;
        $jurado->celular = $request->celular;
        $jurado->correo = $request->correo;
        $jurado->neducativo_id = $request->neducativo_id;
        $jurado->filiacionpolitica_id = $request->filiacionpolitica_id;
        $jurado->tipoempleado_id = $request->tipoempleado_id;
        $jurado->zona = $request->zona;
        $jurado->puesto = $request->puesto;
        $jurado->nombre_puesto = $request->nombre_puesto;
        $jurado->candidato_id = Auth::user()->candidato_id;
        $jurado->user_id = Auth::user()->id;

        $jurado->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'jurado' => $jurado
        );

        return response()->json($data, $data['code']);
    }
    
}
