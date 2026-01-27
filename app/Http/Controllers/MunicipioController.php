<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Municipio;

class MunicipioController extends Controller
{
    public function show($id){
        $municipios = Municipio::where('departamento_id', $id)->get();
        $data = array(
            'code' => 200,
            'status' => 'success',
            'municipios' => $municipios
        );
        return response()->json($data, $data['code']);
    }

    public function get_municipio($departamento_id, $municipio_id){
        $municipio = Municipio::where('departamento_id', $departamento_id)
                              ->where('id', $municipio_id)
                              ->first();
        if(!is_null($municipio)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'municipio' => $municipio
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Municipio no encontrado'
            );
        }
        return response()->json($data, $data['code']);
    }
}
