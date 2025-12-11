<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipoempleado;

class TipoempleadoController extends Controller
{
    public function index(){
        $tipoempleados = Tipoempleado::all();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'tipos' => $tipoempleados
        );
        return response()->json($data, $data['code']);
    }
}
