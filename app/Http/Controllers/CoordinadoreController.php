<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\Fill;

use App\Models\Coordinadore;
use App\Models\Ipreporte;
use App\Models\User;
use App\Models\Lidere;
use App\Models\Listadovotante;

class CoordinadoreController extends Controller
{
    public function excel_coordinadores(Request $request, $token_id, $coordinador, $lider, $sublider){

        //Importante permite generar gran cantidad de registros.
        ini_set('memory_limit', '-1');
        set_time_limit(3000000);

        $token = DB::select("SELECT oat.id, u.candidato_id, c.corporacione_id, u.id as user_id
            FROM oauth_access_tokens as oat 
            INNER JOIN users as u
            ON oat.user_id = u.id
            INNER JOIN candidatos as c
            ON u.candidato_id = c.id
            WHERE oat.revoked = 0 AND oat.id = '". $token_id. " '  "
        );

        if(count($token) > 0){

            $condicion = '';
            $order = '';
            $candidato = $token[0]->candidato_id;

            if($coordinador == 'undefined' || $coordinador == -1){

                if($lider == 'undefined' || $lider == -1){
                    $condicion = "";
                    $order = "lv.nombre_puesto";
                }else{
                    $condicion = "AND l.id = $lider AND l.candidato_id = $candidato";
                    $order = "l.nombres, l.apellidos";                
                }
                
            }else{
                
                if($lider == 'undefined' || $lider == -1){
                    $condicion = "AND c.id = $coordinador";
                    $order = "c.nombres, c.apellidos";                
                }else{
                    $condicion = "AND c.id = $coordinador AND l.id = $lider ";
                    $order = "c.nombres, c.apellidos, l.nombres, l.apellidos";                
                }

            }

            $condicion_sublider = ($sublider == -1) ? '' : "AND lv.sublidere_id = $sublider";


            $votantes = DB::select("SELECT o.observacion, ca.nombres as candidato, c.nombres as nom_coordinador, c.apellidos as ape_coordinador, l.nombres as nom_lider, l.apellidos as ape_lider, sl.nombres as nom_sublider, sl.apellidos as ape_sublider, lv.nombres, lv.apellidos, lv.fecha_nac, lv.id, lv.direccion, d.departamento, m.municipio, lv.nombre_puesto, lv.mesa, lv.telefono, lv.created_at as fecha_ingreso
                FROM listadovotantes as lv 
                INNER JOIN candidatos as ca ON lv.candidato_id = ca.id
                INNER JOIN departamentos as d ON lv.departamento_id = d.id
                INNER JOIN municipios as m ON lv.departamento_id = m.departamento_id AND lv.municipio_id = m.id
                LEFT JOIN lideres as l ON lv.lidere_id = l.id AND lv.candidato_id = l.candidato_id
                LEFT JOIN sublideres as sl ON lv.sublidere_id = sl.id
                LEFT JOIN coordinadores as c ON l.coordinadore_id = c.id AND l.candidato_id = c.candidato_id
                INNER JOIN observaciones as o ON lv.observacione_id = o.id
                WHERE lv.candidato_id = $candidato $condicion $condicion_sublider
                ORDER BY $order  ASC");

            $spreadsheet = IOFactory::load('plantillas/votantes.xlsx');
            $sheet = $spreadsheet->getSheetByName('GENERAL');

            $fila = 2;
            for ($i=0; $i < count($votantes) ; $i++) { 
                $sheet->setCellValue("A$fila", $votantes[$i]->nom_coordinador . ' ' . $votantes[$i]->ape_coordinador);
                $sheet->setCellValue("B$fila", $votantes[$i]->nom_lider . ' ' .$votantes[$i]->ape_lider);
                $sheet->setCellValue("C$fila", $votantes[$i]->nom_sublider . ' ' .$votantes[$i]->ape_sublider);
                $sheet->setCellValue("D$fila", $votantes[$i]->id);
                $sheet->setCellValue("E$fila", $votantes[$i]->nombres . ' ' .$votantes[$i]->apellidos);    
                $sheet->setCellValue("F$fila", $votantes[$i]->fecha_nac);
                // $sheet->setCellValue("H$fila", $votantes[$i]->correo);
                $sheet->setCellValue("G$fila", $votantes[$i]->direccion);
                $sheet->setCellValue("H$fila", $votantes[$i]->telefono);
                $sheet->setCellValue("I$fila", $votantes[$i]->departamento);
                $sheet->setCellValue("J$fila", $votantes[$i]->municipio);
                $sheet->setCellValue("K$fila", $votantes[$i]->nombre_puesto);
                $sheet->setCellValue("L$fila", $votantes[$i]->mesa);
                $sheet->setCellValue("M$fila", $votantes[$i]->fecha_ingreso);
                $sheet->setCellValue("N$fila", $votantes[$i]->observacion);
                $fila ++;
            }

            $filename = 'Excel_votantes'.time().'.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');


            //Guarda un historial de que usuario exporta el reporte.
            $ip = $request->ip();
            $ip_reporte = new Ipreporte();
            $ip_reporte->desc_reporte = 'Reporte de militantes';
            $ip_reporte->ip_server = $ip;
            $ip_reporte->user_id = $token[0]->user_id;
            $ip_reporte->save();            

            /* $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($sheet);
            $writer->save("05featuredemo.csv"); */
        }
    }

    public function getLideres($coordinadore_id, $candidato){
        $lideres = DB::select("SELECT c.id as coordinadore_id, c.nombres as nombres_coordinador, c.apellidos as apellidos_coordinador, l.id, l.candidato_id, l.nombres, l.apellidos, l.fecha_nac, l.meta_votantes, l.telefono, l.direccion, l.profesione_id, COUNT(s.id) as numero_sublideres, (SELECT COUNT(*) FROM listadovotantes as lv WHERE lv.lidere_id = l.id AND lv.observacione_id = 1) as total_militantes
            FROM lideres as l 
            INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
            LEFT JOIN sublideres as s ON l.id = s.lidere_id
            WHERE l.coordinadore_id = $coordinadore_id AND l.candidato_id = $candidato
            GROUP BY c.id, c.nombres, c.apellidos, l.id, l.candidato_id, l.nombres, l.apellidos, l.fecha_nac, l.meta_votantes, l.telefono, l.direccion, l.profesione_id");
        $data = array(
            'code' => 200,
            'status' => 'success',
            'lideres' => $lideres
        );
        return response()->json($data, $data['code']);
    }

    public function index($candidato){
        
        // $coordinadores = Coordinadore::all(); 
        $coordinadores = Coordinadore::where('candidato_id', $candidato)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'coordinadores' => $coordinadores
        );
        return response()->json($data, $data['code']);
    }

    public function infoCoordinadores($id, $agrupacion){
        if($id == -1){
            if($agrupacion == 1){
                $info = DB::select("SELECT lv.departamento_id, d.departamento, (SELECT SUM(meta_votacion) FROM coordinadores ) as meta_votacion, (SELECT COUNT(*) FROM coordinadores ) as total_coordinadores, (SELECT COUNT(*) FROM lideres ) as total_lideres, COUNT(lv.id) as total_militantes
                    FROM listadovotantes as lv
                    INNER JOIN lideres as l ON lv.lidere_id = l.id
                    LEFT JOIN sublideres as sl ON lv.sublidere_id = sl.id
                    INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
					INNER JOIN departamentos as d ON lv.departamento_id = d.id
                    WHERE lv.observacione_id != 4
                    GROUP BY lv.departamento_id, d.departamento");
            }else{
                $info = DB::select("SELECT lv.municipio_id, d.departamento, m.municipio, (SELECT SUM(meta_votacion) FROM coordinadores ) as meta_votacion, (SELECT COUNT(*) FROM coordinadores ) as total_coordinadores, (SELECT COUNT(*) FROM lideres ) as total_lideres, COUNT(lv.id) as total_militantes
                    FROM listadovotantes as lv
                    INNER JOIN lideres as l ON lv.lidere_id = l.id
                    LEFT JOIN sublideres as sl ON lv.sublidere_id = sl.id
                    INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
					INNER JOIN departamentos as d ON lv.departamento_id = d.id
                    INNER JOIN municipios as m ON lv.departamento_id = m.departamento_id AND lv.municipio_id = m.id
                    WHERE lv.observacione_id != 4
                    GROUP BY lv.municipio_id, d.departamento, m.municipio");
            }
        }else{
            if($agrupacion == 1){
                $info = DB::select("SELECT lv.departamento_id, d.departamento, c.meta_votacion as meta_votacion, (SELECT COUNT(*) FROM lideres WHERE coordinadore_id = $id) as total_lideres, COUNT(lv.id) as total_militantes
                FROM listadovotantes as lv
                INNER JOIN lideres as l ON lv.lidere_id = l.id
                LEFT JOIN sublideres as sl ON lv.sublidere_id = sl.id
                INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
				INNER JOIN departamentos as d ON lv.departamento_id = d.id
                WHERE l.coordinadore_id = $id AND lv.observacione_id != 4
                GROUP BY lv.departamento_id, d.departamento");
            }else{
                $info = DB::select("SELECT lv.municipio_id, d.departamento, m.municipio, c.meta_votacion as meta_votacion, (SELECT COUNT(*) FROM lideres WHERE coordinadore_id = $id) as total_lideres, COUNT(lv.id) as total_militantes
                FROM listadovotantes as lv
                INNER JOIN lideres as l ON lv.lidere_id = l.id
                LEFT JOIN sublideres as sl ON lv.sublidere_id = sl.id
                INNER JOIN coordinadores as c ON l.coordinadore_id = c.id
				INNER JOIN departamentos as d ON lv.departamento_id = d.id
				INNER JOIN municipios as m ON lv.departamento_id = m.departamento_id AND lv.municipio_id = m.id
                WHERE l.coordinadore_id = $id AND lv.observacione_id != 4
                GROUP BY lv.municipio_id, d.departamento, m.municipio");
            }
        }

        $data = array(
            "code" => 200,
            "status" => 'success',
            "info" => $info
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

        $coordinador = Coordinadore::with('candidato')->find($id);
        $response = array(
            'status' => 'success',
            'code' => 200,
            'coordinador' => $coordinador
        ); 
        return response()->json($response, $response['code']);

    }

    public function store(Request $request){

        $validator = \Validator::make($request->all(), [
            'id' => 'required|unique:coordinadores',
            'nombres' => 'required',
            'apellidos' => 'required',
            'fecha_nac' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'profesione_id' => 'required',
            'meta_votacion' => 'required|numeric',
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $cumple = explode("-", $request->fecha_nac);

        /* $usuario = new User();
        $usuario->role_id = 3;         
        $usuario->name = $request->nombres.' '.$request->apellidos;
        $usuario->email = $request->correo;
        $usuario->password = Hash::make($request->cedula);
        $usuario->save(); */

        $coordinador = new Coordinadore();
        $coordinador->id = $request->id;
        $coordinador->nombres = $request->nombres;
        $coordinador->apellidos = $request->apellidos;
        $coordinador->fecha_nac = $request->fecha_nac;
        $coordinador->mes_nac = $cumple[1];
        $coordinador->dia_nac = $cumple[2];
        $coordinador->direccion = $request->direccion;
        $coordinador->barrio = $request->barrio;
        $coordinador->telefono = $request->telefono;
        $coordinador->observaciones = $request->observaciones;
        $coordinador->perfil = $request->perfil;
        $coordinador->meta_votacion = $request->meta_votacion;
        $coordinador->candidato_id = $request->candidato_id;
        $coordinador->profesione_id = $request->profesione_id;
        $coordinador->user_id = Auth::user()->id;

        $coordinador->save();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'coordinador' => $coordinador
        );
        return response()->json($data, $data['code']);

    }

    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'profesione_id' => 'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $cumple = explode("-", $request->fecha_nac);
        $coordinador = Coordinadore::find($id);
        $coordinador->nombres = $request->nombres;
        $coordinador->apellidos = $request->apellidos;
        $coordinador->fecha_nac = $request->fecha_nac;
        $coordinador->mes_nac = $cumple[1];
        $coordinador->dia_nac = $cumple[2];
        $coordinador->direccion = $request->direccion;
        $coordinador->barrio = $request->barrio;
        $coordinador->telefono = $request->telefono;
        $coordinador->observaciones = $request->observaciones;
        $coordinador->perfil = $request->perfil;
        $coordinador->meta_votacion = $request->meta_votacion;
        $coordinador->profesione_id = $request->profesione_id;

        $coordinador->save();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'coordinador' => $coordinador
        );
        return response()->json($data, $data['code']);
    }

    public function destroy($id){
        try {
            // Verificar que el coordinador existe
            $coordinador = Coordinadore::find($id);
            if (!$coordinador) {
                return response()->json([
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Coordinador no encontrado'
                ], 404);
            }

            // Obtener el candidato_id del usuario autenticado
            $candidato_id = Auth::user()->candidato_id;

            // 1. Buscar los líderes que tengan coordinadore_id igual al id recibido
            $lideres = Lidere::where('coordinadore_id', $id)->get();

            // Recopilar los IDs de los líderes para usar en la siguiente consulta
            $lideres_ids = $lideres->pluck('id')->toArray();

            // 2. Buscar en la tabla listadovotantes los registros que tengan lidere_id 
            // de los líderes encontrados y validar que el candidato sea el del usuario autenticado
            if (!empty($lideres_ids)) {
                $listadovotantes = Listadovotante::whereIn('lidere_id', $lideres_ids)
                    ->where('candidato_id', $candidato_id)
                    ->get();

                // Eliminar los registros de listadovotantes
                Listadovotante::whereIn('lidere_id', $lideres_ids)
                    ->where('candidato_id', $candidato_id)
                    ->delete();
            }

            // Eliminar los líderes asociados al coordinador
            Lidere::where('coordinadore_id', $id)->delete();

            // Finalmente eliminar el coordinador
            $coordinador->delete();

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Coordinador eliminado exitosamente',
                'eliminados' => [
                    'coordinador' => 1,
                    'lideres' => count($lideres_ids),
                    'votantes' => isset($listadovotantes) ? count($listadovotantes) : 0
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Error al eliminar el coordinador: ' . $e->getMessage()
            ], 500);
        }
    }
}
