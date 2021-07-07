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
Route::put('/usuario','App\Http\Controllers\usuariosController@update');

//foros
Route::get('/foros','App\Http\Controllers\ProfesorEscribeForo@index');
Route::get('/foro','App\Http\Controllers\ProfesorEscribeForo@show');
Route::get('/archivo','App\Http\Controllers\ProfesorEscribeForo@traerArchivo');
Route::post('/foro','App\Http\Controllers\ProfesorEscribeForo@store');
Route::put('/foro','App\Http\Controllers\ProfesorEscribeForo@update');
Route::delete('/foro','App\Http\Controllers\ProfesorEscribeForo@destroy');


Route::get('/profesor-grupo','App\Http\Controllers\ProfesorGrupo@listarProfesorGrupo');


Route::get('/alumno','App\Http\Controllers\alumnos@show');

Route::get('/profesor-foro','App\Http\Controllers\ProfesorEscribeForo@index');

Route::get('/foro-grupo','App\Http\Controllers\ProfesorGrupo@listarDatosForo');



//tareas
Route::get('/traerTareasGrupo','App\Http\Controllers\ProfesorCreaTarea@traerTareasGrupo');
Route::get('/traerArchivo','App\Http\Controllers\ProfesorCreaTarea@traerArchivo');
Route::post('/tarea','App\Http\Controllers\ProfesorCreaTarea@store');
Route::get('/tareas','App\Http\Controllers\ProfesorCreaTarea@show');

//alumnos
Route::get('/alunos','App\Http\Controllers\AlumnoEntregaTarea@index');
Route::get('/alumno','App\Http\Controllers\AlumnoEntregaTarea@traerTareasMateria');
Route::post('/alumno','App\Http\Controllers\AlumnoEntregaTarea@store');
Route::get('/archivoAlumno','App\Http\Controllers\AlumnoEntregaTarea@traerArchivo');
Route::put('/alumno','App\Http\Controllers\AlumnoEntregaTarea@update');


