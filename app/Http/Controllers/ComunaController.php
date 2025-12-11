<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComunaController extends Controller
{
    public function index($dpto, $mcpio){
        $comunas = DB::select("SELECT d.comuna FROM divipoles AS d WHERE departamento_id = $dpto AND municipio_id = $mcpio GROUP BY d.comuna ");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'comunas' => $comunas
        );
        return response()->json($data, $data['code']);
    }
}
