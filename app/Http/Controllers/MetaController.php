<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Meta;

class MetaController extends Controller
{
    public function metasVotantes(Request $request){

        $dpto = $request->dpto;
        $candidato = $request->candidato;        

        $metas = DB::select("SELECT c.id, p.partido, c.nombres, c.corporacione_id, cor.corporacion, c.meta_votacion
        FROM candidatos as c 
        INNER JOIN partidos as p ON c.partido_id = p.id
        INNER JOIN corporaciones as cor ON c.corporacione_id = cor.id
        WHERE c.id = $candidato");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'metas' => $metas
        );
        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        if(count($request->comunas) == 0){
            $data = [
                'comunas' => 'Hay campos vacios'
            ];
            return response()->json($data, 200);
        }

        for ($i=0; $i < count($request->comunas); $i++) { 
            $meta = new Meta();
            $meta->comuna = $request->comunas[$i]['comuna'];
            $meta->candidato_id = Auth::user()->candidato;
            $meta->meta = $request->comunas[$i]['meta'];

            $meta->save();
        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'metas' => $request->comunas
        );

        return response()->json($data, $data['code']);
    }

   

    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'comuna' => 'required',
            'meta' => 'required'
        ]);   

        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }
        
        $meta = Meta::find($id);
        $meta->comuna = $request->comuna;
        $meta->meta = $request->meta;

        $meta->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'metas' => $meta
        );

        return response()->json($data, $data['code']);
    }
}
