<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Profesione;

class ProfesioneController extends Controller
{
    public function index(){
        $profesiones = Profesione::all();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'profesiones' => $profesiones
        );
        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'profesion' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $profesion = new Profesione();
        $profesion->profesion = $request->profesion;
        $profesion->save();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'profesion' => $profesion
        );
        return response()->json($data, $data['code']);
    }
}
