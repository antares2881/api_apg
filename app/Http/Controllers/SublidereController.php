<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sublidere;

class SublidereController extends Controller
{
    public function getSublideres($lidere_id){
        $sublideres = Sublidere::where('lidere_id', $lidere_id)->get();
        $data = array(
            'code' => 200,
            'status' => 'success',
            'sublideres' => $sublideres
        );
        return response()->json($data, $data['code']);
    }
}
