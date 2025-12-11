<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comando;

class ComandoController extends Controller
{
    public function index(){
        $comandos = Comando::all();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'comandos' => $comandos
        );
        return response()->json($data, $data['code']);
    }

    public function store(Request $request){

        $validator = \Validator::make($request->all(), [
            'nombre' => 'required',
            'proyectados' => 'required',
            'contacto' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $comando = new Comando();
        $comando->nombre = $request->nombre;
        $comando->proyectados = $request->proyectados;
        $comando->contacto = $request->contacto;

        $comando->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'comando' => $comando
        );

        return response()->json($data, $data['code']);

    }

    public function update(Request $request, $id){

        $validator = \Validator::make($request->all(), [
            'nombre' => 'required',
            'proyectados' => 'required',
            'contacto' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }
        
        $comando = Comando::find($id);
        $comando->nombre = $request->nombre;
        $comando->proyectados = $request->proyectados;
        $comando->contacto = $request->contacto;

        $comando->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'comando' => $comando
        );

        return response()->json($data, $data['code']);

    }

}
