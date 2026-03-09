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
use App\Models\Preconteo_observacione;
use App\Models\Preconteo_votacione;

class PreconteoController extends Controller
{
    public function candidatos($corporacione_id){
        $candidatos = DB::select("SELECT * FROM preconteocandidatos WHERE corporacione_id = $corporacione_id");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'candidatos' => $candidatos
        );
        return response()->json($data, $data['code']);
    }

    public function departamentos(){
        $departamentos = DB::select("SELECT dpto FROM divipolepreconteos GROUP BY dpto, dpto ORDER BY dpto ASC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'departamentos' => $departamentos
        );
        return response()->json($data, $data['code']);
    }

    public function municipios($dpto){
        $municipios = DB::select("SELECT mcpio FROM divipolepreconteos WHERE dpto = '".$dpto."' GROUP BY mcpio, mcpio ORDER BY mcpio ASC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'municipios' => $municipios
        );
        return response()->json($data, $data['code']);
    }

    public function comunas($dpto, $mcpio){
        $comunas = DB::select("SELECT comuna FROM divipolepreconteos WHERE dpto = '".$dpto."' AND mcpio = '".$mcpio."' GROUP BY comuna, comuna ORDER BY comuna ASC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'comunas' => $comunas
        );
        return response()->json($data, $data['code']);
    }

    public function puestos($dpto, $mcpio){
        $puestos = DB::select("SELECT puesto FROM divipolepreconteos WHERE dpto = '".$dpto."' AND mcpio = '".$mcpio."' GROUP BY puesto, puesto ORDER BY puesto ASC");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'puestos' => $puestos
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

    public function mesas_faltantes_dpto($dpto){

        $faltantes = DB::select("SELECT dp.cod_mcpio, dp.mcpio, COUNT(dp.mesa) as faltantes 
        FROM divipolepreconteos as dp 
        LEFT JOIN preconteos as p ON dp.id = p.divipolepreconteo_id 
        WHERE dp.cod_dpto = $dpto AND p.divipolepreconteo_id IS NULL
        GROUP BY dp.cod_mcpio, dp.mcpio
        ORDER BY dp.cod_mcpio ASC");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'mesas_faltantes' => $faltantes
        );

        return response()->json($data, $data['code']);
    }

    public function mesas_faltantes_mcpio($dpto, $mcpio){

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
        p.id, p.numero_sufragantes, pv.total_votos, p.numero_firmas, p.tachaduras, p.reconteo_votos, p.observaciones, p.created_at,
        pc.nombres, pc.apellidos, 
        pp.partido 
        FROM preconteos as p
        INNER JOIN divipolepreconteos as dp ON p.divipolepreconteo_id = dp.id
        INNER JOIN preconteo_votaciones as pv ON p.id = pv.preconteo_id
        INNER JOIN preconteocandidatos as pc ON pv.preconteocandidato_id = pc.id
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

    public function resultados_general(Request $request, $corporacione_id){

        $dpto = $request->query('dpto');
        $mcpio = $request->query('mcpio');
        $puesto = $request->query('puesto');
        $comuna = $request->query('comuna');

        $filtro_main = '';
        $filtro_total_mesas = '';
        $filtro_mesas_informadas = '';

        if(!empty($dpto) && $dpto != -1 && $dpto != 'undefined'){
            $nom_dpto = str_replace("'", "''", $dpto);
            $filtro_main .= " AND dp.dpto = '$nom_dpto'";
            $filtro_total_mesas .= " AND dp_total.dpto = '$nom_dpto'";
            $filtro_mesas_informadas .= " AND dp2.dpto = '$nom_dpto'";
        }

        if(!empty($mcpio) && $mcpio != -1 && $mcpio != 'undefined'){
            $nom_mcpio = str_replace("'", "''", $mcpio);
            $filtro_main .= " AND dp.mcpio = '$nom_mcpio'";
            $filtro_total_mesas .= " AND dp_total.mcpio = '$nom_mcpio'";
            $filtro_mesas_informadas .= " AND dp2.mcpio = '$nom_mcpio'";
        }

        if(!empty($puesto) && $puesto != -1 && $puesto != 'undefined'){
            $nom_puesto = str_replace("'", "''", $puesto);
            $filtro_main .= " AND dp.puesto = '$nom_puesto'";
            $filtro_total_mesas .= " AND dp_total.puesto = '$nom_puesto'";
            $filtro_mesas_informadas .= " AND dp2.puesto = '$nom_puesto'";
        }

        if(!empty($comuna) && $comuna != -1 && $comuna != 'undefined'){
            $nom_comuna = str_replace("'", "''", $comuna);
            $filtro_main .= " AND dp.comuna = '$nom_comuna'";
            $filtro_total_mesas .= " AND dp_total.comuna = '$nom_comuna'";
            $filtro_mesas_informadas .= " AND dp2.comuna = '$nom_comuna'";
        }

        $resultados = DB::select("SELECT pp.partido, pc.nombres, pc.apellidos, SUM(pv.total_votos) as total
        FROM preconteos as p
        INNER JOIN divipolepreconteos as dp ON p.divipolepreconteo_id = dp.id
        INNER JOIN preconteo_votaciones as pv ON p.id = pv.preconteo_id
        INNER JOIN preconteocandidatos as pc ON pv.preconteocandidato_id = pc.id
        INNER JOIN preconteopartidos as pp ON pc.preconteopartido_id = pp.id
        WHERE pc.corporacione_id = $corporacione_id $filtro_main
        GROUP BY pp.partido, pc.nombres, pc.apellidos
        ORDER BY total DESC");

        $total_mesas_result = DB::select("SELECT COUNT(*) as total_mesas
        FROM divipolepreconteos as dp_total
        WHERE 1 = 1 $filtro_total_mesas");

        $mesas_informadas_result = DB::select("SELECT COUNT(DISTINCT p2.divipolepreconteo_id) as mesas_informadas
        FROM preconteos as p2
        INNER JOIN divipolepreconteos as dp2 ON p2.divipolepreconteo_id = dp2.id
        INNER JOIN preconteo_votaciones as pv2 ON p2.id = pv2.preconteo_id
        INNER JOIN preconteocandidatos as pc2 ON pv2.preconteocandidato_id = pc2.id
        WHERE pc2.corporacione_id = $corporacione_id $filtro_mesas_informadas");

        $total_mesas = (count($total_mesas_result) > 0) ? $total_mesas_result[0]->total_mesas : 0;
        $mesas_informadas = (count($mesas_informadas_result) > 0) ? $mesas_informadas_result[0]->mesas_informadas : 0;

        for($i = 0; $i < count($resultados); $i++){
            $resultados[$i]->total_mesas = $total_mesas;
            $resultados[$i]->mesas_informadas = $mesas_informadas;
        }

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
            'numero_firmas' => 'required|numeric',
            'preconteo' => 'required|array|min:1',
            'preconteo.*.preconteocandidato_id' => 'required|numeric',
            'preconteo.*.total_votos' => 'required|numeric',
            'observaciones_ids' => 'nullable|array',
            'observaciones_ids.*' => 'numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $response_preconteo = null;
        $response_votaciones = array();
        $response_observaciones = array();

        DB::transaction(function () use ($request, &$response_preconteo, &$response_votaciones, &$response_observaciones) {
            $preconteo = new Preconteo();
            $preconteo->divipolepreconteo_id = $request->divipolepreconteo_id;
            $preconteo->numero_sufragantes = $request->numero_sufragantes;
            $preconteo->numero_firmas = $request->numero_firmas;
            $preconteo->numero_incinerados = $request->numero_incinerados;
            $preconteo->tachaduras = $request->tachaduras;
            $preconteo->reconteo_votos = $request->reconteo_votos;
            $preconteo->observaciones = $request->observaciones;
            $preconteo->adjunto = $request->adjunto;
            $preconteo->user_id = Auth::user()->id;
            $preconteo->save();

            for ($i=0; $i < count($request->preconteo); $i++) {
                $votacion = new Preconteo_votacione();
                $votacion->preconteo_id = $preconteo->id;
                $votacion->preconteocandidato_id = $request->preconteo[$i]['preconteocandidato_id'];
                $votacion->total_votos = $request->preconteo[$i]['total_votos'];
                $votacion->save();

                $response_votaciones[$i] = $votacion;
            }

            if(!empty($request->observaciones_ids) && is_array($request->observaciones_ids)){
                for ($i=0; $i < count($request->observaciones_ids); $i++) {
                    $observacion = new Preconteo_observacione();
                    $observacion->preconteo_id = $preconteo->id;
                    $observacion->observacione_id = $request->observaciones_ids[$i];
                    $observacion->save();

                    $response_observaciones[$i] = $observacion;
                }
            }

            $response_preconteo = $preconteo;
        });

        $data = array(
            'status' => 'success',
            'code' => 200,
            'preconteo' => $response_preconteo,
            'votaciones' => $response_votaciones,
            'preconteo_observaciones' => $response_observaciones
        );

        return response()->json($data, $data['code']);
        
    }

    public function update(Request $request){
        
        $validator = \Validator::make($request->all(), [
            'divipolepreconteo_id' => 'required',
            'numero_sufragantes' => 'required|numeric',
            'numero_firmas' => 'required|numeric',
            'preconteo' => 'required|array|min:1',
            'preconteo.*.preconteocandidato_id' => 'required|numeric',
            'preconteo.*.total_votos' => 'required|numeric',
            'observaciones_ids' => 'nullable|array',
            'observaciones_ids.*' => 'numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $response_preconteo = null;
        $response_votaciones = array();
        $response_observaciones = array();

        DB::transaction(function () use ($request, &$response_preconteo, &$response_votaciones, &$response_observaciones) {
            $preconteo = Preconteo::where('divipolepreconteo_id', $request->divipolepreconteo_id)->first();

            if(empty($preconteo)){
                $preconteo = new Preconteo();
                $preconteo->divipolepreconteo_id = $request->divipolepreconteo_id;
            }

            $preconteo->numero_sufragantes = $request->numero_sufragantes;
            $preconteo->numero_firmas = $request->numero_firmas;
            $preconteo->numero_incinerados = $request->numero_incinerados;
            $preconteo->tachaduras = $request->tachaduras;
            $preconteo->reconteo_votos = $request->reconteo_votos;
            $preconteo->observaciones = $request->observaciones;
            $preconteo->adjunto = $request->adjunto;
            $preconteo->user_id = Auth::user()->id;
            $preconteo->save();

            Preconteo_votacione::where('preconteo_id', $preconteo->id)->delete();
            Preconteo_observacione::where('preconteo_id', $preconteo->id)->delete();

            for ($i=0; $i < count($request->preconteo); $i++) {
                $votacion = new Preconteo_votacione();
                $votacion->preconteo_id = $preconteo->id;
                $votacion->preconteocandidato_id = $request->preconteo[$i]['preconteocandidato_id'];
                $votacion->total_votos = $request->preconteo[$i]['total_votos'];
                $votacion->save();

                $response_votaciones[$i] = $votacion;
            }

            if(!empty($request->observaciones_ids) && is_array($request->observaciones_ids)){
                for ($i=0; $i < count($request->observaciones_ids); $i++) {
                    $observacion = new Preconteo_observacione();
                    $observacion->preconteo_id = $preconteo->id;
                    $observacion->observacione_id = $request->observaciones_ids[$i];
                    $observacion->save();

                    $response_observaciones[$i] = $observacion;
                }
            }

            $response_preconteo = $preconteo;
        });

        $data = array(
            'status' => 'success',
            'code' => 200,
            'preconteo' => $response_preconteo,
            'votaciones' => $response_votaciones,
            'preconteo_observaciones' => $response_observaciones
        );

        return response()->json($data, $data['code']);
        
    }

    public function votacion_mesa($id){

        $votacion = DB::select("SELECT dp.cod_dpto, dp.cod_mcpio, dp.dpto, dp.mcpio, dp.cod_zona, dp.cod_puesto, dp.puesto, dp.mesa, pc.nombres, pc.apellidos, pv.preconteocandidato_id, pv.total_votos,
        (SELECT GROUP_CONCAT(po.observacione_id ORDER BY po.observacione_id ASC)
            FROM preconteo_observaciones as po
            WHERE po.preconteo_id = p.id
        ) as observaciones_ids,
        (SELECT GROUP_CONCAT(o.observacion ORDER BY po2.observacione_id ASC SEPARATOR ' | ')
            FROM preconteo_observaciones as po2
            INNER JOIN observaciones as o ON po2.observacione_id = o.id
            WHERE po2.preconteo_id = p.id
        ) as observaciones_preconteo,
        p.* FROM preconteos AS p
        INNER JOIN divipolepreconteos as dp ON p.divipolepreconteo_id = dp.id
        INNER JOIN preconteo_votaciones as pv ON p.id = pv.preconteo_id
        INNER JOIN preconteocandidatos as pc ON pv.preconteocandidato_id = pc.id
        WHERE p.divipolepreconteo_id = $id
        ORDER BY pv.total_votos DESC");

        $data = array(
            'status' => 'success',
            'code' => 200,
            'votacion' => $votacion
        );

        return response()->json($data, $data['code']);
    }

    public function observaciones(Request $request, $id = null, $tipo = null){

        if($id === 'null' || $id === 'undefined' || $id === ''){
            $id = null;
        }

        if($id === 'excel' && is_null($tipo)){
            $tipo = 'excel';
            $id = null;
        }

        $sql = "SELECT p.*, dp.*, po.observacione_id, o.observacion, tv.total_votos
        FROM preconteos as p
        INNER JOIN divipolepreconteos as dp ON p.divipolepreconteo_id = dp.id
        INNER JOIN preconteo_observaciones as po ON p.id = po.preconteo_id
        INNER JOIN observaciones as o ON po.observacione_id = o.id
        LEFT JOIN (
            SELECT preconteo_id, SUM(total_votos) as total_votos
            FROM preconteo_votaciones
            GROUP BY preconteo_id
        ) as tv ON p.id = tv.preconteo_id
        ";

        $bindings = [];
        if(!is_null($id) && $id !== ''){
            $sql .= " WHERE po.observacione_id = ?";
            $bindings[] = $id;
        }

        $observaciones = DB::select($sql, $bindings);

        $data = array(
            'status' => 'success',
            'code' => 200,
            'observaciones' => $observaciones
        );

        $esExcelPorRuta = ($tipo === 'excel');

        if(!$esExcelPorRuta && ($request->ajax() || $request->expectsJson() || $request->wantsJson())){
            return response()->json($data, $data['code']);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Observaciones');

        $encabezados = [
            'Departamento',
            'Municipio',
            'Puesto',
            'Mesa',
            'Tiene tachaduras',
            'Tiene enmendaduras',
            'Reconteo de votos',
            'Numero de firmas',
            'Numero de sufragantes',
            'Total votos',
            'Observacion',
            'Observaciones'
        ];

        $sheet->fromArray($encabezados, null, 'A1');

        $fila = 2;
        foreach ($observaciones as $item) {
            $sheet->setCellValue("A{$fila}", $item->dpto ?? '');
            $sheet->setCellValue("B{$fila}", $item->mcpio ?? '');
            $sheet->setCellValue("C{$fila}", $item->puesto ?? '');
            $sheet->setCellValue("D{$fila}", $item->mesa ?? '');
            $sheet->setCellValue("E{$fila}", $item->tachaduras ?? '');
            $sheet->setCellValue("F{$fila}", $item->tachaduras );
            $sheet->setCellValue("G{$fila}", $item->reconteo_votos ?? '');
            $sheet->setCellValue("H{$fila}", $item->numero_firmas ?? '');
            $sheet->setCellValue("I{$fila}", $item->numero_sufragantes ?? '');
            $sheet->setCellValue("J{$fila}", $item->total_votos ?? '');
            $sheet->setCellValue("K{$fila}", $item->observacione_id ?? '');
            $sheet->setCellValue("L{$fila}", $item->observacion ?? '');
            $fila++;
        }

        foreach (range('A', 'L') as $columna) {
            $sheet->getColumnDimension($columna)->setAutoSize(true);
        }

        $filename = 'observaciones_'.time().'.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
