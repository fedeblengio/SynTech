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
  $b = DB::table('re_hacer_tareas')
  ->select('re_hacer_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 're_hacer_tareas.idAlumnos AS idAlumnos', 're_hacer_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario' ,'profesor_crea_tareas.idGrupo' ,'profesor_crea_tareas.idProfesor' ,'profesor_crea_tareas.idMateria')
  ->join('profesor_crea_tareas', 're_hacer_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
  ->join('usuarios', 're_hacer_tareas.idAlumnos', '=', 'usuarios.username')   
  ->join('tareas', 're_hacer_tareas.idTareas', '=', 'tareas.id')  
  ->where('profesor_crea_tareas.idGrupo','TB1')
  ->where('re_hacer_tareas.idTareas',12)
  ->where('profesor_crea_tareas.idMateria',1)
  ->orderBy('re_hacer_tareas.created_at', 'desc')
  ->get();
 $a= DB::table('alumno_entrega_tareas')
  ->select('alumno_entrega_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'alumno_entrega_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario' ,'profesor_crea_tareas.idGrupo' ,'profesor_crea_tareas.idProfesor' ,'profesor_crea_tareas.idMateria')
  ->join('profesor_crea_tareas', 'alumno_entrega_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
  ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.username') 
  ->join('tareas', 'alumno_entrega_tareas.idTareas', '=', 'tareas.id')   
  ->where('profesor_crea_tareas.idGrupo','TB1')
  ->where('alumno_entrega_tareas.idTareas',12)
  ->where('profesor_crea_tareas.idMateria',1)
  ->orderBy('alumno_entrega_tareas.created_at', 'desc')
  ->get();
    return response()->json($a);
  /*   return $peticionSQL; */
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
Route::post('/imagen-perfil', 'App\Http\Controllers\loginController@cargarImagen')->middleware('verificar_token');
// TRAER IMAGEN FTP B64
Route::get('/imagen-perfil', 'App\Http\Controllers\loginController@traerImagen')->middleware('verificar_token');
// CAMBIAR CONTRASEÑA
Route::put('/usuario', 'App\Http\Controllers\usuariosController@update')->middleware('verificar_token');
//


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

Route::get('/tarea', 'App\Http\Controllers\ProfesorCreaTarea@traerTarea')->middleware('verificar_token');
Route::post('/tarea', 'App\Http\Controllers\ProfesorCreaTarea@tareas')->middleware('verificar_token');
Route::delete('/tarea', 'App\Http\Controllers\ProfesorCreaTarea@destroy')->middleware('verificar_token');
Route::get('/tareas', 'App\Http\Controllers\ProfesorCreaTarea@listarTareas')->middleware('verificar_token');
Route::post('/entregas-alumno', 'App\Http\Controllers\AlumnoEntregaTarea@seleccion')->middleware('verificar_token');

// ENTREGAS 
Route::get('/entregas-grupo', 'App\Http\Controllers\AlumnoEntregaTarea@listarEntregas')->middleware('verificar_token');
Route::get('/entregas-alumnos', 'App\Http\Controllers\AlumnoEntregaTarea@listarEntregasAlumno')->middleware('verificar_token');
Route::get('/entregas-alumno', 'App\Http\Controllers\AlumnoEntregaTarea@entregaAlumno')->middleware('verificar_token');
Route::put('/entregas-correccion', 'App\Http\Controllers\AlumnoEntregaTarea@corregirEntrega')->middleware('verificar_token');

// AGENDA CLASE VIRTUAL
Route::get('/agenda-clase', 'App\Http\Controllers\AgendaClaseVirtualController@show')->middleware('verificar_token');
Route::post('/agenda-clase', 'App\Http\Controllers\AgendaClaseVirtualController@store')->middleware('verificar_token');
Route::put('/agenda-clase', 'App\Http\Controllers\AgendaClaseVirtualController@update')->middleware('verificar_token');
Route::delete('/agenda-clase', 'App\Http\Controllers\AgendaClaseVirtualController@destroy')->middleware('verificar_token');


// ENDPOINTS EQUISDES // USELESS NO BORRAR

//TAREA 
/* Route::get('/traerTareasGrupo','App\Http\Controllers\ProfesorCreaTarea@traerTareasGrupo')->middleware('verificar_token');
Route::get('/traerArchivoTarea','App\Http\Controllers\ProfesorCreaTarea@traerArchivo'); */

/* Route::get('/tareas','App\Http\Controllers\ProfesorCreaTarea@show')->middleware('verificar_token');
 */
// ALUMNOS 
Route::get('/alumnosTarea', 'App\Http\Controllers\AlumnoEntregaTarea@index')->middleware('verificar_token');
Route::get('/alumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@traerTareasMateria')->middleware('verificar_token');
Route::post('/alumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@store')->middleware('verificar_token');
Route::get('/archivoAlumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@traerArchivo')->middleware('verificar_token');
Route::put('/alumnoTarea', 'App\Http\Controllers\AlumnoEntregaTarea@update')->middleware('verificar_token');


Route::get('/alumno', 'App\Http\Controllers\alumnos@show')->middleware('verificar_token');
