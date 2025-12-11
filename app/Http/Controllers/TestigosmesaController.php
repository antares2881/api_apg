<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Testigosmesa;

class TestigosmesaController extends Controller
{
    public function store(Request $request){

        $mesa = new Testigosmesa();
        $mesa->testigo_id = $request->id;
        $mesa->mesa = $request->mesa;
        $mesa->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'mesas' => $mesa
        );

        return response()->json($data, $data['code']);
    }
}
