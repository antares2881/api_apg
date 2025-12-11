<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Models\Asistencia;

class AsistenciaController extends Controller
{
    public function asistenciaComandos($comando_id){

        $condicion_comando = "";
        
        if(Auth::user()->role_id > 2){
            
            $condicion_comando = "WHERE a.comando_id = $comando_id";
        }

        $asistencia_comandos = DB::select("SELECT c.id, c.nombre, c.proyectados, COUNT(a.comando_id) as confirmados, c.contacto
            FROM comandos as c 
            LEFT JOIN asistencias as a ON a.comando_id = c.id 
            $condicion_comando
            GROUP BY c.id, c.nombre, c.proyectados, c.contacto");
            
        $data = array(
            'status' => 'success',
            'code' => 200,
            'asistencia_comandos' => $asistencia_comandos
        );

        return response()->json($data, $data['code']);

    }
    public function DarAsistencia(Request $request){
        $validator = \Validator::make($request->all(), [
            'cedula' => 'required',
            'nombres' => 'required',
            'desc_dpto' => 'required',
            'desc_mcpio' => 'required',
            'nombre_puesto' => 'required',
            'mesa' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $asistencia = new Asistencia();
        $asistencia->cedula = $request->cedula;
        $asistencia->nombres = $request->nombres.' '.$request->apellidos;
        $asistencia->desc_dpto = $request->desc_dpto;
        $asistencia->desc_mcpio = $request->desc_mcpio;
        $asistencia->nombre_puesto = $request->nombre_puesto;
        $asistencia->comuna = $request->comuna;
        $asistencia->departamento_id = $request->departamento_id;
        $asistencia->municipio_id = $request->municipio_id;
        $asistencia->zona = $request->zona;
        $asistencia->puesto = $request->puesto;
        $asistencia->mesa = $request->mesa;
        $asistencia->lidere_id = $request->lidere_id;
        $asistencia->observacion = $request->observacion;
        $asistencia->comando_id = $request->comando_id;
        $asistencia->user_id = Auth::user()->id;

        $asistencia->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'asistencia' => $asistencia
        );

        return response()->json($data, $data['code']);
    }

    public function verificarAsistencia($cedula){
        // $asistencia = Asistencia::where('cedula', $cedula)->get();

        $asistencia = DB::select("SELECT a.*, c.nombre as nombre_comando FROM asistencias as a INNER JOIN comandos as c ON a.comando_id = c.id WHERE cedula = $cedula");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'asistencia' => $asistencia
        );

        return response()->json($data, $data['code']);
    }

    public function votantes_confirmados(Request $request){
                
        $opcion = $request->opcion;
        $parametro = $request->parametro;

        if($opcion == 1){
            $condicion = "WHERE a.nombres LIKE '%".$parametro."%' ";
        }else{
            $condicion = "WHERE a.cedula = $parametro";
        }
                
        $votantes = DB::select("SELECT a.*, c.nombre 
            FROM asistencias AS a 
            INNER JOIN comandos as c ON a.comando_id = c.id
            $condicion
            ORDER BY c.nombre
        ");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'confirmados' => $votantes
        );

        return response()->json($data, $data['code']);
    }

    public function votantes_comando($id, $comando, $role){

        $condicion = "";

        if($role == 10 ){
            $condicion = "WHERE c.id = $id ";
        }

        $confirmados = DB::select("SELECT a.*, c.nombre FROM comandos AS c INNER JOIN asistencias AS a ON c.id = a.comando_id WHERE c.id = $id ORDER BY a.zona ASC, a.puesto ASC, a.mesa ASC");
        $spreadsheet = IOFactory::load('plantillas/reporte_confirmados_comando.xlsx');
        $sheet = $spreadsheet->getSheetByName('Hoja1');
        $sheet->setCellValue("B1", $comando);
        $fila = 3;
        for ($i=0; $i < count($confirmados) ; $i++) { 
            $sheet->setCellValue("A$fila", $confirmados[$i]->cedula);
            $sheet->setCellValue("B$fila", $confirmados[$i]->nombres);
            $sheet->setCellValue("C$fila", $confirmados[$i]->nombre_puesto);
            $sheet->setCellValue("D$fila", $confirmados[$i]->mesa);
            $sheet->setCellValue("E$fila", $confirmados[$i]->observacion);
            $sheet->setCellValue("F$fila", $confirmados[$i]->created_at);
            $fila++;
        }

        $filename = 'votantes_confirmados'.time().'.xls';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
    }
    
}
