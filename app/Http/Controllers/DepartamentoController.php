<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;

class DepartamentoController extends Controller
{
    public function index(){
        $departamentos = Departamento::where('id', '<>', 88)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'departamentos' => $departamentos
        );
        return response()->json($data, $data['code']);
    }
}
