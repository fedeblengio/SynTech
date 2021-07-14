<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\User;
use App\Http\Controllers;
use Carbon\Carbon;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/login','App\Http\Controllers\loginController@connect');


Route::get('/test', function (){

 return "Estas en /test";
});


//usuarios
Route::put('/usuario','App\Http\Controllers\usuariosController@update')->middleware('verificar_token');

//foros
Route::get('/foros','App\Http\Controllers\ProfesorEscribeForo@index')->middleware('verificar_token');
Route::get('/foro','App\Http\Controllers\ProfesorEscribeForo@show')->middleware('verificar_token');
Route::get('/archivo','App\Http\Controllers\ProfesorEscribeForo@traerArchivo')->middleware('verificar_token');
Route::post('/foro','App\Http\Controllers\ProfesorEscribeForo@store')->middleware('verificar_token');
Route::put('/foro','App\Http\Controllers\ProfesorEscribeForo@update')->middleware('verificar_token');
Route::delete('/foro','App\Http\Controllers\ProfesorEscribeForo@destroy')->middleware('verificar_token');


Route::get('/profesor-grupo','App\Http\Controllers\ProfesorGrupo@listarProfesorGrupo')->middleware('verificar_token');


Route::get('/alumno','App\Http\Controllers\alumnos@show')->middleware('verificar_token');

Route::get('/profesor-foro','App\Http\Controllers\ProfesorEscribeForo@index')->middleware('verificar_token');

Route::get('/foro-grupo','App\Http\Controllers\ProfesorGrupo@listarDatosForo')->middleware('verificar_token');



//tareas
Route::get('/traerTareasGrupo','App\Http\Controllers\ProfesorCreaTarea@traerTareasGrupo')->middleware('verificar_token');
Route::get('/traerArchivo','App\Http\Controllers\ProfesorCreaTarea@traerArchivo')->middleware('verificar_token');
Route::post('/tarea','App\Http\Controllers\ProfesorCreaTarea@store')->middleware('verificar_token');
Route::get('/tareas','App\Http\Controllers\ProfesorCreaTarea@show')->middleware('verificar_token');

//alumnos
Route::get('/alumnosTarea','App\Http\Controllers\AlumnoEntregaTarea@index')->middleware('verificar_token');
Route::get('/alumnoTarea','App\Http\Controllers\AlumnoEntregaTarea@traerTareasMateria')->middleware('verificar_token');
Route::post('/alumnoTarea','App\Http\Controllers\AlumnoEntregaTarea@store')->middleware('verificar_token');
Route::get('/archivoAlumnoTarea','App\Http\Controllers\AlumnoEntregaTarea@traerArchivo')->middleware('verificar_token');
Route::put('/alumnoTarea','App\Http\Controllers\AlumnoEntregaTarea@update')->middleware('verificar_token');


