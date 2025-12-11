<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Models\Coordinadore;
use App\Models\Lidere;
use App\Models\Turno;

class AgendaController extends Controller
{
    public function delete($id){
        $item = Agenda::find($id)->delete();
        return 'ok';
    }

    public function fechaCumple($fecha){
        $cumple = explode('-', $fecha);
        // return $cumple;
        $coordinadores = Coordinadore::where([
            ['mes_nac', $cumple[1]],
            ['dia_nac', $cumple[2]],
        ])->get();
        $lideres = Lidere::where([
            ['mes_nac', $cumple[1]],
            ['dia_nac', $cumple[2]],
        ])->get();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'coordinadores' => $coordinadores,
            'lideres' => $lideres,
        );

        return response()->json($data, $data['code']);
    }

    public function show($fecha){
        $agendas = Agenda::with('candidato')->where('fecha', $fecha)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'agendas' => $agendas
        );
        return response()->json($data, $data['code']);
    }

    public function store(Request $request){

        $validator = \Validator::make($request->all(), [
            'lugar' => 'required',
            'persona' => 'required',
            'fecha' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200); 
        }

        $agenda = new Agenda();
        $agenda->candidato_id = Auth::user()->candidato_id;
        $agenda->fecha = $request->fecha;
        $agenda->lugar = $request->lugar;
        $agenda->persona = $request->persona;
        $agenda->observacion = $request->observacion;
        $agenda->user_id = Auth::user()->id;

        $agenda->save();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'agenda' => $agenda
        );
        return response()->json($data, $data['code']);

    }

    public function update(Request $request, $id){

        $validator = \Validator::make($request->all(), [
            'lugar' => 'required',
            'persona' => 'required',
            'fecha' => 'required'
        ]); 

        if($validator->fails()){
            return response()->json($validator->messages(), 200); 
        }

        $agenda = Agenda::find($id);
        $agenda->lugar = $request->lugar;
        $agenda->fecha = $request->fecha;
        $agenda->persona = $request->persona;
        $agenda->observacion = $request->observacion;
        $agenda->user_id = Auth::user()->id;

        $agenda->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'agenda' => $agenda
        );
        return response()->json($data, $data['code']);

    }
}
