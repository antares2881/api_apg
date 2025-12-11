<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Personal;

class PersonalController extends Controller
{
    public function index(Request $request){
        $personals = Personal::with('cargo')->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'personal' => $personals
        );
        return response()->json($data, $data['code']);
    }

    public function personalXCargo($cargo_id){
        $personal = Personal::where('cargo_id', $cargo_id)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'personal' => $personal
        );
        return response()->json($data, $data['code']);
    }
    
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'id' => 'required|unique:personals',
            'nombres' => 'required',
            'apellidos' => 'required',
            'direccion' => 'required',
            'celular' => 'required',
            'correo' => 'required|unique:personals',
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $personal = new Personal();
        $personal->id = $request->id;
        $personal->cargo_id = $request->cargo_id;
        $personal->candidato_id = Auth::user()->candidato_id;
        $personal->user_id = Auth::user()->id;
        $personal->nombres = $request->nombres;
        $personal->apellidos = $request->apellidos;
        $personal->direccion = $request->direccion;
        $personal->celular = $request->celular;
        $personal->correo = $request->correo;
        
        $personal->save(); 
        $data = array(
            'status' => 'success',
            'code' => 200,
            'personal' => $personal
        );
        return response()->json($data, $data['code']);
    }

    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'nombres' => 'required',
            'apellidos' => 'required',
            'direccion' => 'required',
            'celular' => 'required',
            'correo' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $personal = Personal::find($id);
        $personal->cargo_id = $request->cargo_id;
        $personal->user_id = Auth::user()->id;
        $personal->nombres = $request->nombres;
        $personal->apellidos = $request->apellidos;
        $personal->direccion = $request->direccion;
        $personal->celular = $request->celular;
        $personal->correo = $request->correo;
        $personal->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'personal' => $personal
        );

        return response()->json($data, $data['code']);
    }
}
