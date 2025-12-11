<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\Fill;

use App\Models\Firma;
use Carbon\Carbon;

class FirmaController extends Controller
{
    public function excel($token_id, $fecha_i, $fecha_f){

        $token = DB::select("SELECT oat.id, u.candidato_id, c.corporacione_id, u.id, u.role_id
            FROM oauth_access_tokens as oat 
            INNER JOIN users as u
            ON oat.user_id = u.id
            INNER JOIN candidatos as c
            ON u.candidato_id = c.id
            WHERE oat.revoked = 0 AND oat.id = '". $token_id. " '  "
        );
        // return $fecha_i;
        if($fecha_i == 0 || $fecha_f == 0){
            $condicion_fecha = '';
        }else{
            $condicion_fecha = " AND f.fecha_firma BETWEEN ' " . $fecha_i. " ' AND ' " . $fecha_f. " ' ";
        }
        if(count($token) > 0){

            $candidato = $token[0]->candidato_id;            
            if($token[0]->role_id == 2){
                $registros = DB::select("SELECT f.cedula, f.nombres, f.apellidos, d.departamento, m.municipio, f.puesto, f.mesa, o.observacion, f.numero_folio, f.renglon, r.nombres as nom_recole, r.apellidos as ape_recole, f.fecha_firma, u.username
                    FROM firmas as f
                    INNER JOIN users as u ON f.user_id = u.id
                    INNER JOIN departamentos as d ON f.departamento_id = d.id
                    INNER JOIN municipios as m ON f.departamento_id = m.departamento_id AND f.municipio_id = m.id
                    INNER JOIN candidatos as c ON u.candidato_id = c.id
                    INNER JOIN observaciones as o ON f.observacione_id = o.id
                    INNER JOIN recolectores as r ON f.recolectore_id = r.id
                    WHERE u.candidato_id = $candidato $condicion_fecha
                ");
            }else{
                $user_id = $token[0]->id;
                $registros = DB::select("SELECT f.cedula, f.nombres, f.apellidos, d.departamento, m.municipio, f.puesto, f.mesa, o.observacion, f.numero_folio, f.renglon, r.nombres as nom_recole, r.apellidos as ape_recole, f.fecha_firma, u.username
                    FROM firmas as f
                    INNER JOIN users as u ON f.user_id = u.id
                    INNER JOIN departamentos as d ON f.departamento_id = d.id
                    INNER JOIN municipios as m ON f.departamento_id = m.departamento_id AND f.municipio_id = m.id
                    INNER JOIN candidatos as c ON u.candidato_id = c.id
                    INNER JOIN observaciones as o ON f.observacione_id = o.id
                    INNER JOIN recolectores as r ON f.recolectore_id = r.id
                    WHERE u.candidato_id = $candidato AND f.user_id = $user_id  $condicion_fecha
                ");
            }
            $spreadsheet = IOFactory::load('plantillas/firmas_general.xlsx');
    
            $sheet = $spreadsheet->getSheetByName('GENERAL');
            $fila = 2;
    
            for ($i=0; $i < count($registros) ; $i++) { 
                $sheet->setCellValue("A$fila", $registros[$i]->cedula);
                $sheet->setCellValue("B$fila", $registros[$i]->nombres);
                $sheet->setCellValue("C$fila", $registros[$i]->apellidos);
                $sheet->setCellValue("D$fila", $registros[$i]->departamento);
                $sheet->setCellValue("E$fila", $registros[$i]->municipio);
                $sheet->setCellValue("F$fila", $registros[$i]->puesto);
                $sheet->setCellValue("G$fila", $registros[$i]->mesa);
                $sheet->setCellValue("H$fila", $registros[$i]->observacion);
                $sheet->setCellValue("I$fila", $registros[$i]->numero_folio);
                $sheet->setCellValue("J$fila", $registros[$i]->renglon);
                $sheet->setCellValue("K$fila", $registros[$i]->nom_recole.' '.$registros[$i]->ape_recole);
                $sheet->setCellValue("L$fila", $registros[$i]->fecha_firma);
                $sheet->setCellValue("M$fila", $registros[$i]->username);
                $fila++;
            }
            
            $filename = 'Reporte_firmas_'.time().'.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
    
            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }
    }
    public function show($id, $candidato){
        // $firma = Firma::where('cedula', $id)->get();
        $firma = DB::select("SELECT f.* FROM firmas as f
            INNER JOIN users as u
            on f.user_id = u.id
            WHERE f.cedula = $id AND u.candidato_id = $candidato");
        
        $data = array(
            'status'=> 'success',
            'code' => 200,
            'firma' => $firma
        );

        return response()->json($data, $data['code']);
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'nombres' => 'required',
            'apellidos' => 'required',
            'observacione_id' => 'required',
            'numero_folio' => 'required|string',
            'renglon' => 'required|integer',
            'recolectore_id' => 'required',
            'estado' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $firma = new Firma();
        $firma->cedula = $request->cedula;
        $firma->recolectore_id = $request->recolectore_id;
        $firma->observacione_id = $request->observacione_id;
        $firma->departamento_id = $request->departamento_id;
        $firma->municipio_id = $request->municipio_id;
        $firma->puesto = $request->puesto; //Excepto chinu
        $firma->mesa = $request->mesa; //Excepto chinu
        $firma->nombres = $request->nombres;
        $firma->apellidos = $request->apellidos;
        $firma->numero_folio = $request->numero_folio;
        $firma->renglon = $request->renglon;
        $firma->estado = $request->estado;
        $firma->user_id = $request->user_id;
        $firma->fecha_firma = Carbon::now();

        $firma->save();

        $data = array(
            'status'=> 'success',
            'code' => 200,
            'firma' => $firma
        );

        return response()->json($data, $data['code']);
    }
}
