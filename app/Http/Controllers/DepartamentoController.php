<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;

class DepartamentoController extends Controller
{
    public function index(){
        $departamentos = Departamento::all();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'departamentos' => $departamentos
        );
        return response()->json($data, $data['code']);
    }

    public function show($id){
        $departamento = Departamento::find($id);
        if(!is_null($departamento)){
            $data = array(
                'status' => 'success',
                'code' => 200,
                'departamento' => $departamento
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Departamento no encontrado'
            );
        }
        return response()->json($data, $data['code']);
    }
}
