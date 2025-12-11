<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ApiurlController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\BarrioController;
use App\Http\Controllers\CandidatoController;
use App\Http\Controllers\ComandoController;
use App\Http\Controllers\CandidatoestadisticaController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\ComunaController;
use App\Http\Controllers\CoordinadoreController;
use App\Http\Controllers\CorporacioneController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\DivipoleController;
use App\Http\Controllers\DivipolepreconteoController;
use App\Http\Controllers\Estadistica2019Controller;
use App\Http\Controllers\Estadisticas2022Controller;
use App\Http\Controllers\FirmaController;
use App\Http\Controllers\HistoricomunicipioController;
use App\Http\Controllers\IpreporteController;
use App\Http\Controllers\JuradoController;
use App\Http\Controllers\LidereController;
use App\Http\Controllers\ListadovotanteController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\NeducativoController;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\PartidoestadisticaController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PreconteoController;
use App\Http\Controllers\ProfesioneController;
use App\Http\Controllers\RecolectoreController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubcoordinadorController;
use App\Http\Controllers\SublidereController;
use App\Http\Controllers\TestigoController;
use App\Http\Controllers\TestigosmesaController;
use App\Http\Controllers\TipoempleadoController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\UsuarioController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [UserController::class, 'userLogin']);
Route::get('unauthorized',function(Request $r){
    $data = array(
        'status' => 'unauthorized',
        'code' => 401,
        'message' => 'El token expiro, logueate nuevamente'
    );
    return response()->json($data, $data['code']);
})->name('api.unauthorized');

Route::group(['middleware' => 'auth:api'], function(){

    //Asistencia
    Route::get('asistencia/{ced}', [AsistenciaController::class, 'verificarAsistencia']);
    Route::post('dar_asistencia', [AsistenciaController::class, 'DarAsistencia']);
    Route::get('asistentes-comandos/{comando_id}', [AsistenciaController::class, 'asistenciaComandos']);
    Route::post('votantes-confirmados', [AsistenciaController::class, 'votantes_confirmados']);
    Route::post('votantes-faltantes', [AsistenciaController::class, 'votantes_faltantes']);

    Route::get('profile-decoded', [UserController::class, 'userDecoded']);    
    Route::get('api-url', [ApiurlController::class, 'get']);

    Route::get('agendas/{fecha}', [AgendaController::class, 'show']);
    Route::get('fecha_cumple/{fecha}', [AgendaController::class, 'fechaCumple']);
    Route::post('agendas', [AgendaController::class, 'store']);
    Route::put('agendas/{id}', [AgendaController::class, 'update']);

    Route::get('barrios', [BarrioController::class, 'index']);
    Route::get('calendarios/{anio}/{mes}', [CalendarioController::class, 'index']);
    Route::post('calendarios', [CalendarioController::class, 'shows']);

    Route::post('change-password', [UsuarioController::class, 'change_password']);
    
    Route::get('cargos', [CargoController::class, 'index']);

    Route::get('corporaciones', [CorporacioneController::class, 'index']);


    Route::get('comandos', [ComandoController::class, 'index']);
    Route::post('comandos', [ComandoController::class, 'store']);
    Route::put('comandos/{id}', [ComandoController::class, 'update']);

    //Candidatos preconteo
    Route::get('candidatos-preconteo/{municipio}', [PreconteoController::class, 'candidatos']);

    Route::get('mi-candidato', [CandidatoController::class, 'miCandidato']);
    
    Route::get('candidatos', [CandidatoController::class, 'index']);
    Route::get('candidatos/{id}', [CandidatoController::class, 'show']);
    Route::post('candidatos', [CandidatoController::class, 'store']);
    Route::put('candidatos/{id}', [CandidatoController::class, 'update']);
    route::get('votantesxcandidato/{corporacione_id}/{candidato_id}', [CandidatoController::class, 'votantes']);

    Route::get('candidatoestadisticas/{corporacion}/{municipio}/{partido}', [CandidatoestadisticaController::class, 'getCandidatos']);

    Route::get('comunas/{dpto}/{mcpio}', [ComunaController::class, 'index']);

    //Coordinadores
    Route::get('coordinadores/{candidato}', [CoordinadoreController::class, 'index']);
    Route::get('coordinadores/{coordinadore_id}/{candidato}', [CoordinadoreController::class, 'getLideres']);
    Route::get('info-coordinadores/{id}/{agrupacion}', [CoordinadoreController::class, 'infoCoordinadores']);
    Route::get('coordinadores-ingresados/{id}', [CoordinadoreController::class, 'show']);
    Route::post('coordinadores', [CoordinadoreController::class, 'store']);
    Route::put('coordinadores/{id}', [CoordinadoreController::class, 'update']);
    Route::delete('coordinadores/{id}', [CoordinadoreController::class, 'destroy']);

    Route::get('departamentos', [DepartamentoController::class, 'index']);
    

    //Divipoles
    Route::get('puestos_divipoles/{dpto}/{mcpio}/{comando_id}', [DivipoleController::class, 'puestos_divipoles']);
    Route::post('divipoles', [DivipoleController::class, 'index']);
    
    //Divipole preconteos
    Route::get('divipole-preconteos/{dpto}/{mcpio}', [DivipolepreconteoController::class, 'puestos_divipoles']);
    Route::post('mostrar-mesas-preconteo', [DivipolepreconteoController::class, 'mostrar_mesas']);

    //Estadisticas 2019
    Route::get('zonasestadisticas/{municipio}', [Estadistica2019Controller::class, 'getZonas']);
    Route::get('puestosestadisticas/{municipio}/{zona}', [Estadistica2019Controller::class, 'getPuestos']);
    Route::post('estadisticas2019', [Estadistica2019Controller::class, 'estadisticas2019']);

    //Estadisticas 2022
    Route::get('corporaciones2022', [Estadisticas2022Controller::class, 'getCorporaciones']);
    Route::get('partidos2022/{dpto}/{corporacion}', [Estadisticas2022Controller::class, 'getPartidos']);
    Route::get('candidatos2022/{dpto}/{corporacion}/{partido}', [Estadisticas2022Controller::class, 'getCandidatos']);
    Route::post('estadisticas2022', [Estadisticas2022Controller::class, 'estadisticas2022']);
   
    //Divipoles
    Route::get('zonas/{dpto}/{mcpio}', [DivipoleController::class, 'zonas']);
    Route::get('puestos/{dpto}/{mcpio}/{zona}', [DivipoleController::class, 'puestos']);
    Route::get('count-puestos/{dpto}/{mcpio}', [DivipoleController::class, 'countPuestos']);
    Route::get('count-comandos', [DivipoleController::class, 'countComandos']);

    Route::post('finalizar-sesion', [UserController::class, 'destroySession']);

    Route::get('firmas/{id}/{candidato}', [FirmaController::class, 'show']);
    Route::post('firmas', [FirmaController::class, 'store']);
    
    //Historico elecciones
    Route::post('historico-municipios', [HistoricomunicipioController::class, 'historico']);

    Route::get('jurados', [JuradoController::class, 'index']);
    Route::get('jurados-agrupados', [JuradoController::class, 'agrupados']);
    Route::get('jurados/{cedula}', [JuradoController::class, 'show']);
    Route::post('jurados', [JuradoController::class, 'store']);
    Route::put('jurados/{id}', [JuradoController::class, 'update']);
    
    Route::get('lideres/{candidato}', [LidereController::class, 'index']);
    Route::get('lideres-militantes/{id}/{candidato}/{tipo}', [LidereController::class, 'numMilitantes']);
    Route::get('lideres/{coordinador}/{candidato}', [LidereController::class, 'getLideresReportes']);
    Route::get('sublideres/{id}/{candidato}', [LidereController::class, 'getSubLideres']);
    Route::get('lideresVotantes/{id}', [LidereController::class, 'getLideresVotantes']);
    Route::delete('lideresVotantes/{id}', [LidereController::class, 'destroy']);
    Route::get('lideres-ingresados/{id}', [LidereController::class, 'show']);
    Route::post('lideres', [LidereController::class, 'store']);
    Route::put('lideres/{id}', [LidereController::class, 'update']);
    
    Route::get('listadovotantes', [ListadovotanteController::class, 'index']);
    Route::post('votantes-repetidos', [ListadovotanteController::class, 'repetidos']);
    Route::get('contar-votantes', [ListadovotanteController::class, 'contarVotantes']);
    Route::get('buscar-votante-dpto/{id}', [ListadovotanteController::class, 'buscarVotanteDpto']);
    Route::get('ver-votantes/{opcion}', [ListadovotanteController::class, 'ver_votantes_general']);
    Route::post('votantes-jurados', [ListadovotanteController::class, 'votantesxJurado']);
    Route::post('ver-votantes', [ListadovotanteController::class, 'ver_votantes']);
    Route::post('listadovotantes', [ListadovotanteController::class, 'store']);
    Route::put('listadovotantes/{id}', [ListadovotanteController::class, 'update']);
    Route::delete('listadovotantes/{id}', [ListadovotanteController::class, 'delete']);
    route::post('votantesxpuesto', [ListadovotanteController::class, 'votantesxPuesto']);
    route::post('listadovotantes-usuarios', [ListadovotanteController::class, 'votantesxusuario']);
    route::get('puestosxvotantes', [ListadovotanteController::class, 'puestosxVotantesAgregados']);
    route::get('votantesxdepartamento', [ListadovotanteController::class, 'votantes_por_dpto']);
    route::get('votantesxmunicipio/{dpto_id}', [ListadovotanteController::class, 'votantes_por_mcpios']);
    route::get('votantesxmcpio/{dpto_id}/{mcpio_id}', [ListadovotanteController::class, 'votantes_por_mcpio']);

    Route::get('municipios/{id}', [MunicipioController::class, 'show']);

    Route::post('metasvotantes', [MetaController::class, 'metasVotantes']);
    Route::post('metas', [MetaController::class, 'store']);
    Route::put('metas/{id}', [MetaController::class, 'update']);

    Route::get('neducativos', [NeducativoController::class, 'index']);

    Route::get('partidos', [PartidoController::class, 'index']);
    Route::get('partidos/{id}', [PartidoController::class, 'show']);

    Route::get('partidoestadisticas', [PartidoestadisticaController::class, 'index']);

    Route::get('personals', [PersonalController::class, 'index']);
    Route::get('personal-cargo/{cargo}', [PersonalController::class, 'personalXCargo']);
    Route::post('personals', [PersonalController::class, 'store']);
    Route::put('personals/{id}', [PersonalController::class, 'update']);

    //Preconteo
    Route::post('preconteo', [PreconteoController::class, 'store']);
    Route::post('edit-preconteo', [PreconteoController::class, 'update']);
    Route::get('preconteo-resultados-general/{mcpio}', [PreconteoController::class, 'resultados_general']);
    Route::get('preconteo-puestos-informados/{dpto}/{mcpio}', [PreconteoController::class, 'puestos_informados']);
    Route::post('preconteo-mostrar-mesas', [PreconteoController::class, 'mesas_informadas']);
    Route::get('preconteo-votacion-mesa/{id}', [PreconteoController::class, 'votacion_mesa']);
    Route::get('preconteo-mesas-faltantes/{dpto}/{mcpio}', [PreconteoController::class, 'mesas_faltantes']);
    Route::post('preconteo-mesas-faltantes', [PreconteoController::class, 'mesas_faltantes_puesto']);

    Route::get('profesiones', [ProfesioneController::class, 'index']);
    Route::post('profesiones', [ProfesioneController::class, 'store']);

    Route::get('recolectores/{candidato}', [RecolectoreController::class, 'index']);
    Route::post('recolectores', [RecolectoreController::class, 'store']);
    Route::put('recolectores/{id}/update', [RecolectoreController::class, 'update']);
    Route::get('roles', [RoleController::class, 'index']);

    //Reportes
    Route::get('reporte-firmas-general/{candidato}', [ReportesController::class, 'reporteFirmasGeneral']);
    Route::post('reporte-firmas-usuarios', [ReportesController::class, 'reporteFirmasUsuarios']);
    Route::post('reporte-firmas-repetidos', [ReportesController::class, 'reporteFirmasRepetidos']);
    Route::post('reporte-firmas-noregion', [ReportesController::class, 'reporteFirmasNoRegion']);
    Route::post('reporte-firmas-recolectores', [ReportesController::class, 'reporteFirmasRecolector']);
    Route::post('reporte-firmas-validas', [ReportesController::class, 'reporteFirmasValidas']);
    Route::post('reporte-firmas-novalidas', [ReportesController::class, 'reporteFirmasNoValidas']);
    Route::get('reporte-votantes-repetidos/{corporacion}', [ReportesController::class, 'reporteVotantesRepetidos']);

    //Reporte de IPS
    Route::get('ipreportes', [IpreporteController::class, 'index']);

    Route::get('subcoordinadores/{candidato}', [SubcoordinadorController::class, 'index']);
    Route::post('subcoordinadores', [SubcoordinadorController::class, 'store']);
    Route::put('subcoordinadores/{id}', [SubcoordinadorController::class, 'update']);   
    
    //Sublideres
    Route::get('sublideres/{lidere_id}', [SublidereController::class, 'getSublideres']);

    Route::get('testigos', [TestigoController::class, 'index']);
    Route::post('testigos', [TestigoController::class, 'store']);
    Route::post('testigos-puesto', [TestigoController::class, 'testigos_puesto']);
    Route::put('testigos/{id}', [TestigoController::class, 'update']);
    Route::delete('testigos/{id}', [TestigoController::class, 'destroy']);

    Route::post('testigosmesas', [TestigosmesaController::class, 'store']);

    Route::get('tipoempleados', [TipoempleadoController::class, 'index']);

    Route::get('turnos/{fecha}', [TurnoController::class, 'show']);

    //Users
    Route::get('users', [UsuarioController::class, 'index']);
    Route::get('users/{corporacion}', [UsuarioController::class, 'show']);
    Route::post('users', [UsuarioController::class, 'store']);
    Route::put('users/{id}', [UsuarioController::class, 'update']);

    Route::get('/votacion_por_municipio/{corporacion?}', [Estadisticas2022Controller::class, 'votacion_por_municipio']);
    Route::get('/regiones', [Estadisticas2022Controller::class, 'regiones']);
});
