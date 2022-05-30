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

// TESTING CODE SIMPLE NOTE
Route::get('/test', function () {
  return  "sadasd";
});
//



// FTP SAVE FILE
Route::post('/FTP', 'App\Http\Controllers\ProfesorEscribeForo@guardarArchivoFTP')->middleware('verificar_token');
Route::get('/traerArchivo', 'App\Http\Controllers\ProfesorEscribeForo@traerArchivo')->middleware('verificar_token'); 
//

// LOGIN
Route::post('/login', 'App\Http\Controllers\loginController@connect');
//


// USUARIOS
//LOGIN
Route::get('/login', 'App\Http\Controllers\loginController@connect');
Route::post('/imagen-perfil', 'App\Http\Controllers\loginController@cargarImagen')->middleware('verificar_token');
// TRAER IMAGEN FTP B64
Route::get('/imagen-perfil', 'App\Http\Controllers\loginController@traerImagen')->middleware('verificar_token');
// CAMBIAR CONTRASEÑA
Route::put('/usuario', 'App\Http\Controllers\usuariosController@update')->middleware('verificar_token');
// CAMBIAR EMAIL Y GENERO
Route::put('/usuario-db', 'App\Http\Controllers\usuariosController@update_db')->middleware('verificar_token');
// LISTAR TODOS DATOS DE UN USUARIO 
Route::get('/usuario','App\Http\Controllers\usuariosController@show')->middleware('verificar_token');


//FORO PRINCIPAL PUBLICACIONES  
Route::get('/foros', 'App\Http\Controllers\ProfesorEscribeForo@index')->middleware('verificar_token');

Route::get('/foro', 'App\Http\Controllers\ProfesorEscribeForo@show')->middleware('verificar_token');
Route::post('/foro', 'App\Http\Controllers\ProfesorEscribeForo@guardarPublicacionBD')->middleware('verificar_token');
/* Route::put('/foro','App\Http\Controllers\ProfesorEscribeForo@update')->middleware('verificar_token'); */
Route::delete('/foro','App\Http\Controllers\ProfesorEscribeForo@destroy')->middleware('verificar_token'); 
//




// MATERIAS DADO UN GRUPO 
Route::get('/listarMaterias', 'App\Http\Controllers\ProfesorGrupo@listarMateriasGrupo')->middleware('verificar_token');
//
// GRUPOS MATERIA DADO UN PROFESOR
Route::get('/profesor-grupo', 'App\Http\Controllers\ProfesorGrupo@listarProfesorGrupo')->middleware('verificar_token');
// TAREAS
Route::get('/tareas-corregir', 'App\Http\Controllers\ProfesorCreaTarea@tareasParaCorregir')->middleware('verificar_token');
Route::get('/tarea', 'App\Http\Controllers\ProfesorCreaTarea@traerTarea')->middleware('verificar_token');
Route::post('/tarea', 'App\Http\Controllers\ProfesorCreaTarea@tareas')->middleware('verificar_token');
Route::delete('/tarea', 'App\Http\Controllers\ProfesorCreaTarea@destroy')->middleware('verificar_token');
Route::get('/tareas', 'App\Http\Controllers\ProfesorCreaTarea@listarTareas')->middleware('verificar_token');
Route::post('/entregas-alumno', 'App\Http\Controllers\AlumnoEntregaTarea@seleccion')->middleware('verificar_token');
Route::get('/notas-alumno', 'App\Http\Controllers\AlumnoEntregaTarea@TareaNotaAlumnoMateria')->middleware('verificar_token');



// ENTREGAS 
Route::get('/entregas-grupo', 'App\Http\Controllers\AlumnoEntregaTarea@listarEntregas')->middleware('verificar_token');
Route::get('/entregas-alumnos', 'App\Http\Controllers\AlumnoEntregaTarea@listarEntregasAlumno')->middleware('verificar_token');
Route::get('/entregas-alumno', 'App\Http\Controllers\AlumnoEntregaTarea@entregaAlumno')->middleware('verificar_token');
Route::put('/entregas-correccion', 'App\Http\Controllers\AlumnoEntregaTarea@verificar_correcion')->middleware('verificar_token');
Route::get('/visualizar-entrega', 'App\Http\Controllers\AlumnoEntregaTarea@visualizarEntrega')->middleware('verificar_token');
Route::get('/promedio', 'App\Http\Controllers\AlumnoEntregaTarea@promedioMateria')->middleware('verificar_token');


// AGENDA CLASE VIRTUAL
Route::get('/agenda-clase', 'App\Http\Controllers\AgendaClaseVirtualController@show')->middleware('verificar_token');
Route::post('/agenda-clase', 'App\Http\Controllers\AgendaClaseVirtualController@store')->middleware('verificar_token');
Route::put('/agenda-clase', 'App\Http\Controllers\AgendaClaseVirtualController@update')->middleware('verificar_token');
Route::delete('/agenda-clase', 'App\Http\Controllers\AgendaClaseVirtualController@destroy')->middleware('verificar_token');
Route::get('/agenda-clase-eventos', 'App\Http\Controllers\AgendaClaseVirtualController@consultaEvento')->middleware('verificar_token');
Route::get('/agenda-clase-grupos', 'App\Http\Controllers\AgendaClaseVirtualController@consultaGruposMateria')->middleware('verificar_token');


// LISTA CLASE VIRTUAL
Route::post('/lista-clase', 'App\Http\Controllers\GrupoController@store')->middleware('verificar_token');
Route::get('/lista-clase', 'App\Http\Controllers\GrupoController@mostrarFaltasTotalesGlobal')->middleware('verificar_token');
Route::get('/registro-clase', 'App\Http\Controllers\GrupoController@registroClase');
Route::put('/lista-clase', 'App\Http\Controllers\GrupoController@update')->middleware('verificar_token');
Route::get('/registro-alumno', 'App\Http\Controllers\GrupoController@registroAlumno')->middleware('verificar_token');
Route::get('/registro-listas', 'App\Http\Controllers\GrupoController@index')->middleware('verificar_token');




// ALUMNOS 
Route::get('/alumnosTarea', 'App\Http\Controllers\AlumnoEntregaTarea@index')->middleware('verificar_token');
Route::get('/alumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@traerTareasMateria')->middleware('verificar_token');
Route::post('/alumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@store')->middleware('verificar_token');
Route::get('/archivoAlumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@traerArchivo')->middleware('verificar_token');
Route::put('/alumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@update')->middleware('verificar_token');


Route::get('/alumno', 'App\Http\Controllers\alumnos@show')->middleware('verificar_token');

// GRUPOS

Route::get('/listar-alumnos', 'App\Http\Controllers\GrupoController@listarAlumnos')->middleware('verificar_token');

// ENDPOINTS EQUISDES // USELESS NO BORRAR

//TAREA 
/* Route::get('/traerTareasGrupo','App\Http\Controllers\ProfesorCreaTarea@traerTareasGrupo')->middleware('verificar_token');
Route::get('/traerArchivoTarea','App\Http\Controllers\ProfesorCreaTarea@traerArchivo'); */

/* Route::get('/tareas','App\Http\Controllers\ProfesorCreaTarea@show')->middleware('verificar_token');
 */

