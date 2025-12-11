<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\Fill;

use App\Models\Preconteo;

class PreconteoController extends Controller
{
    public function candidatos($municipio){
        $candidatos = DB::select("SELECT * FROM preconteocandidatos WHERE municipio_id = $municipio");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'candidatos' => $candidatos
        );
        return response()->json($data, $data['code']);
    }

    public function mesas_informadas(Request $request){

        $puesto = $request->puesto;
        
        $mesas = DB::select("SELECT dp.id, dp.mesa FROM preconteos AS p 
        INNER JOIN divipolepreconteos as dp ON p.divipolepreconteo_id = dp.id
        WHERE dp.puesto = '".$puesto."'
        GROUP BY dp.id, dp.mesa
        ORDER BY dp.mesa ASC");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'mesas' => $mesas
        );

        return response()->json($data, $data['code']); 
    }

    public function mesas_faltantes($dpto, $mcpio){

        $faltantes = DB::select("SELECT dp.cod_dpto, dp.cod_mcpio, dp.mcpio, dp.cod_zona, dp.cod_puesto, dp.puesto, COUNT(dp.mesa) as faltantes 
        FROM divipolepreconteos as dp 
        LEFT JOIN preconteos as p ON dp.id = p.divipolepreconteo_id 
        WHERE dp.cod_dpto = $dpto AND dp.cod_mcpio = $mcpio AND p.divipolepreconteo_id IS NULL
        GROUP BY dp.cod_dpto, dp.cod_mcpio, dp.mcpio, dp.cod_zona, dp.cod_puesto, dp.puesto");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'mesas_faltantes' => $faltantes
        );

        return response()->json($data, $data['code']);
    }

    public function mesas_faltantes_puesto(Request $request){

        $dpto = $request->cod_dpto;
        $mcpio = $request->cod_mcpio;
        $zona = $request->cod_zona;
        $puesto = $request->cod_puesto;

        $mesas = DB::select("SELECT dp.mesa
        FROM divipolepreconteos as dp 
        LEFT JOIN preconteos as p ON dp.id = p.divipolepreconteo_id 
        WHERE dp.cod_dpto = $dpto AND dp.cod_mcpio = $mcpio AND dp.cod_zona = '" .$zona. "' AND dp.cod_puesto = '" .$puesto. "' AND p.divipolepreconteo_id IS NULL");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'mesas' => $mesas
        );

        return response()->json($data, $data['code']);
    }

    public function preconteo_general(){

        $general = DB::select("SELECT dp.cod_dpto, dp.cod_mcpio, dp.cod_zona, dp.cod_puesto, dp.dpto, dp.mcpio, dp.puesto, dp.mesa, 
        p.id, p.numero_sufragantes, p.total_votos, p.numero_firmas, p.tachaduras, p.reconteo_votos, p.observaciones, p.created_at,
        pc.nombres, pc.apellidos, 
        pp.partido 
        FROM preconteos as p
        INNER JOIN divipolepreconteos as dp ON p.divipolepreconteo_id = dp.id
        INNER JOIN preconteocandidatos as pc ON p.preconteocandidato_id = pc.id
        INNER JOIN preconteopartidos as pp ON pc.preconteopartido_id = pp.id
        ORDER BY dp.cod_zona, dp.cod_puesto, dp.mesa ASC");

        $spreadsheet = IOFactory::load('plantillas/general_preconteo.xlsx');
        $sheet = $spreadsheet->getSheetByName('Hoja1');
        $fila = 2;
        
        for ($i=0; $i < count($general) ; $i++) { 
            $sheet->setCellValue("A$fila", $general[$i]->cod_dpto);
            $sheet->setCellValue("B$fila", $general[$i]->dpto);
            $sheet->setCellValue("C$fila", $general[$i]->cod_mcpio);
            $sheet->setCellValue("D$fila", $general[$i]->mcpio);
            $sheet->setCellValue("E$fila", $general[$i]->cod_zona);
            $sheet->setCellValue("F$fila", $general[$i]->cod_puesto);
            $sheet->setCellValue("G$fila", $general[$i]->puesto);
            $sheet->setCellValue("H$fila", $general[$i]->mesa);
            $sheet->setCellValue("I$fila", $general[$i]->partido);
            $sheet->setCellValue("J$fila", $general[$i]->nombres.' '.$general[$i]->apellidos);
            $sheet->setCellValue("K$fila", $general[$i]->total_votos);
            $sheet->setCellValue("L$fila", $general[$i]->observaciones);
            $sheet->setCellValue("M$fila", $general[$i]->numero_sufragantes);
            $sheet->setCellValue("N$fila", $general[$i]->numero_firmas);
            $sheet->setCellValue("O$fila", $general[$i]->tachaduras);
            $sheet->setCellValue("P$fila", $general[$i]->reconteo_votos);
            $sheet->setCellValue("Q$fila", $general[$i]->created_at);
            $fila++;
        }

        $filename = 'preconteo_general'.time().'.xls';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
    }

    public function puestos_informados($dpto, $mcpio){

        $puestos = DB::select("SELECT dp.cod_zona, dp.cod_puesto, dp.puesto FROM preconteos as p
        INNER JOIN divipolepreconteos as dp ON p.divipolepreconteo_id = dp.id
        WHERE dp.cod_dpto = $dpto AND dp.cod_mcpio = $mcpio
        GROUP BY dp.cod_zona, dp.cod_puesto, dp.puesto
        ORDER BY dp.cod_zona, dp.cod_puesto");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'puestos' => $puestos
        );

        return response()->json($data, $data['code']);
    }

    public function resultados_general($mcpio){

        $resultados = DB::select("SELECT pp.partido, pc.nombres, pc.apellidos, SUM(p.total_votos) as total, 
        (SELECT COUNT(*) FROM divipolepreconteos WHERE cod_mcpio = $mcpio) as total_mesas,
        (SELECT COUNT(DISTINCT divipolepreconteo_id) FROM preconteos  ) as mesas_informadas
        FROM preconteos as p 
        INNER JOIN divipolepreconteos as dp ON p.divipolepreconteo_id = dp.id
        INNER JOIN preconteocandidatos as pc ON p.preconteocandidato_id = pc.id
        INNER JOIN preconteopartidos as pp ON pc.preconteopartido_id = pp.id
        GROUP BY pp.partido, pc.nombres, pc.apellidos
        ORDER BY total DESC");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'resultados' => $resultados
        );

        return response()->json($data, $data['code']);

    }

    public function store(Request $request){
        
        $validator = \Validator::make($request->all(), [
            'divipolepreconteo_id' => 'required',
            'numero_sufragantes' => 'required|numeric',
            'numero_firmas' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $response_preconteo = array();

        for ($i=0; $i < count($request->preconteo); $i++) { 

            $preconteo = new Preconteo();
            $preconteo->divipolepreconteo_id = $request->divipolepreconteo_id;
            $preconteo->preconteocandidato_id = $request->preconteo[$i]['preconteocandidato_id'];
            $preconteo->numero_sufragantes = $request->numero_sufragantes;
            $preconteo->total_votos = $request->preconteo[$i]['total_votos'];
            $preconteo->numero_firmas = $request->numero_firmas;
            $preconteo->tachaduras = $request->tachaduras;
            $preconteo->reconteo_votos = $request->reconteo_votos;
            $preconteo->observaciones = $request->observaciones;
            $preconteo->user_id = Auth::user()->id;

            $preconteo->save();
            
            $response_preconteo[$i] = $preconteo;

        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'preconteo' => $response_preconteo
        );

        return response()->json($data, $data['code']);
        
    }

    public function update(Request $request){
        
        $validator = \Validator::make($request->all(), [
            'divipolepreconteo_id' => 'required',
            'numero_sufragantes' => 'required|numeric',
            'numero_firmas' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $response_preconteo = array();

        $delete_preconteo = Preconteo::where('divipolepreconteo_id', $request->divipolepreconteo_id)->delete();

        for ($i=0; $i < count($request->preconteo); $i++) {

            $preconteo = new Preconteo();
            $preconteo->divipolepreconteo_id = $request->divipolepreconteo_id;
            $preconteo->preconteocandidato_id = $request->preconteo[$i]['preconteocandidato_id'];
            $preconteo->numero_sufragantes = $request->numero_sufragantes;
            $preconteo->total_votos = $request->preconteo[$i]['total_votos'];
            $preconteo->numero_firmas = $request->numero_firmas;
            $preconteo->tachaduras = $request->tachaduras;
            $preconteo->reconteo_votos = $request->reconteo_votos;
            $preconteo->observaciones = $request->observaciones;
            $preconteo->user_id = Auth::user()->id;

            $preconteo->save();
            
            $response_preconteo[$i] = $preconteo;

        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'preconteo' => $response_preconteo
        );

        return response()->json($data, $data['code']);
        
    }

    public function votacion_mesa($id){

        $votacion = DB::select("SELECT dp.cod_dpto, dp.cod_mcpio, dp.dpto, dp.mcpio, dp.cod_zona, dp.cod_puesto, dp.puesto, dp.mesa, pc.nombres, pc.apellidos, p.* FROM preconteos AS p
        INNER JOIN divipolepreconteos as dp ON p.divipolepreconteo_id = dp.id
        INNER JOIN preconteocandidatos as pc ON p.preconteocandidato_id = pc.id
        WHERE p.divipolepreconteo_id = $id
        ORDER BY p.total_votos DESC");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'votacion' => $votacion
        );

        return response()->json($data, $data['code']);
    }
}
