<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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

// LOGIN
Route::post('/login', 'App\Http\Controllers\loginController@connect'); 
//

Route::middleware(['verificar_token'])->group(function () {

  // GET AND POST PROFILE IMAGE
  Route::post('/imagen-perfil', 'App\Http\Controllers\loginController@cargarImagen');
  Route::get('/imagen-perfil/{id}', 'App\Http\Controllers\loginController@traerImagen');

  // FTP GET FILE
  Route::get('/archivo/{archivo}', 'App\Http\Controllers\ProfesorEscribeForo@traerArchivo'); 
  //USUARIO
  Route::put('/usuario/{id}/contrasenia', 'App\Http\Controllers\usuariosController@changePassword');
  Route::put('/usuario/{id}', 'App\Http\Controllers\usuariosController@updateUserInfo');
  Route::get('/usuario/{id}','App\Http\Controllers\usuariosController@show');
  Route::get('/usuario/{id}/grupo', 'App\Http\Controllers\ProfesorEscribeForo@traerGrupos');
  //FORO PRINCIPAL PUBLICACIONES
  Route::get('/foro', 'App\Http\Controllers\ProfesorEscribeForo@show');
  Route::post('/foro', 'App\Http\Controllers\ProfesorEscribeForo@store');
  Route::delete('/foro/{id}','App\Http\Controllers\ProfesorEscribeForo@destroy');

  //AGENDA CLASE VIRTUAL
  Route::get('/agenda-clase/usuario/{id}/grupo/{idGrupo}', 'App\Http\Controllers\AgendaClaseVirtualController@show');
  Route::get('/agenda-clase/profesor/{idProfesor}/grupo/{idGrupo}/materia', 'App\Http\Controllers\AgendaClaseVirtualController@getMateriasFromProfesorGrupo');
  Route::post('/agenda-clase', 'App\Http\Controllers\AgendaClaseVirtualController@store');
  Route::delete('/agenda-clase/{id}', 'App\Http\Controllers\AgendaClaseVirtualController@destroy');
  // EVENTOS
  Route::get('/evento/usuario/{id}', 'App\Http\Controllers\AgendaClaseVirtualController@consultaEventos');

  // NOTICIA
  Route::post('/noticia','App\Http\Controllers\materialPublicoController@store');
  Route::delete('/noticia/{id}','App\Http\Controllers\materialPublicoController@destroy'); 

  // GRUPO
  Route::get('/grupo/{id}/materia', 'App\Http\Controllers\ProfesorGrupo@listarMateriasGrupo');
  Route::get('/grupo/{idGrupo}/materia/{idMateria}/usuarios', 'App\Http\Controllers\GrupoController@listarAlumnos');
  
  Route::get('/grupo/{idGrupo}/materia/{idMateria}/usuarios/{idUsuario}/tarea', 'App\Http\Controllers\ProfesorCreaTarea@listarTareas');
  Route::post('/tarea', 'App\Http\Controllers\ProfesorCreaTarea@store');
  Route::delete('/tarea/{id}', 'App\Http\Controllers\ProfesorCreaTarea@destroy'); 

});


// TAREAS
Route::get('/tareas-corregir', 'App\Http\Controllers\ProfesorCreaTarea@tareasParaCorregir')->middleware('verificar_token'); //SE UsA
Route::get('/tarea', 'App\Http\Controllers\ProfesorCreaTarea@traerTarea')->middleware('verificar_token'); //SE UsA


Route::post('/entregas-alumno', 'App\Http\Controllers\AlumnoEntregaTarea@seleccion')->middleware('verificar_token'); //SE UsA
Route::get('/notas-alumno', 'App\Http\Controllers\AlumnoEntregaTarea@TareaNotaAlumnoMateria')->middleware('verificar_token'); //SE UsA
//

// ENTREGAS 
Route::get('/entregas-grupo', 'App\Http\Controllers\AlumnoEntregaTarea@listarEntregas')->middleware('verificar_token'); //SE UsA
Route::get('/entregas-alumnos', 'App\Http\Controllers\AlumnoEntregaTarea@listarEntregasAlumno')->middleware('verificar_token'); //SE UsA
Route::get('/entregas-alumno', 'App\Http\Controllers\AlumnoEntregaTarea@entregaAlumno')->middleware('verificar_token'); //SE UsA
Route::put('/entregas-correccion', 'App\Http\Controllers\AlumnoEntregaTarea@verificar_correcion')->middleware('verificar_token'); //SE UsA
Route::get('/visualizar-entrega', 'App\Http\Controllers\AlumnoEntregaTarea@visualizarEntrega')->middleware('verificar_token'); //SE UsA
Route::get('/promedio', 'App\Http\Controllers\AlumnoEntregaTarea@promedioMateria')->middleware('verificar_token'); //SE UsA
//



// LISTA CLASE VIRTUAL
Route::post('/lista-clase', 'App\Http\Controllers\GrupoController@store')->middleware('verificar_token'); //SE UsA
Route::get('/lista-clase', 'App\Http\Controllers\GrupoController@mostrarFaltasTotalesGlobal')->middleware('verificar_token'); //SE UsA
Route::get('/registro-clase', 'App\Http\Controllers\GrupoController@registroClase'); //SE UsA
Route::put('/lista-clase', 'App\Http\Controllers\GrupoController@update')->middleware('verificar_token'); //SE UsA
Route::get('/registro-alumno', 'App\Http\Controllers\GrupoController@registroAlumno')->middleware('verificar_token');
Route::get('/registro-listas', 'App\Http\Controllers\GrupoController@index')->middleware('verificar_token'); //SE UsA
//

// ALUMNOS 
/* Route::get('/alumnosTarea', 'App\Http\Controllers\AlumnoEntregaTarea@index')->middleware('verificar_token');
Route::get('/alumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@traerTareasMateria')->middleware('verificar_token');
Route::post('/alumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@store')->middleware('verificar_token');
Route::get('/archivoAlumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@traerArchivo')->middleware('verificar_token');
Route::put('/alumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@update')->middleware('verificar_token');
Route::get('/alumno', 'App\Http\Controllers\alumnos@show')->middleware('verificar_token'); */
//




Route::get('/noticia','App\Http\Controllers\materialPublicoController@index'); // ENDPOINT PUBLICO