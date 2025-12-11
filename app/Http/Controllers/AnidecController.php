<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnidecController extends Controller
{
    public function show($id){
        // $ced = intval($id);
        if(!is_numeric($id)){
            return response()->json('Solo puede enviar numeros', 200);
        } 
        $datosPersona = DB::table('anidec')
            ->select('cedula', 'nom1', 'nom2', 'ape1', 'ape2')
            ->where('cedula', $id)
            ->get();
 
        $datos_censo = DB::select('call consulta(?)', array($id));  
        
        $datos = [
            'datosPersona' => $datosPersona,
            'lugar' => $datos_censo,
            'status' => 'success',
            'code' => 200
        ];
        // return response()->json($datos, $$datos['code']);
        return $datos;
    }
}
