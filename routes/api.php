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
  Route::get('/agenda-clase/usuario/{id}/grupo/{idGrupo}', 'App\Http\Controllers\AgendaClaseVirtualController@index');
  Route::get('/agenda-clase/profesor/{idProfesor}/grupo/{idGrupo}/materia', 'App\Http\Controllers\AgendaClaseVirtualController@getMateriasFromProfesorGrupo');
  Route::post('/agenda-clase', 'App\Http\Controllers\AgendaClaseVirtualController@store');
  Route::delete('/agenda-clase/{id}', 'App\Http\Controllers\AgendaClaseVirtualController@destroy');
  // LISTA
  Route::post('/agenda-clase/{idClase}/asistencia', 'App\Http\Controllers\GrupoController@pasarListaClaseVirtual');
  Route::put('/agenda-clase/{idClase}/asistencia', 'App\Http\Controllers\GrupoController@modificarLista');
  // REGISTRO
  Route::get('/agenda-clase/{idClase}/registro', 'App\Http\Controllers\GrupoController@registroClase');
  Route::get('/agenda-clase/registro/profesor/{idProfesor}', 'App\Http\Controllers\GrupoController@getAllListasFromProfesor');
  // EVENTOS
  Route::get('/evento/usuario/{id}', 'App\Http\Controllers\AgendaClaseVirtualController@consultaEventos');

  // NOTICIA
  Route::post('/noticia','App\Http\Controllers\materialPublicoController@store');
  Route::delete('/noticia/{id}','App\Http\Controllers\materialPublicoController@destroy'); 

  // GRUPO
  Route::get('/grupo/{id}/materia', 'App\Http\Controllers\ProfesorGrupo@listarMateriasGrupo');
  Route::get('/grupo/{idGrupo}/materia/{idMateria}/usuarios', 'App\Http\Controllers\GrupoController@listarAlumnos');
  
  Route::get('/grupo/{idGrupo}/materia/{idMateria}/usuarios/{idUsuario}/tarea', 'App\Http\Controllers\ProfesorCreaTarea@listarTareas');
  Route::get('/grupo/{idGrupo}/materia/{idMateria}/alumno/{idUsuario}/notas', 'App\Http\Controllers\AlumnoEntregaTarea@TareaNotaAlumnoMateria');
  Route::get('/grupo/{idGrupo}/materia/{idMateria}/registro-faltas', 'App\Http\Controllers\GrupoController@mostrarFaltasTotalesGlobal');

  Route::get('/tarea/{id}', 'App\Http\Controllers\ProfesorCreaTarea@traerTarea'); 
  Route::post('/tarea', 'App\Http\Controllers\ProfesorCreaTarea@store');
  Route::delete('/tarea/{id}', 'App\Http\Controllers\ProfesorCreaTarea@destroy'); 

  Route::post('/tarea/{idTarea}/alumno/{idAlumno}/entrega', 'App\Http\Controllers\AlumnoEntregaTarea@entregarTarea');
  Route::get('/tarea/{idTarea}/alumno/{idAlumno}/entrega', 'App\Http\Controllers\AlumnoEntregaTarea@entregaAlumno');
  Route::get('/tarea/{idTarea}/alumno/{idAlumno}/re-entrega', 'App\Http\Controllers\AlumnoEntregaTarea@entregaAlumnoReHacer');
  Route::get('/tarea/alumno/{idUsuario}/entregas', 'App\Http\Controllers\AlumnoEntregaTarea@listarEntregasAlumno');

  Route::get('/grupo/{idGrupo}/materia/{idMateria}/tarea/{idTarea}/entrega', 'App\Http\Controllers\AlumnoEntregaTarea@listarEntregas');
  Route::put('/tarea/{idTarea}/alumno/{idAlumno}/correccion', 'App\Http\Controllers\AlumnoEntregaTarea@verificarCorreccion');
  
  Route::get('/tarea/{idTarea}/alumno/{idAlumno}/registro', 'App\Http\Controllers\AlumnoEntregaTarea@visualizarEntrega'); 
  Route::get('/grupo/{idGrupo}/materia/{idMateria}/promedio', 'App\Http\Controllers\AlumnoEntregaTarea@promedioMateria');
  
  // NOTIFICACIONES
  Route::get('/notificacion/usuario/{idUsuario}', 'App\Http\Controllers\NotificationController@listarNotificaciones');
  Route::put('/notificacion/{idNotificacion}', 'App\Http\Controllers\NotificationController@marcarLeida');
});

Route::get('/grupo/{idGrupo}/materia/{idMateria}/registro-completo', 'App\Http\Controllers\AlumnoEntregaTarea@descargarRegistroCompleto');

Route::get('/noticia','App\Http\Controllers\materialPublicoController@index'); // ENDPOINT PUBLICO

