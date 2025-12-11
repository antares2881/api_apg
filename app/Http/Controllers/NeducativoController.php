<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Neducativo;

class NeducativoController extends Controller
{
    public function index(){
        $neducativos = Neducativo::all();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'niveles' => $neducativos
        );
        return response()->json($data, $data['code']);
    }
}
