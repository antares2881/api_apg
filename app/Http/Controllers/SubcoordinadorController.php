<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subcoordinadore;

class SubcoordinadorController extends Controller
{
    public function index($candidato){        
        
        $subcoordinadores = Subcoordinadore::where('candidato_id', $candidato)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'subcoordinadores' => $subcoordinadores
        );
        return response()->json($data, $data['code']);
    }

    public function store(Request $request){

        $validator = \Validator::make($request->all(), [
            'id' => 'required|unique:subcoordinadores',
            'nombres' => 'required',
            'apellidos' => 'required',
            'fecha_nac' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'correo' => 'required|unique:subcoordinadores|email|string|max:255',
            'empleado' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $cumple = explode("-", $request->fecha_nac);

        /* $usuario = new User();
        $usuario->role_id = 3;         
        $usuario->name = $request->nombres.' '.$request->apellidos;
        $usuario->email = $request->correo;
        $usuario->password = Hash::make($request->cedula);
        $usuario->save(); */

        $subcoordinador = new Subcoordinadore();
        $subcoordinador->id = $request->id;
        $subcoordinador->nombres = $request->nombres;
        $subcoordinador->apellidos = $request->apellidos;
        $subcoordinador->fecha_nac = $request->fecha_nac;
        $subcoordinador->mes_nac = $cumple[1];
        $subcoordinador->dia_nac = $cumple[2];
        $subcoordinador->direccion = $request->direccion;
        $subcoordinador->barrio = $request->barrio;
        $subcoordinador->telefono = $request->telefono;
        $subcoordinador->correo = $request->correo;
        $subcoordinador->observaciones = $request->observaciones;
        $subcoordinador->empleado = $request->empleado;
        $subcoordinador->perfil = $request->perfil;
        $subcoordinador->candidato_id = $request->candidato_id;
        $subcoordinador->user_id = 1;

        $subcoordinador->save();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'subcoordinador' => $subcoordinador
        );
        return response()->json($data, $data['code']);

    }

    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'correo' => 'required|email|string|max:255',
            'empleado' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $cumple = explode("-", $request->fecha_nac);
        $subcoordinador = Subcoordinadore::find($id);
        $subcoordinador->nombres = $request->nombres;
        $subcoordinador->apellidos = $request->apellidos;
        $subcoordinador->fecha_nac = $request->fecha_nac;
        $subcoordinador->mes_nac = $cumple[1];
        $subcoordinador->dia_nac = $cumple[2];
        $subcoordinador->direccion = $request->direccion;
        $subcoordinador->barrio = $request->barrio;
        $subcoordinador->telefono = $request->telefono;
        $subcoordinador->correo = $request->correo;
        $subcoordinador->observaciones = $request->observaciones;
        $subcoordinador->empleado = $request->empleado;
        $subcoordinador->perfil = $request->perfil;

        $subcoordinador->save();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'subcoordinador' => $subcoordinador
        );
        return response()->json($data, $data['code']);
    }
}
