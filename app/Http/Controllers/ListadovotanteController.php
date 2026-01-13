<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\Fill;

use App\Models\Ipreporte;
use App\Models\Jurado;
use App\Models\Listadovotante;
use App\Models\Testigo;
use Illuminate\Database\QueryException;

class ListadovotanteController extends Controller
{
    public function index(){
        if(Auth::user()->role_id == 1){
            $listadovotantes = Listadovotante::with('lider')->get();
        }else{
            $listadovotantes = Listadovotante::with('lider')->where('candidato_id', Auth::user()->candidato_id)->get();
        }
        $data = array(
            'status' => 'success',
            'code' => 200,
            'listadovotantes' => $listadovotantes
        );
        return response()->json($data, $data['code']);
    }

    public function repetidos(Request $request){
        // $votante = Listadovotante::with('candidato', 'lider')->where('id', $cedula)->get();
        $cedula = $request->cedula;
        $candidato = $request->candidato;
        $condicion = "";

        if($request->corporacion == 5){
            $condicion = "AND lv.candidato_id = $candidato ";
        }

        $votante = DB::select("SELECT lv.*, c.nombres as nombre_candidato, CONCAT(l.nombres,l.apellidos) as nombre_lider, u.name, d.departamento as desc_dpto, m.municipio as desc_mcpio
            FROM listadovotantes as lv 
            INNER JOIN departamentos as d ON lv.departamento_id = d.id
            INNER JOIN municipios as m ON lv.departamento_id = m.departamento_id AND lv.municipio_id = m.id
            INNER JOIN candidatos as c ON lv.candidato_id = c.id
            INNER JOIN lideres as l ON lv.lidere_id = l.id
            INNER JOIN users as u ON lv.user_registra = u.id
            WHERE lv.id = $cedula $condicion");        

        $data = array(
            'status' => 'success',
            'code' => 200,
            // 'num_registros' => count($votante),
            'votante' => $votante
        );

        return response()->json($data, $data['code']);

    }

    public function store(request $request){
        $validator = \Validator::make($request->all(), [
            'nombres' => 'required',
            'apellidos' => 'required',
            'fecha_nac' => 'required',
            'departamento_id' => 'required',
            'municipio_id' => 'required',
            'lidere_id' => 'required',
            'zona' => 'required',
            'puesto' => 'required',
            'mesa' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $repetido = Listadovotante::where('id', $request->cedula)->get();

        $votante = new Listadovotante();
        $votante->id = $request->cedula;
        $votante->lidere_id = $request->lidere_id;

        //valida si tiene sublider el militante
        $votante->sublidere_id = ($request->sublidere_id) ? $request->sublidere_id : 0;
        
        if(count($repetido) > 0){
            $votante->observacione_id = 4;

        }else{
            $votante->observacione_id = $request->observacione_id;
        }

        $votante->candidato_id = Auth::user()->candidato_id;    
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

        try{
            $votante->save();
            $data = array(
                'status' => 'success',
                'code' => 200,
                'votante' => $votante
            );
            return response()->json($data, $data['code']);
        }catch(QueryException $e){
            $errorCode = isset($e->errorInfo[1]) ? $e->errorInfo[1] : null;
            if($errorCode == 1062){
                $data = array(
                    'status' => 'fail',
                    'code' => 200,
                    'message' => 'Este registro ya fue ingresado por este lider en la base de datos.'
                );
                return response()->json($data, $data['code']);
            }
            $data = array(
                'status' => 'error',
                'code' => 500,
                'message' => 'Error al guardar el registro.',
                'details' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }
    }

    public function update(request $request, $id){

        $validator = \Validator::make($request->all(), [
            'nombres' => 'required',
            'apellidos' => 'required',
            'fecha_nac' => 'required',
            'departamento_id' => 'required',
            'municipio_id' => 'required',
            'zona' => 'required',
            'puesto' => 'required',
            'mesa' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $cedula = $request->id;
        $corporacion = $id;

        //Elimina todos los votantes que esten ingresados con la misma cedula
        if($corporacion == 5){
            $votantes_old = Listadovotante::where([
                'id' => $cedula,
                'candidato_id' => Auth::user()->candidato_id
            ])->delete();
        }else{
            $votantes_old = Listadovotante::find($cedula)->delete();
        }

        $votante = new Listadovotante();
        $votante->id = $cedula;
        $votante->lidere_id = $request->lidere_id;
        $votante->sublidere_id = ($request->sublidere_id) ? $request->sublidere_id : 0;
        $votante->observacione_id = 1;
        $votante->candidato_id = Auth::user()->candidato_id;    
        $votante->comuna = $request->comuna;
        $votante->nombres = $request->nombres;
        $votante->apellidos = $request->apellidos;
        $votante->estado = $request->estado;
        $votante->fecha_nac = $request->fecha_nac;
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
        $data = array(
            'status' => 'success',
            'code' => 200,
            'votante' => $votante
        );
        return response()->json($data, $data['code']);
    }

    public function delete($id){
        if(Auth::user()->role_id == 2){
            $votante = Listadovotante::find($id)->delete();
            $data = array(
                'status' => 'success',
                'code' => 200,
                'messages' => 'El registro fue eliminado'
            );
            return response()->json($data, $data['code']);
        }
        $data = array(
            'status' => 'denegado',
            'code' => 200,
            'message' => 'No tienes autorización para realizar esta accion'
        );
        return response()->json($data, $data['code']);
    }

    public function buscarVotanteMunicipio($dpto, $mcpio){  //Pendiente de prueba
        $puestos = DB::select('SELECT l.departamento_id, l.municipio_id, l.puesto, d.nombre_puesto, COUNT(l.nombre_puesto) as total FROM divipoles as d LEFT JOIN listadovotantes as l ON d.nombre_puesto = l.nombre_puesto and d.departamento_id = l.departamento_id and d.municipio_id = l.municipio_id
        WHERE d.departamento_id = '.$dpto.' and d.municipio_id = '.$mcpio.' GROUP BY l.departamento_id, l.municipio_id, l.puesto,d.nombre_puesto ORDER BY d.nombre_puesto');
        return $puestos;
    }

    public function contarVotantes(){
        $numeroTotal = DB::select('SELECT COUNT(*) as total FROM listadovotantes as l  WHERE l.candidato_id =  '.Auth::user()->candidato_id.' ');
        $data = array(
            'status' => 'success',
            'code' => 200,
            'total' => $numeroTotal[0]
        );
        return response()->json($data, $data['code']);
    }   
    
    public function excel_votantes_general($token_id, $tipo){

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
            $inner = "";
            $select = "";
            
            if($tipo == 1){
                $inner = "INNER JOIN coordinadores as c ON li.coordinadore_id = c.id"; 
            }else if($tipo == 2){                
                $inner = "INNER JOIN subcoordinadores as c ON li.subcoordinadore_id = c.id"; 
            }else{
                $inner = "LEFT JOIN coordinadores as c ON li.coordinadore_id = c.id
                LEFT JOIN subcoordinadores as s ON li.subcoordinadore_id = s.id";
                $select = ", s.nombres as nombre_subcoordinador, s.apellidos as apellidos_subcoordinador";
            }

            $votantes = DB::select("SELECT l.id, l.nombres, l.apellidos, l.fecha_nac, l.edad, l.direccion, l.telefono, l.correo, p.profesion, d.departamento, m.municipio, l.nombre_puesto, l.mesa, o.observacion, u.username, l.created_at,  li.nombres as nombre_lider, li.apellidos as apellido_lider, c.nombres as nombre_coordinador, c.apellidos as apellidos_coordinador $select
            FROM listadovotantes as l 
            INNER JOIN lideres as li ON l.lidere_id = li.id
            INNER JOIN profesiones as p ON l.profesione_id = p.id
            INNER JOIN departamentos as d ON l.departamento_id = d.id
            INNER JOIN municipios as m ON l.departamento_id = m.departamento_id AND l.municipio_id = m.id
            INNER JOIN observaciones as o ON l.observacione_id = o.id
            INNER JOIN users as u ON l.user_registra = u.id
            $inner
            WHERE l.candidato_id = $candidato
            ORDER BY c.nombres ASC, li.nombres ASC");

            // return $votantes;
            if($tipo > 0){
                $spreadsheet = IOFactory::load('plantillas/votantes_general.xlsx');
                $sheet = $spreadsheet->getSheetByName('GENERAL');
                $fila = 2;
                for ($i=0; $i < count($votantes) ; $i++) { 
                    $sheet->setCellValue("A$fila", $votantes[$i]->nombre_coordinador . ' ' . $votantes[$i]->apellidos_coordinador);
                    $sheet->setCellValue("B$fila", $votantes[$i]->nombre_lider . ' ' .$votantes[$i]->apellido_lider);
                    $sheet->setCellValue("C$fila", $votantes[$i]->id);
                    $sheet->setCellValue("D$fila", $votantes[$i]->nombres . ' ' .$votantes[$i]->apellidos);
                    
                    $sheet->setCellValue("E$fila", $votantes[$i]->direccion);
                    $sheet->setCellValue("F$fila", $votantes[$i]->telefono);
                    $sheet->setCellValue("G$fila", $votantes[$i]->municipio);
                    $sheet->setCellValue("H$fila", $votantes[$i]->nombre_puesto);
                    $sheet->setCellValue("I$fila", $votantes[$i]->mesa);
                    $sheet->setCellValue("J$fila", $votantes[$i]->profesion);
                    $sheet->setCellValue("K$fila", $votantes[$i]->edad);
                    $fila ++;
                }
            }else{
                $spreadsheet = IOFactory::load('plantillas/general_coord_subcoord.xlsx');
                $sheet = $spreadsheet->getSheetByName('GENERAL');
                $fila = 2;
                for ($i=0; $i < count($votantes) ; $i++) { 
                    $sheet->setCellValue("A$fila", $votantes[$i]->nombre_coordinador . ' ' . $votantes[$i]->apellidos_coordinador);
                    $sheet->setCellValue("B$fila", $votantes[$i]->nombre_subcoordinador . ' ' . $votantes[$i]->apellidos_subcoordinador);
                    $sheet->setCellValue("C$fila", $votantes[$i]->nombre_lider . ' ' .$votantes[$i]->apellido_lider);
                    $sheet->setCellValue("D$fila", $votantes[$i]->id);
                    $sheet->setCellValue("E$fila", $votantes[$i]->nombres . ' ' .$votantes[$i]->apellidos);
                    
                    $sheet->setCellValue("F$fila", $votantes[$i]->direccion);
                    $sheet->setCellValue("G$fila", $votantes[$i]->telefono);
                    $sheet->setCellValue("H$fila", $votantes[$i]->municipio);
                    $sheet->setCellValue("I$fila", $votantes[$i]->nombre_puesto);
                    $sheet->setCellValue("J$fila", $votantes[$i]->mesa);
                    $sheet->setCellValue("J$fila", $votantes[$i]->profesion);
                    $sheet->setCellValue("K$fila", $votantes[$i]->edad);
                    $fila ++;
                }
            }
        
            
            
            $filename = 'Excel_general'.time().'.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }
    }

    public function excel_votantes_repetidos($token){

        $token = DB::select("SELECT oat.id, u.candidato_id, c.corporacione_id, u.id as user_id
            FROM oauth_access_tokens as oat 
            INNER JOIN users as u
            ON oat.user_id = u.id
            INNER JOIN candidatos as c
            ON u.candidato_id = c.id
            WHERE oat.revoked = 0 AND oat.id = '". $token. " '  "
        );

        if(count($token) > 0){

            $candidato = $token[0]->candidato_id;
            $corporacion = $token[0]->corporacione_id;    
            $condicion_general = "";
            $condicion_in = "";

            if($corporacion == 5){
                $condicion_general = "lv.candidato_id = $candidato AND ";
                $condicion_in = "WHERE candidato_id = $candidato ";
            }

            $repetidos = DB::select("SELECT lv.id, lv.nombres, lv.apellidos, c.nombres as candidato, l.nombres as nombre_lider, l.apellidos as apellidos_lider, lv.created_at as fecha_ingreso
                FROM listadovotantes as lv
                INNER JOIN candidatos as c ON lv.candidato_id = c.id
                INNER JOIN lideres as l ON lv.lidere_id = l.id
                WHERE $condicion_general lv.id
                IN (SELECT id
                FROM listadovotantes
                $condicion_in
                GROUP BY id
                HAVING count(id) >1)
                ORDER BY nombres, fecha_ingreso");

                $spreadsheet = IOFactory::load('plantillas/repetidos.xlsx');
                $sheet = $spreadsheet->getSheetByName('Repetidos');
                $fila = 2;
                for ($i=0; $i < count($repetidos) ; $i++) { 
                    $sheet->setCellValue("A$fila", $repetidos[$i]->id);
                    $sheet->setCellValue("B$fila", $repetidos[$i]->nombres .' '.$repetidos[$i]->apellidos);
                    $sheet->setCellValue("C$fila", $repetidos[$i]->candidato);
                    $sheet->setCellValue("D$fila", $repetidos[$i]->nombre_lider . ' ' .$repetidos[$i]->apellidos_lider);
                    $sheet->setCellValue("E$fila", $repetidos[$i]->fecha_ingreso);
                    $fila++;
                }

                $filename = 'Excel_repetidos'.time().'.xls';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$filename.'"');
                header('Cache-Control: max-age=0');

                $writer = IOFactory::createWriter($spreadsheet, 'Xls');
                $writer->save('php://output');

                //Guarda un historial de que usuario exporta el reporte.
                $ip = $request->ip();
                $ip_reporte = new Ipreporte();
                $ip_reporte->desc_reporte = 'Reporte de militantes';
                $ip_reporte->ip_server = $ip;
                $ip_reporte->user_id = $token[0]->user_id;
                $ip_reporte->save();     
        }
    }

    public function lvotacion($id){ //Pendiente de prueba
        
        // $lugar = DB::select('call consulta(?)', array($id));        
        $jurado = Jurado::where([
            ['id',$id],
            ['candidato_id',Auth::user()->candidato]
        ])->get();
        $testigo = Testigo::where([
            ['id',$id],
            ['candidato_id',Auth::user()->candidato]
        ])->get();
        $votante = Listadovotante::with('lider')->where([
            ['cedula',$id],
            ['candidato_id',Auth::user()->candidato]
        ])->get();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'jurado' => $jurado,
            'testigo' => $testigo,
            'votante' => $votante,            
        );
        
        return response()->json($data, $data['code']);
    }

    public function mostrarVotantesPuesto(Request $request){ //Pendiente de prueba

        $departamento = $request->departamento_id;
        $municipio = $request->municipio_id;
        $nombre_puesto = $request->nombre_puesto;

        $votantes = DB::select('SELECT l.cedula, l.nombres, l.apellidos, l.zona, l.puesto, l.mesa, li.nombres as nombre_lider, li.apellidos as apellido_lider FROM listadovotantes as l INNER JOIN lideres as li ON l.lidere_id = li.id WHERE l.departamento_id = '.$departamento.' AND l.municipio_id = '.$municipio.' AND l.nombre_puesto = "'.$nombre_puesto.'" AND l.candidato_id = '.Auth::user()->candidato.' ');

        return $votantes;

    }

    public function puestosxVotantesAgregados(){
        $candidato = Auth::user()->candidato_id;
        $puestos = DB::select("SELECT nombre_puesto FROM listadovotantes WHERE candidato_id = $candidato AND observacione_id = 1 GROUP BY nombre_puesto"); 
        $data = array(
            'status' => 'success',
            'code' => 200,
            'puestos' => $puestos
        );
        return response()->json($data, $data['code']);
    }

    public function ver_votantes(Request $request){

        $tipo = $request->tipo;
        $opcion = $request->opcion;
        $candidato = $request->candidato;
        $role = $request->role;
        $condicion = "";
        $condicion_coordinador = "";
        $condicion_candidato = "";
        $inner = "";

        if($role == 6){
            $id = Auth::user()->id;
            $condicion_coordinador = " AND l.coordinadore_id =  $id ";
            $inner = "INNER JOIN coordinadores as c ON l.coordinadore_id = c.id";
            $condicion = "AND c.id = $id";
        }

        if($tipo == 1){
            $votantes = DB::select("SELECT lv.departamento_id, lv.municipio_id, d.departamento, m.municipio, lv.zona, lv.puesto, lv.nombre_puesto as lugar, COUNT(lv.nombre_puesto) as votantes FROM listadovotantes as lv 
                    INNER JOIN departamentos as d ON lv.departamento_id = d.id
                    INNER JOIN municipios as m ON lv.departamento_id = m.departamento_id AND lv.municipio_id = m.id
                    $inner
                    WHERE lv.candidato_id = $candidato AND lv.observacione_id = 1 $condicion
                    GROUP BY d.departamento, lv.departamento_id, m.municipio, lv.municipio_id, lv.nombre_puesto, lv.puesto, lv.zona
                    ORDER BY votantes DESC");
           
            
        }else{
            switch ($opcion) {
                case '1':
                    if($role == 6){
                        $votantes = DB::select("SELECT c.id, c.nombres, c.apellidos, COUNT(c.id) as votantes, (SELECT SUM(meta_votantes) FROM lideres WHERE coordinadore_id =  $id) as meta_votantes 
                            FROM listadovotantes as lv 
                            INNER JOIN lideres as l ON lv.lidere_id = l.id
                            INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                            WHERE lv.candidato_id = $candidato AND lv.observacione_id = 1 $condicion_coordinador
                            GROUP BY c.id, c.nombres, c.apellidos");
                    }else{
                        $votantes = DB::select("SELECT c.id, c.nombres, c.apellidos, c.meta_votacion as meta_votantes, COUNT(c.id) as votantes
                            FROM listadovotantes as lv 
                            INNER JOIN lideres as l ON lv.lidere_id = l.id
                            INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                            WHERE lv.candidato_id = $candidato AND lv.observacione_id = 1
                            GROUP BY c.id");   
                    }
                    break;
                case '2':
                        $votantes = DB::select("SELECT lv.lidere_id, l.nombres, l.apellidos, COUNT(lv.lidere_id) as votantes, l.meta_votantes 
                        FROM listadovotantes as lv 
                        INNER JOIN lideres as l ON lv.lidere_id = l.id
                        WHERE lv.candidato_id = $candidato AND lv.observacione_id = 1 $condicion_coordinador
                        GROUP BY lv.lidere_id, l.nombres, l.apellidos, l.meta_votantes
                        ORDER BY l.nombres, l.apellidos");
                        break;
                default:
                    $votantes = DB::select("SELECT lv.sublidere_id, l.nombres, l.apellidos, COUNT(lv.sublidere_id) as votantes, l.meta_votantes 
                        FROM listadovotantes as lv 
                        INNER JOIN sublideres as l ON lv.sublidere_id = l.id
                        WHERE lv.candidato_id = $candidato AND lv.observacione_id = 1 $condicion_coordinador
                        GROUP BY lv.sublidere_id, l.nombres, l.apellidos, l.meta_votantes
                        ORDER BY l.nombres, l.apellidos");
                    break;
            }
        }

        return $votantes;
    }

    public function ver_votantes_general($opcion){

        $candidato = Auth::user()->candidato_id;
        $role = Auth::user()->role_id;
        $id = Auth::user()->id;

        $condicion_coordinador = "";
        $condicion_lider = "";
        $inner_coordinador = "";
        
        if($opcion == 1){
            $edad = 'AND lv.edad >= 18';
        }else{
            $edad = 'AND lv.edad BETWEEN 14 AND 28';
        }
        
        if($role == 5){
            $condicion_lider = "AND lv.lidere_id = $id";
        }
        
        if($role == 6){
            $inner_coordinador = "INNER JOIN lideres as l
            ON lv.lidere_id = l.id
            INNER JOIN coordinadores as c
            ON l.coordinadore_id = c.id";
            $condicion_coordinador = "AND c.id = $id";

        }

        if($role == 8){
            $inner_coordinador = "INNER JOIN lideres as l
            ON lv.lidere_id = l.id
            INNER JOIN subcoordinadores as c
            ON l.subcoordinadore_id = c.id";
            $condicion_coordinador = "AND c.id = $id";
        }

        $votantes = DB::select("SELECT o.observacion, COUNT(lv.observacione_id) as votantes
            FROM listadovotantes as lv 
            INNER JOIN observaciones as o
            ON lv.observacione_id = o.id
            $inner_coordinador
            WHERE lv.candidato_id = $candidato $condicion_coordinador $condicion_lider $edad
            GROUP BY o.observacion");
        return $votantes;
    }

    public function votantes_confirmados_puesto($dpto, $mcpio, $zona, $puesto, $nombre_puesto, $comando){

        $votantes = DB::select("SELECT l.nombres as nombre_lider, l.apellidos as ape_lider, l.telefono as telefono_lider, a.*, c.nombre
            FROM asistencias AS a
            INNER JOIN comandos as c ON a.comando_id = c.id
            LEFT JOIN lideres as l ON a.lidere_id = l.id
            WHERE a.departamento_id = $dpto AND a.municipio_id = $mcpio AND a.zona = '" .$zona. "' AND a.puesto = '" .$puesto. "' AND a.comando_id = $comando
            ORDER BY created_at ASC
        ");


        $spreadsheet = IOFactory::load('plantillas/reporte_confirmados.xlsx');
        $sheet = $spreadsheet->getSheetByName('Hoja1');
        $sheet->setCellValue("B1", $nombre_puesto);
        $fila = 3;
        for ($i=0; $i < count($votantes) ; $i++) { 
            $sheet->setCellValue("A$fila", $votantes[$i]->nombre_lider.' '.$votantes[$i]->ape_lider);
            $sheet->setCellValue("B$fila", $votantes[$i]->cedula);
            $sheet->setCellValue("C$fila", $votantes[$i]->nombres);
            $sheet->setCellValue("D$fila", $votantes[$i]->mesa);
            $sheet->setCellValue("E$fila", $votantes[$i]->observacion);
            $sheet->setCellValue("F$fila", $votantes[$i]->nombre);
            $sheet->setCellValue("G$fila", $votantes[$i]->created_at);
            $fila++;
        }

        $filename = 'votantes_confirmados'.time().'.xls';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        
    }

    public function votantes_esperados_puesto($dpto, $mcpio, $zona, $puesto, $nombre_puesto){

        $votantes = DB::select("SELECT DISTINCT lv.id, c.nombres as nombre_coordinador, c.apellidos as ape_coordinador, c.telefono as telefono_coordinador, lv.nombres, lv.apellidos, lv.telefono, l.nombres as nombre_lider, l.apellidos as ape_lider, l.telefono as telefono_lider, lv.*
            FROM listadovotantes AS lv 
            INNER JOIN lideres as l ON lv.lidere_id = l.id
            INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
            WHERE lv.departamento_id = $dpto AND lv.municipio_id = $mcpio AND lv.zona = '" .$zona. "' AND lv.puesto = '" .$puesto. "' AND lv.observacione_id = 1
            ORDER BY nombre_lider ASC
        ");


        $spreadsheet = IOFactory::load('plantillas/reporte_esperados.xlsx');
        $sheet = $spreadsheet->getSheetByName('Hoja1');
        $sheet->setCellValue("B1", $nombre_puesto);
        $fila = 3;
        for ($i=0; $i < count($votantes) ; $i++) { 
            $sheet->setCellValue("A$fila", $votantes[$i]->nombre_coordinador.' '.$votantes[$i]->ape_coordinador);
            $sheet->setCellValue("B$fila", $votantes[$i]->telefono_coordinador);
            $sheet->setCellValue("C$fila", $votantes[$i]->nombre_lider.' '.$votantes[$i]->ape_lider);
            $sheet->setCellValue("D$fila", $votantes[$i]->telefono_lider);
            $sheet->setCellValue("E$fila", $votantes[$i]->id);
            $sheet->setCellValue("F$fila", $votantes[$i]->nombres.' '.$votantes[$i]->apellidos);
            $sheet->setCellValue("G$fila", $votantes[$i]->telefono);
            $sheet->setCellValue("H$fila", $votantes[$i]->mesa);
            $fila++;
        }

        $filename = 'votantes_esperados'.time().'.xls';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        
    }

    public function votantes_confirmados(){

        $votantes = DB::select("SELECT l.nombres as nombre_lider, l.apellidos as ape_lider, l.telefono as telefono_lider, a.*, c.nombre
            FROM asistencias AS a
            INNER JOIN comandos as c ON a.comando_id = c.id
            LEFT JOIN lideres as l ON a.lidere_id = l.id
            ORDER BY created_at ASC
        ");


        $spreadsheet = IOFactory::load('plantillas/reporte_confirmados.xlsx');
        $sheet = $spreadsheet->getSheetByName('Hoja1');
        $sheet->setCellValue("B1", 'GENERAL');
        $fila = 3;
        for ($i=0; $i < count($votantes) ; $i++) { 
            $sheet->setCellValue("A$fila", $votantes[$i]->nombre_lider.' '.$votantes[$i]->ape_lider);
            $sheet->setCellValue("B$fila", $votantes[$i]->cedula);
            $sheet->setCellValue("C$fila", $votantes[$i]->nombres);
            $sheet->setCellValue("D$fila", $votantes[$i]->mesa);
            $sheet->setCellValue("E$fila", $votantes[$i]->observacion);
            $sheet->setCellValue("F$fila", $votantes[$i]->nombre);
            $sheet->setCellValue("G$fila", $votantes[$i]->created_at);
            $fila++;
        }

        $filename = 'votantes_confirmados'.time().'.xls';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        
    }

    public function votantes_faltantes($dpto, $mcpio, $zona, $puesto, $nombre_puesto){

        $faltantes = DB::select("SELECT c.nombres as nombre_coordinador, c.apellidos as ape_coordinador, c.telefono as tele_coord, lv.*, l.nombres as nombre_lider, l.apellidos as ape_lider, l.telefono as telefono_lider FROM listadovotantes as lv 
        LEFT JOIN asistencias as a ON lv.id = a.cedula 
        INNER JOIN lideres as l ON lv.lidere_id = l.id
        INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
        WHERE a.cedula IS NULL AND lv.observacione_id = 1 AND lv.departamento_id = $dpto AND lv.municipio_id = $mcpio AND lv.zona = $zona AND lv.puesto = $puesto");
        $spreadsheet = IOFactory::load('plantillas/reporte_esperados.xlsx');
        $sheet = $spreadsheet->getSheetByName('Hoja1');
        $sheet->setCellValue("B1", $nombre_puesto);
        $fila = 3;
        for ($i=0; $i < count($faltantes) ; $i++) { 
            $sheet->setCellValue("A$fila", $faltantes[$i]->nombre_coordinador.' '.$faltantes[$i]->ape_coordinador);
            $sheet->setCellValue("B$fila", $faltantes[$i]->tele_coord);
            $sheet->setCellValue("C$fila", $faltantes[$i]->nombre_lider.' '.$faltantes[$i]->ape_lider);
            $sheet->setCellValue("D$fila", $faltantes[$i]->telefono_lider);
            $sheet->setCellValue("E$fila", $faltantes[$i]->id);
            $sheet->setCellValue("F$fila", $faltantes[$i]->nombres.' '.$faltantes[$i]->apellidos);
            $sheet->setCellValue("G$fila", $faltantes[$i]->telefono);
            $sheet->setCellValue("H$fila", $faltantes[$i]->mesa);
            $fila++;
        }

        $filename = 'votantes_faltantes'.time().'.xls';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
    }

    public function votantesxPuesto(Request $request){
        
        $dpto = $request->dpto;
        $mcpio = $request->mcpio;
        $zona = $request->zona;
        $puesto = $request->puesto;
        $candidato = $request->candidato;

        $votantes = DB::select("SELECT l.nombres, l.apellidos, COUNT(lidere_id) as votos, d.num_hombres, d.num_mujeres, d.mesas, d.nombre_puesto, c.nombres as candidato
            FROM listadovotantes AS lv 
            INNER JOIN lideres as l ON lv.lidere_id = l.id
            INNER JOIN divipoles as d ON lv.departamento_id = d.departamento_id AND lv.municipio_id = d.municipio_id AND lv.zona = d.zona AND lv.puesto = d.puesto
            INNER JOIN candidatos as c ON lv.candidato_id = c.id
            WHERE lv.departamento_id = $dpto AND lv.municipio_id = $mcpio AND lv.zona = '" .$zona. "' AND lv.puesto = '" .$puesto. "' AND lv.candidato_id = $candidato
            GROUP BY l.nombres, l.apellidos, d.num_hombres, d.num_mujeres, d.mesas, d.nombre_puesto
        ");
        return $votantes;
    }

    public function votantesxJurado(Request $request){
        $candidato = Auth::user()->candidato_id;

        if($request->filtro == 1){
            $profesione_id = $request->profesione_id;
            $votantes = Listadovotante::with('jurado', 'profesion')->where([
                'observacione_id' => 1,
                'profesione_id' => $profesione_id,
                'candidato_id' => $candidato
            ])->limit(1000)->get();
        }else{
            $puesto = $request->puesto_votacion;
            $votantes = Listadovotante::with('jurado', 'profesion')->where([
                'observacione_id' => 1,
                'nombre_puesto' => $puesto,
                'candidato_id' => $candidato
            ])->limit(1000)->get();
        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'votantes' => $votantes
        );

        return response()->json($data, $data['code']);

    }

    public function votantesxusuario(Request $request){

        $candidato = Auth::user()->candidato_id;
        
        $condicion_user = "";
        $condicion_fecha = "";

        $fecha_i = $request->fecha_inicial;
        $fecha_f = $request->fecha_final;

        if($request->user_id > 1){
            $user = $request->user_id;
            $condicion_user = "AND lv.user_registra = $user";
        }else if($request->user == 'undefined'){
            $condicion_user = "";
        }

        if($fecha_i != 0 && $fecha_f != 0){
            
            $condicion_fecha = "AND lv.created_at BETWEEN ' " . $fecha_i. " ' AND ' " . $fecha_f. " '";
        }

        $reporte = DB::select("SELECT u.`name`, COUNT(lv.user_registra) as total FROM listadovotantes as lv
        INNER JOIN users as u ON lv.user_registra = u.id
        WHERE lv.candidato_id = $candidato $condicion_fecha $condicion_user
        GROUP BY u.`name`
        ORDER BY total DESC");

        $agrupacion = $request->agrupacion;

        if($condicion_user == ''){
            if($agrupacion == 1){
                $dataChart = DB::select("SELECT lv.nombre_puesto, COUNT(lv.id) as total_militantes
                    FROM listadovotantes as lv
                    INNER JOIN lideres as l ON lv.lidere_id = l.id
                    LEFT JOIN sublideres as sl ON lv.sublidere_id = sl.id
                    INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                    WHERE lv.departamento_id = 13 AND lv.municipio_id = 1
                    GROUP BY lv.nombre_puesto");
            }else{
                $dataChart = DB::select("SELECT lv.comuna, COUNT(lv.id) as total_militantes
                    FROM listadovotantes as lv
                    INNER JOIN lideres as l ON lv.lidere_id = l.id
                    LEFT JOIN sublideres as sl ON lv.sublidere_id = sl.id
                    INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                    WHERE lv.departamento_id = 13 AND lv.municipio_id = 1
                    GROUP BY lv.comuna");
            }
        }else{
            if($agrupacion == 1){
                $dataChart = DB::select("SELECT lv.nombre_puesto, COUNT(lv.id) as total_militantes
                FROM listadovotantes as lv
                INNER JOIN lideres as l ON lv.lidere_id = l.id
                LEFT JOIN sublideres as sl ON lv.sublidere_id = sl.id
                INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                WHERE lv.departamento_id = 13 AND lv.municipio_id = 1 AND lv.user_registra = $user
                GROUP BY lv.nombre_puesto");
            }else{
                $dataChart = DB::select("SELECT lv.comuna, COUNT(lv.id) as total_militantes
                FROM listadovotantes as lv
                INNER JOIN lideres as l ON lv.lidere_id = l.id
                LEFT JOIN sublideres as sl ON lv.sublidere_id = sl.id
                INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                WHERE lv.departamento_id = 13 AND lv.municipio_id = 1 AND lv.user_registra = $user
                GROUP BY lv.comuna");
            }
        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'resultado' => $reporte,
            'dataChart' => $dataChart
        );

        return response()->json($data, $data['code']);

    }

    public function votantes_por_dpto(){
        
        $candidato_id = Auth::user()->candidato_id;
        $role_id = Auth::user()->role_id;
        $user_id = Auth::user()->id;
        
        $condicion_adicional = "";
        
        // Si es coordinador (role_id = 6), solo mostrar votantes de su coordinación
        if($role_id == 6){
            $condicion_adicional = "INNER JOIN lideres as l ON lv.lidere_id = l.id
                                    INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                                    AND c.id = $user_id";
        }
        // Si es líder (role_id = 5), solo mostrar sus votantes
        elseif($role_id == 5){
            $condicion_adicional = "AND lv.lidere_id = $user_id";
        }

        $votantes_por_dpto = DB::select("SELECT 
            d.id as departamento_id,
            d.departamento,
            COUNT(lv.id) as total_votantes,
            SUM(CASE WHEN lv.observacione_id = 1 THEN 1 ELSE 0 END) as votantes_confirmados,
            SUM(CASE WHEN lv.observacione_id = 4 THEN 1 ELSE 0 END) as votantes_repetidos,
            SUM(CASE WHEN lv.observacione_id NOT IN (1,4) THEN 1 ELSE 0 END) as otros_estados
            FROM listadovotantes as lv 
            INNER JOIN departamentos as d ON lv.departamento_id = d.id
            $condicion_adicional
            WHERE lv.candidato_id = ?
            GROUP BY d.id, d.departamento
            ORDER BY total_votantes DESC", [$candidato_id]);

        $data = array(
            'status' => 'success',
            'code' => 200,
            'votantes_por_departamento' => $votantes_por_dpto,
            'total_general' => array_sum(array_column($votantes_por_dpto, 'total_votantes'))
        );

        return response()->json($data, $data['code']);
    }

    public function votantes_por_mcpios($dpto_id){
        
        $candidato_id = Auth::user()->candidato_id;
        $role_id = Auth::user()->role_id;
        $user_id = Auth::user()->id;
        
        $condicion_adicional = "";
        
        // Si es coordinador (role_id = 6), solo mostrar votantes de su coordinación
        if($role_id == 6){
            $condicion_adicional = "INNER JOIN lideres as l ON lv.lidere_id = l.id
                                    INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                                    AND c.id = $user_id";
        }
        // Si es líder (role_id = 5), solo mostrar sus votantes
        elseif($role_id == 5){
            $condicion_adicional = "AND lv.lidere_id = $user_id";
        }

        $votantes_por_mcpio = DB::select("SELECT 
            m.id as municipio_id,
            m.municipio,
            COUNT(lv.id) as total_votantes,
            SUM(CASE WHEN lv.observacione_id = 1 THEN 1 ELSE 0 END) as votantes_confirmados,
            SUM(CASE WHEN lv.observacione_id = 4 THEN 1 ELSE 0 END) as votantes_repetidos,
            SUM(CASE WHEN lv.observacione_id NOT IN (1,4) THEN 1 ELSE 0 END) as otros_estados
            FROM listadovotantes as lv 
            INNER JOIN departamentos as d ON lv.departamento_id = d.id
            INNER JOIN municipios as m ON lv.departamento_id = m.departamento_id AND lv.municipio_id = m.id
            $condicion_adicional
            WHERE lv.candidato_id = ? AND lv.departamento_id = ?
            GROUP BY m.id, m.municipio
            ORDER BY total_votantes DESC", [$candidato_id, $dpto_id]);

        $data = array(
            'status' => 'success',
            'code' => 200,
            'votantes_por_municipio' => $votantes_por_mcpio,
            'total_general' => array_sum(array_column($votantes_por_mcpio, 'total_votantes'))
        );

        return response()->json($data, $data['code']);
    }

    public function votantes_por_mcpio($dpto_id, $mcpio_id){
        
        $candidato_id = Auth::user()->candidato_id;
        $role_id = Auth::user()->role_id;
        $user_id = Auth::user()->id;
        
        $condicion_adicional = "";
        
        // Si es coordinador (role_id = 6), solo mostrar votantes de su coordinación
        if($role_id == 6){
            $condicion_adicional = "INNER JOIN lideres as l ON lv.lidere_id = l.id
                                    INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
                                    AND c.id = $user_id";
        }
        // Si es líder (role_id = 5), solo mostrar sus votantes
        elseif($role_id == 5){
            $condicion_adicional = "AND lv.lidere_id = $user_id";
        }

        if($mcpio_id == 1){
            $votantes_por_mcpio = DB::select("SELECT 
            lv.comuna, lv.nombre_puesto,
            COUNT(lv.id) as total_votantes,
            SUM(CASE WHEN lv.observacione_id = 1 THEN 1 ELSE 0 END) as votantes_confirmados,
            SUM(CASE WHEN lv.observacione_id = 4 THEN 1 ELSE 0 END) as votantes_repetidos,
            SUM(CASE WHEN lv.observacione_id NOT IN (1,4) THEN 1 ELSE 0 END) as otros_estados
            FROM listadovotantes as lv 
            INNER JOIN departamentos as d ON lv.departamento_id = d.id
            INNER JOIN municipios as m ON lv.departamento_id = m.departamento_id AND lv.municipio_id = m.id
            $condicion_adicional
            WHERE lv.candidato_id = ? AND lv.departamento_id = ? AND lv.municipio_id = ?
            GROUP BY lv.comuna, lv.nombre_puesto
            ORDER BY lv.comuna ASC, total_votantes DESC", [$candidato_id, $dpto_id, $mcpio_id]);
        }else{
            
        $votantes_por_mcpio = DB::select("SELECT 
            lv.nombre_puesto,
            COUNT(lv.id) as total_votantes,
            SUM(CASE WHEN lv.observacione_id = 1 THEN 1 ELSE 0 END) as votantes_confirmados,
            SUM(CASE WHEN lv.observacione_id = 4 THEN 1 ELSE 0 END) as votantes_repetidos,
            SUM(CASE WHEN lv.observacione_id NOT IN (1,4) THEN 1 ELSE 0 END) as otros_estados
            FROM listadovotantes as lv 
            INNER JOIN departamentos as d ON lv.departamento_id = d.id
            INNER JOIN municipios as m ON lv.departamento_id = m.departamento_id AND lv.municipio_id = m.id
            $condicion_adicional
            WHERE lv.candidato_id = ? AND lv.departamento_id = ? AND lv.municipio_id = ?
            GROUP BY lv.nombre_puesto
            ORDER BY total_votantes DESC", [$candidato_id, $dpto_id, $mcpio_id]);
        }


        $data = array(
            'status' => 'success',
            'code' => 200,
            'votantes_por_municipio' => $votantes_por_mcpio,
            'total_general' => array_sum(array_column($votantes_por_mcpio, 'total_votantes'))
        );

        return response()->json($data, $data['code']);
    }
}
