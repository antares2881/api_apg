<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barrio;

class BarrioController extends Controller
{
    public function index(){
        $barrios = Barrio::all();
        $data = array(
            'code' => 200,
            'status' => 'success',
            'barrios' => $barrios
        );
        return response()->json($data, $data['code']);
    }
}
