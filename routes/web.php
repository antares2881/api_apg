<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\CoordinadoreController;
use App\Http\Controllers\CorporacioneController;
use App\Http\Controllers\ComunaController;
use App\Http\Controllers\FirmaController;
use App\Http\Controllers\LidereController;
use App\Http\Controllers\ListadovotanteController;
use App\Http\Controllers\PreconteoController;
use App\Http\Controllers\Estadisticas2022Controller;
use App\Http\Controllers\Estadisticas2023Controller;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\DepartamentoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('comunas/{mun}', [ComunaController::class, 'index']);
Route::get('corporaciones', [CorporacioneController::class, 'index']);
Route::get('imprimir-excel/{token}/{fecha_i}/{fecha_f}', [FirmaController::class, 'excel']);
Route::get('imprimir-votantes-general/{token}/{tipo}', [ListadovotanteController::class, 'excel_votantes_general']);
Route::get('imprimir-votantes-repetidos/{token}', [ListadovotanteController::class, 'excel_votantes_repetidos']);
Route::get('excel-lideres/{token}', [LidereController::class, 'excel_lideres']);
Route::get('excel-coordinadores/{token}/{coordinador}/{lider}/{sublider}', [CoordinadoreController::class, 'excel_coordinadores']);

route::get('reporte-esperados/{dpto}/{mcpio}/{zona}/{puesto}/{nombre_puesto}', [ListadovotanteController::class, 'votantes_esperados_puesto']);
route::get('reporte-confirmados/{dpto}/{mcpio}/{zona}/{puesto}/{nombre_puesto}/{comando_id}', [ListadovotanteController::class, 'votantes_confirmados_puesto']);
route::get('total-confirmados', [ListadovotanteController::class, 'votantes_confirmados']);
Route::get('reporte-faltantes/{dpto}/{mcpio}/{zona}/{puesto}/{nombre_puesto}', [ListadovotanteController::class, 'votantes_faltantes']);
Route::get('reporte-comandos/{id}/{comando}/{role}', [AsistenciaController::class, 'votantes_comando']);
Route::get('reporte-preconteo-general', [PreconteoController::class, 'preconteo_general']);

// Estadisticas locales 2023
Route::get('municipios/{cod_dpto}/{corporacion}', [Estadisticas2023Controller::class, 'getMunicipios']);
Route::get('corporaciones/{cod_dpto}', [Estadisticas2023Controller::class, 'getCorporacion']);
Route::get('estadisticas2023/{corporacion}/{municipio}/{comuna}/{puesto}/{mesa}/{partido}/{candidato}/{agrupacion}', [Estadisticas2023Controller::class, 'estadisticas2023']);
Route::get('puestosestadisticas/{departamento}/{municipio}', [Estadisticas2023Controller::class, 'getPuestos']);
Route::get('comunasestadisticas/{departamento}/{municipio}', [Estadisticas2023Controller::class, 'getComunas']);
Route::get('partidosestadisticas/{departamento}/{municipio}', [Estadisticas2023Controller::class, 'getPartidos']);
Route::get('mesasestadisticas/{puesto}', [Estadisticas2023Controller::class, 'getMesas']);
Route::get('candidatoestadisticas/{departamento}/{municipio}/{corporacion}/{partido}', [Estadisticas2023Controller::class, 'getCandidatos']);

//Estadisticas 2022
Route::get('corporaciones2022', [Estadisticas2022Controller::class, 'getCorporaciones']);
Route::get('partidos2022/{dpto}/{corporacion}', [Estadisticas2022Controller::class, 'getPartidos']);
Route::get('puestos2022/{dpto}/{mcpio}', [Estadisticas2022Controller::class, 'getPuestos']);
Route::get('candidatos2022/{dpto}/{corporacion}/{partido}', [Estadisticas2022Controller::class, 'getCandidatos']);
Route::get('/estadisticas2022/{corporacion}/{dpto}/{tipo_reporte}/{mun}/{par}/{can}/{puesto}', [Estadisticas2022Controller::class, 'estadisticas2022']);
Route::get('departamentos', [DepartamentoController::class, 'index']);
Route::get('municipios/{id}', [MunicipioController::class, 'show']);
