<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recolectore;

class RecolectoreController extends Controller
{
    public function index($candidato){
        $recolectores = Recolectore::where('candidato_id', $candidato)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'recolectores' => $recolectores
        );
        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'id' => 'required|unique:recolectores',
            'nombres' => 'required',
            'apellidos' => 'required',
            'telefono' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }
        $recolector = new Recolectore();
        $recolector->id = $request->id;
        $recolector->candidato_id = $request->candidato;
        $recolector->nombres = $request->nombres;
        $recolector->apellidos = $request->apellidos;
        $recolector->telefono = $request->telefono;

        $recolector->save();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'recolector' => $recolector
        );
        return response()->json($data, $data['code']);
    }

    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'nombres' => 'required',
            'apellidos' => 'required',
            'telefono' => 'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }
        $recolector = Recolectore::find($id);
        $recolector->nombres = $request->nombres;
        $recolector->apellidos = $request->apellidos;
        $recolector->telefono = $request->telefono;

        $recolector->save();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'recolector' => $recolector
        );
        return response()->json($data, $data['code']);
    }
}
