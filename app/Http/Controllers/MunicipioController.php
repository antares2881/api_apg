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
}
