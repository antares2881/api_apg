<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\Fill;

use App\Models\Lidere;
use App\Models\Listadovotante;
use App\Models\Sublidere;

class LidereController extends Controller
{   
    public function destroy($id){

        $candidato = Auth::user()->candidato_id;
        $lider = Lidere::where([
            'candidato_id' => $candidato,
            'id' => $id
        ])->delete();

        $votantes = Listadovotante::where([
            'lidere_id' => $id,
            'candidato_id' => $candidato,
        ])->delete();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'lider' => $lider,
            'votantes' => $votantes
        );

        return response()->json($data, $data['code']);
    }

    public function excel_lideres($token_id){
        $token = DB::select("SELECT oat.id, u.candidato_id, c.corporacione_id
            FROM oauth_access_tokens as oat 
            INNER JOIN users as u
            ON oat.user_id = u.id
            INNER JOIN candidatos as c
            ON u.candidato_id = c.id
            WHERE oat.revoked = 0 AND oat.id = '". $token_id. " '  "
        );
        if(count($token) > 0){
            $candidato = $token[0]->candidato_id;  
           
            $lideres = DB::select("SELECT CONCAT(c.nombres,' ',c.apellidos) as coordinador, l.id, CONCAT(l.nombres,' ',l.apellidos) as lider, l.direccion, l.telefono, l.correo FROM lideres as l 
            INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
            WHERE l.candidato_id = $candidato");            

            $spreadsheet = IOFactory::load('plantillas/lideres.xlsx');
            $sheet = $spreadsheet->getSheetByName('GENERAL');
            $celda = 2;

            for ($i=0; $i < count($lideres) ; $i++) { 
                $sheet->setCellValue("A$celda", $lideres[$i]->coordinador);                
                $sheet->setCellValue("B$celda", $lideres[$i]->id);
                $sheet->setCellValue("C$celda", $lideres[$i]->lider);
                $sheet->setCellValue("D$celda", $lideres[$i]->direccion);   
                $sheet->setCellValue("E$celda", $lideres[$i]->telefono);
                $sheet->setCellValue("F$celda", $lideres[$i]->correo);
                $celda ++;
            }

            $filename = 'Excel_lideres'.time().'.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }
    }

    public function getLideresReportes($coordinador, $candidato){

        
        if($coordinador == 'undefined' || $coordinador == -1 ){
            $condicion_coordinador = "";
        }else{
            $condicion_coordinador = "AND l.coordinadore_id = $coordinador";
        }

        $lideres = DB::select("SELECT l.*, c.nombres as nombres_coordi, c.apellidos as ape_coordi FROM lideres as l 
            INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
            WHERE l.candidato_id = $candidato $condicion_coordinador "
        );   

        $data = array(
            'status' => 'success',
            'code' => 200,
            'lideres' => $lideres
        );

        return response()->json($data, $data['code']);
    }

    public function getLideresVotantes($id){

        $candidato = Auth::user()->candidato_id;
        $lider_votantes = DB::select("SELECT ca.nombres, c.nombres as nom_coor, c.apellidos as ape_coor, l.nombres as nom_lid, l.apellidos as ape_lid, lv.id as cedula_votante, lv.nombres as nom_vot, lv.apellidos as ape_vot
            FROM lideres as l 
            LEFT JOIN listadovotantes as lv ON l.id = lv.lidere_id
            LEFT JOIN coordinadores as c ON l.coordinadore_id = c.id
            INNER JOIN candidatos as ca ON l.candidato_id = ca.id
            WHERE l.id = $id AND l.candidato_id = $candidato");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'lider_votantes' => $lider_votantes
        );

        return response()->json($data, $data['code']);
    }

    public function getSubLideres($id, $candidato){
        $sublideres = DB::select("SELECT l.id as lidere_id, l.nombres as nombres_lider, l.apellidos as apellidos_lider, sl.id, sl.candidato_id, sl.nombres, sl.apellidos, sl.fecha_nac, sl.meta_votantes, sl.telefono, sl.direccion, sl.profesione_id, (SELECT COUNT(*) FROM listadovotantes as lv WHERE lv.sublidere_id = sl.id) as total_militantes
            FROM sublideres as sl 
            INNER JOIN lideres as l ON sl.lidere_id = l.id
            WHERE l.id = $id AND l.candidato_id = $candidato");

        $lider = DB::select("SELECT l.id as lidere_id, l.nombres as nombres_lider, l.apellidos as apellidos_lider, l.id, l.candidato_id, l.nombres, l.apellidos, l.fecha_nac, l.meta_votantes, l.telefono, l.direccion, l.profesione_id, (SELECT COUNT(*) FROM listadovotantes as lv WHERE lv.lidere_id = l.id AND lv.sublidere_id = 0) as total_militantes
            FROM lideres as l 
            WHERE l.id = $id AND l.candidato_id = $candidato");
       
        $data = array(
            'code' => 200,
            'status' => 'success',
            'sublideres' => $sublideres,
            'lider' => $lider
        );
        return response()->json($data, $data['code']);
    }

    public function index($candidato){

        $condicion = "";

        if(Auth::user()->role_id == 5){
            $id = Auth::user()->id;
            $condicion = "AND l.id = $id ";
        }

        if(Auth::user()->role_id == 6){
            $id = Auth::user()->id;
            $condicion = "AND l.coordinadore_id = $id ";
        }

        // $lideres = Lidere::with('coordinador')->get();    
        $lideres = DB::select("SELECT l.*, c.nombres as nom_jefe, c.apellidos as ape_jefe FROM lideres as l 
            INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
            WHERE l.candidato_id = $candidato $condicion "
        );   

        $data = array(
            'status' => 'success',
            'code' => 200,
            'lideres' => $lideres
        );

        return response()->json($data, $data['code']);
        
    }

    public function numMilitantes($id, $candidato, $tipo){

        $variable = ($tipo == 1) ? 'lidere_id' : 'sublidere_id';
        $militantes = DB::select("SELECT COUNT(*) as total_militantes FROM listadovotantes WHERE $variable = ? AND candidato_id = ?", [$id, $candidato]);
        
        $data = array(
            "status" => 'success',
            "code" => 200,
            "numero_militantes" => $militantes
        );
        return response()->json($data, $data['code']);
    }

    public function show($id){
        if(empty($id)){
            $data = array(
                'status' => 'error',
                'code' => 200,
                'message' => 'El numero de id no es valido'
            );
            return response()->json($data, $data['code']);
        }

        $lider = Lidere::with('candidato')->find($id);
        $response = array(
            'status' => 'success',
            'code' => 200,
            'lider' => $lider
        ); 
        return response()->json($response, $response['code']);
    }

    public function store(Request $request){

        //Validar si un lider esta creado
        $ya_ingresado = Lidere::where([
            'id' => $request->id,
            'candidato_id' => $request->candidato_id
        ])->get();

        if(count($ya_ingresado) > 0){
            $data = array(
                'status' => 'fail',
                "code" => 200,
                'error' => "Este lider ya esta creado para este candidato."
            );
            return response()->json($data, $data['code']);
        }

        //Validar si un sublider esta creado
        $sub_ya_ingresado = Sublidere::where([
            'id' => $request->id,
            'candidato_id' => $request->candidato_id
        ])->get();

        if(count($sub_ya_ingresado) > 0){
            $data = array(
                'status' => 'fail',
                "code" => 200,
                'error' => "Este Sublider ya esta creado para este candidato."
            );
            return response()->json($data, $data['code']);
        }
        
        $validator = \Validator::make($request->all(), [
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'fecha_nac' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'meta_votantes' => 'required|numeric',
            'profesione_id' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $cumple = explode("-", $request->fecha_nac);

        $lider = ($request->esSublider == 1) ? new Lidere() : new Sublidere();
        $lider->id = $request->id;
        $lider->candidato_id = $request->candidato_id;
        $lider->nombres = $request->nombres;
        $lider->apellidos = $request->apellidos;
        $lider->fecha_nac = $request->fecha_nac;
        $lider->mes_nac = $cumple[1];
        $lider->dia_nac = $cumple[2];

        if($request->esSublider == 1){
            $lider->coordinadore_id = $request->coordinadore_id;
        }else{
            $lider->lidere_id = $request->lidere_id;
        }
        
        $lider->direccion = $request->direccion;
        $lider->telefono = $request->telefono;
        $lider->profesione_id = $request->profesione_id;
        $lider->meta_votantes = $request->meta_votantes;

        $lider->save();
        $id = $request->id;
        
        //se agrega el lider como votante
        $votante = new Listadovotante();
        $votante->id = $id;

        //valida si es un sublider
        if($request->esSublider == 1){
            $votante->lidere_id = $id;
            $votante->sublidere_id = 0;
        }else{
            $votante->lidere_id = $request->lidere_id;
            $votante->sublidere_id = $id;
        }

        $votante->observacione_id = 1;
        $votante->candidato_id = $request->candidato_id;
        $votante->comuna = $request->comuna;
        $votante->nombres = $request->nombres;
        $votante->apellidos = $request->apellidos;
        $votante->estado = $request->estado;
        $votante->fecha_nac = $request->fecha_nac;
        $votante->edad = $request->edad;
        $votante->direccion = $request->direccion;
        $votante->telefono = $request->telefono;
        $votante->departamento_id = $request->departamento_id;
        $votante->municipio_id = $request->municipio_id;
        $votante->zona = $request->zona;
        $votante->puesto = $request->puesto;
        $votante->nombre_puesto = $request->nombre_puesto;
        $votante->mesa = $request->mesa;
        $votante->user_registra = Auth::user()->id;

        $votante->save();
        
        if($request->esSublider == 1){
            $lideres = DB::select("SELECT l.*, c.nombres as nom_jefe, c.apellidos as ape_jefe, COUNT(s.id) as numero_sublideres FROM lideres as l 
                INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                LEFT JOIN sublideres as s ON l.id = s.lidere_id
                WHERE l.id = $id "
            );  
        }else{
            $lideres = DB::select("SELECT sl.*, l.nombres as nom_jefe, l.apellidos as ape_jefe FROM sublideres as sl 
                INNER JOIN lideres as l ON sl.lidere_id = l.id
                WHERE sl.id = $id "
            );  
        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'lideres' => $lideres
        );

        return response()->json($data, $data['code']);
    }

    public function update(Request $request, $id){
        
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'fecha_nac' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'meta_votantes' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $cumple = explode("-", $request->fecha_nac);
        //$lider = Lidere::find($id);
        $lider = ($request->esSublider == 1) ? Lidere::find($id) : Sublidere::find($id);
        $lider->candidato_id = $request->candidato_id;
        $lider->nombres = $request->nombres;
        $lider->apellidos = $request->apellidos;
        $lider->fecha_nac = $request->fecha_nac;
        $lider->mes_nac = $cumple[1];
        $lider->dia_nac = $cumple[2];
        //$lider->coordinadore_id = $request->coordinadore_id;
        $lider->direccion = $request->direccion;
        $lider->telefono = $request->telefono;
        $lider->profesione_id = $request->profesione_id;
        $lider->meta_votantes = $request->meta_votantes;

        $lider->save();

        if($request->esSublider == 1){
            
            $lideres = DB::select("SELECT l.*, c.nombres as nom_jefe, c.apellidos as ape_jefe, COUNT(s.id) as numero_sublideres
                FROM lideres as l 
                INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                LEFT JOIN sublideres as s ON l.id = s.lidere_id
                WHERE l.id = $id "
            );  
        }else{
            $lideres = DB::select("SELECT sl.*, l.nombres as nom_jefe, l.apellidos as ape_jefe FROM sublideres as sl 
                INNER JOIN lideres as l ON sl.lidere_id = l.id
                WHERE sl.id = $id "
            );  
        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'lideres' => $lideres
        );

        return response()->json($data, $data['code']);
    }
}
