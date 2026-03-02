<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Divipole;

class DivipoleController extends Controller
{
    public function countComandos(){
        $num_comandos = DB::select("SELECT COUNT(nombre) as num_comandos FROM comandos");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'num_comandos' => $num_comandos
        );
        return response()->json($data, $data['code']);
    }

    public function countPuestos($dpto, $mcpio){
        $num_puestos = DB::select("SELECT COUNT(puesto) as num_puestos FROM divipoles WHERE departamento_id = $dpto AND municipio_id = $mcpio");
        $data = array(
            'status' => 'success',
            'code' => 200,
            'num_puestos' => $num_puestos
        );
        return response()->json($data, $data['code']);
    }

    public function index(Request $request){

        $dpto = $request->dpto;
        $condicion = "";
        $condicionLv = "";

        // Condicion para elecciones congreso - senado
        if($request->corporacion == 3){
            $divipoles = DB::select("SELECT dp.departamento, d.departamento_id, SUM(d.mesas) as mesas, SUM(d.num_hombres) as num_hombres, SUM(d.num_mujeres) as num_mujeres, 
            (SELECT COUNT(*) FROM listadovotantes as lv WHERE lv.departamento_id = d.departamento_id AND lv.observacione_id = 1 ) as votantes
            FROM divipoles as d 
            INNER JOIN departamentos as dp ON d.departamento_id = dp.id
            GROUP BY d.departamento_id, dp.departamento");
        }else if($request->corporacion == 1 || $request->corporacion == 4 || $request->corporacion == 5){

            $mcpio = $request->mcpio;
            $condicion = " AND d.municipio_id = $mcpio";
            $condicionLv = "";

            //Si la campaña es de concejo
            if($request->corporacion == 5){
                $candidato = $request->candidato;
                $condicionLv = " AND lv.candidato_id = $candidato";
            }
            $divipoles = DB::select("SELECT dp.departamento, d.departamento_id, d.municipio_id, m.municipio, d.zona, d.puesto, d.nombre_puesto, d.mesas, d.num_hombres, d.num_mujeres, 
            (SELECT COUNT(*) FROM listadovotantes as lv WHERE lv.departamento_id = d.departamento_id AND lv.municipio_id = d.municipio_id AND lv.zona = d.zona AND lv.puesto = d.puesto AND lv.observacione_id = 1  $condicionLv) as votantes
            FROM divipoles as d 
            INNER JOIN departamentos as dp ON d.departamento_id = dp.id
            INNER JOIN municipios as m ON d.departamento_id = m.departamento_id AND d.municipio_id = m.id
            WHERE d.departamento_id = $dpto $condicion ");

        }

        $data = array(
            'status' => 'success',
            'code' => 200,
            'divipoles' => $divipoles
        );

        return response()->json($data, $data['code']);
    }

    public function zonas($dpto, $mcpio){
        $zonas = Divipole::select('zona')
        ->where([
            'departamento_id' => $dpto,
            'municipio_id' => $mcpio
        ])->groupBy('zona')->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'zonas' => $zonas
        );
        return response()->json($data, $data['code']);
    }

    public function puestos_divipoles($dpto, $mcpio, $comando_id){

        $dpto = (int) $dpto;
        $mcpio = (int) $mcpio;
        $comandoId = is_null($comando_id) ? null : (int) $comando_id;

        $condicionComando = "";
        $params = [];

        if (!is_null($comandoId) && $comandoId > 0) {
            $condicionComando = " AND a.comando_id = ?";
            $params[] = $comandoId;
        }

        if ($dpto === -1 && $mcpio === -1) {
            $sql = "SELECT d.departamento_id, dp.departamento, COUNT(a.id) as confirmados,
                    (SELECT COUNT(DISTINCT lv.id)
                        FROM listadovotantes as lv
                        WHERE lv.departamento_id = d.departamento_id
                          AND lv.observacione_id = 1) as esperados
                    FROM divipoles as d
                    INNER JOIN departamentos as dp ON d.departamento_id = dp.id
                    LEFT JOIN asistencias as a ON d.departamento_id = a.departamento_id
                        AND d.municipio_id = a.municipio_id
                        AND d.zona = a.zona
                        AND d.puesto = a.puesto{$condicionComando}
                    GROUP BY d.departamento_id, dp.departamento
                    ORDER BY dp.departamento";
        } elseif ($dpto > 0 && $mcpio === -1) {
            $sql = "SELECT d.departamento_id, d.municipio_id, m.municipio, COUNT(a.id) as confirmados,
                    (SELECT COUNT(DISTINCT lv.id)
                        FROM listadovotantes as lv
                        WHERE lv.departamento_id = d.departamento_id
                          AND lv.municipio_id = d.municipio_id
                          AND lv.observacione_id = 1) as esperados
                    FROM divipoles as d
                    INNER JOIN municipios as m ON d.departamento_id = m.departamento_id AND d.municipio_id = m.id
                    LEFT JOIN asistencias as a ON d.departamento_id = a.departamento_id
                        AND d.municipio_id = a.municipio_id
                        AND d.zona = a.zona
                        AND d.puesto = a.puesto{$condicionComando}
                    WHERE d.departamento_id = ?
                    GROUP BY d.departamento_id, d.municipio_id, m.municipio
                    ORDER BY m.municipio";
            $params[] = $dpto;
        } else {
            $sql = "SELECT d.zona, d.puesto, d.nombre_puesto, d.mesas, COUNT(a.id) as confirmados,
                    (SELECT COUNT(DISTINCT lv.id)
                        FROM listadovotantes as lv
                        WHERE lv.departamento_id = d.departamento_id
                          AND lv.municipio_id = d.municipio_id
                          AND lv.zona = d.zona
                          AND lv.puesto = d.puesto
                          AND lv.observacione_id = 1) as esperados
                    FROM divipoles as d
                    LEFT JOIN asistencias as a ON d.departamento_id = a.departamento_id
                        AND d.municipio_id = a.municipio_id
                        AND d.zona = a.zona
                        AND d.puesto = a.puesto{$condicionComando}
                    WHERE d.departamento_id = ? AND d.municipio_id = ?
                    GROUP BY d.zona, d.puesto, d.nombre_puesto, d.mesas, d.departamento_id, d.municipio_id
                    ORDER BY d.zona, d.puesto";
            $params[] = $dpto;
            $params[] = $mcpio;
        }

        $puestos = DB::select($sql, $params);

        $data = array(
            'status' => 'success',
            'code' => 200,
            'puestos' => $puestos
        );

        return response()->json($data, $data['code']);
    }

    public function puestos($dpto, $mcpio, $zona){
        $puestos = Divipole::select('puesto', 'nombre_puesto')
        ->where([
            'departamento_id' => $dpto,
            'municipio_id' => $mcpio,
            'zona' => $zona
        ])->groupBy('puesto', 'nombre_puesto')->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'puestos' => $puestos
        );
        return response()->json($data, $data['code']);
    }
}
