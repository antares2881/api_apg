<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Divipolepreconteo;
use App\Models\Preconteo;

class DivipolepreconteoController extends Controller
{
    public function mostrar_mesas(Request $request){

        $dpto = $request->dpto;
        $mcpio = $request->mcpio;
        $zona = $request->zona;
        $puesto = $request->puesto;

        /* $mesas = DB::select("SELECT dp.*, p.*
        FROM divipolepreconteos AS dp 
        LEFT JOIN preconteos as p ON dp.id = p.divipolepreconteo_id
        WHERE dp.cod_dpto = $dpto AND dp.cod_mcpio = $mcpio AND dp.cod_zona = '".$zona."' AND dp.cod_puesto = '".$puesto."' "); */

        $mesas = Divipolepreconteo::with('preconteo')
        ->where([
            'cod_dpto' => $dpto,
            'cod_mcpio' => $mcpio,
            'cod_zona' => $zona,
            'cod_puesto' => $puesto,
        ])->get();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'mesas' => $mesas
        );
        return response()->json($data, $data['code']);

    }

    public function puestos_divipoles($dpto, $mcpio){

        $puestos = DB::select("SELECT dp.mcpio, dp.cod_zona, dp.cod_puesto, dp.puesto, COUNT(dp.puesto) as total_mesas
        FROM divipolepreconteos as dp 
        WHERE dp.cod_dpto = $dpto AND dp.cod_mcpio = $mcpio
        GROUP BY dp.mcpio, dp.cod_zona, dp.cod_puesto, dp.puesto");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'puestos' => $puestos
        );

        return response()->json($data, $data['code']);
    }
}
