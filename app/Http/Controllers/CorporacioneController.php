<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Corporacione;

class CorporacioneController extends Controller
{
    public function index(){
        $corporaciones = Corporacione::where('eleccione_id', 4)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'corporaciones' => $corporaciones
        );
        return response()->json($data, $data['code']);
    }
}
