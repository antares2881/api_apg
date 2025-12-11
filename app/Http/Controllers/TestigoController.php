<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Testigo;
use App\Models\Divipoletestigo;

class TestigoController extends Controller
{
    public function destroy($id){
        $testigo = Testigo::find($id)->delete();
        return 'ok';
    }
    public function index(){
        $testigos = Testigo::with('mesas')->where('candidato_id', Auth::user()->candidato_id)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'testigos' => $testigos
        );
        return response()->json($data, $data['code']);
    }

    public function store(Request $request){

        $validator = \Validator::make($request->all(), [
            'cedula' => 'required',
            'nom1' => 'required',
            'ape1' => 'required',
            'partido_id' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $testigo = new testigo();
        $testigo->divipoletestigo_id = $request->divipoletestigo_id;
        $testigo->candidato_id = Auth::user()->candidato_id;
        $testigo->partido_id = $request->partido_id;
        $testigo->cedula = $request->cedula;
        $testigo->nom1 = $request->nom1;
        $testigo->nom2 = $request->nom2;
        $testigo->ape1 = $request->ape1;
        $testigo->ape2 = $request->ape2;
        $testigo->email = $request->email;
        $testigo->telefono = $request->telefono;
        $testigo->user_id = Auth::user()->id;
        
        $testigo->save();

        $mesa = Testigo::find($request->id);

        $data = array(
            'status' => 'success',
            'code' => 200,
            'testigo' => $testigo
        );
        
        return response()->json($data, $data['code']);
    }

    public function testigos_puesto(Request $request){
        $zona = $request->zona;
        $puesto = $request->puesto;

        $testigos = Divipoletestigo::with('testigo')->where([
            'cod_zona' => $zona,
            'cod_puesto' => $puesto
        ])->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'testigos' => $testigos
        );

        return response()->json($data, $data['code']);
    }

    public function testigosxmcpio($dpto, $mcpio){ //Funcion sin probar
        $testigos = Testigo::with('mesas')
            ->where([
            ['departamento_id', $dpto],
            ['municipio_id', $mcpio],
        ])->get();
        return $testigos;
    }

    public function testigosxdpto(){  //Funcion sin probar
        $testigos = DB::select('SELECT departamento_id, departamento,  municipio, municipio_id,  COUNT(municipio_id) as total FROM testigos GROUP BY departamento_id, departamento, municipio, municipio_id ORDER BY departamento_id asc');
        return $testigos;
    }
    
    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'nom1' => 'required',
            'ape1' => 'required',
            'celular' => 'required',
            'departamento_id' => 'required',
            'municipio_id' => 'required',
            'zona' => 'required',
            'puesto' => 'required',
            'codigo_interno' => 'required',
        ]);
            
        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }
        
        $testigo = testigo::find($id);
        $testigo->nom1 = $request->nom1;
        $testigo->nom2 = $request->nom2;
        $testigo->ape1 = $request->ape1;
        $testigo->ape2 = $request->ape2;
        $testigo->celular = $request->celular;
        $testigo->departamento_id = $request->departamento_id;
        $testigo->departamento = $request->departamento;
        $testigo->municipio_id = $request->municipio_id;
        $testigo->municipio = $request->municipio;
        $testigo->zona = $request->zona;
        $testigo->puesto = $request->puesto;
        $testigo->codigo_interno = $request->codigo_interno;
        $testigo->user_id = Auth::user()->id;
        
        $testigo->save();

        $delete_mesas = Testigosmesa::where('testigo_id', $id)->delete();
        $mesa = Testigo::find($id);
        $mesa->mesas()->create(['mesa' => $request->mesa]);

        $data = array(
            'status' => 'success',
            'code' => 200,
            'testigo' => $testigo
        );
        return response()->json($data, $data['code']);
    }
}
