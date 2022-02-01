<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Http\Controllers\ProfesorGrupo;
use App\Http\Controllers\ProfesorEscribeForo;
use App\Http\Controllers\alumnos;
use App\Http\Controllers\ProfesorCreaTarea;
use App\Http\Controllers\AlumnoEntregaTarea;
use App\Http\Controllers\AgendaClaseVirtualController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuncionesSitioTest extends TestCase
{
    public function test_listar_profesor_grupo()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'idProfesor'   => '55555555'
        ]);
             
        $profesor = new ProfesorGrupo();
        $resultado = $profesor->listarProfesorGrupo($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    }  

    public function test_listar_datos_foro_profesor()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'idUsuario'   => '55555555'
        ]);
             

        $profesor = new ProfesorEscribeForo();
        $resultado = $profesor->traerPublicacionesProfesor($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    }  

    public function test_listar_datos_foro_alumno()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'idUsuario'   => '44444444'
        ]);
             

        $profesor = new ProfesorEscribeForo();
        $resultado = $profesor->traerPublicacionesAlumno($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    }  

    public function test_agregar_post()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'idForo'   => '3',
            'idUsuario'=>'55555555',
            'titulo'=>'EJEMPLO DE TITULO', 
            'mensaje'=>'Ejemplo de mensaje'
        ]);
             

        $profesor = new ProfesorEscribeForo();
        $resultado = $profesor->guardarPublicacionBD($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    }  

    public function test_un_listar_foro()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'idMateria'   => '2',
            'idGrupo'=>'Sitio'
        ]);
             

        $profesor = new ProfesorEscribeForo();
        $resultado = $profesor->index($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    }  

    public function test_listar_datos_foro()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'idForo'   => '3'
        ]);
             

        $profesor = new ProfesorEscribeForo();
        $resultado = $profesor->show($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    }  

    
    public function test_profesor_alumno_grupo()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'idAlumno'   => '44444444'
        ]);
             

        $profesor = new alumnos();
        $resultado = $profesor->show($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    }  

      
    public function test_update_profesor_foro()
    {
        $this->withoutExceptionHandling();
        $idDatos = DB::table('datosForo')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idDatos'   => $idDatos[0]->id,
            'titulo' => 'TITULO CAMBIADO',
            'mensaje' => 'MENSAJE CAMBIADO'
        ]);
             

        $profesor = new ProfesorEscribeForo();
        $resultado = $profesor->update($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    }  

    public function test_eliminar_post_de_foro()
    {
        $this->withoutExceptionHandling();
        $idDatos = DB::table('datosForo')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idDatos'   => $idDatos[0]->id,
        ]);
             

        $profesor = new ProfesorEscribeForo();
        $resultado = $profesor->destroy($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_profesor_crea_tarea()
    {
        $this->withoutExceptionHandling();
       
        $request = new Request([
            'titulo'   => 'EJEMPLO DE TITULO',
            'descripcion'   => 'EJEMPLO DE DESC',
            'fechaVencimiento'   => '2021-12-16T22:18',
            'idMateria'   => '2',
            'idGrupo'   => 'Sitio',
            'idUsuario'   => '55555555',
        ]);
             

        $tarea = new ProfesorCreaTarea();
        $resultado = $tarea->crearTarea($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 


    public function test_listar_tareas_profesor()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idGrupo'   => 'Sitio',
            'idMateria'   => '2',
            'idUsuario'   => '55555555',
        ]);
             

        $tareas = new ProfesorCreaTarea();
        $resultado = $tareas->consultaProfesor($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_listar_tareas_alumnos()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idMateria'   => '2',
            'idUsuario'   => '44444444',
        ]);
             

        $tareas = new ProfesorCreaTarea();
        $resultado = $tareas->consultaAlumno($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_listar_tarea()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idTarea'   => $idTareas[0]->id,
        ]);
             

        $tareas = new ProfesorCreaTarea();
        $resultado = $tareas->traerTarea($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_update_tarea()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'id'   => $idTareas[0]->id,
            'titulo'   => 'MODIFICAR TITULO EJEMPLO',
            'descripcion'   => 'MOD DESC',
            'fecha_vencimiento'   => '2021-12-16T22:18',
        ]);
             

        $tareas = new ProfesorCreaTarea();
        $resultado = $tareas->update($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_alumno_sube_tarea()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idTareas'   => $idTareas[0]->id,
            'mensaje'   => 'EJEMPLO DE MENSAJE',
            'idAlumnos'   => '44444444',
        ]);
             

        $tareaAlumno = new AlumnoEntregaTarea();
        $resultado = $tareaAlumno->subirTarea($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_corregir_tarea()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idTareas'   => $idTareas[0]->id,
            'calificacion'   => '5',
            'idAlumnos'   => '44444444',
            'mensaje'   => 'EJEMPLO DE MENSAJE',
            're_hacer'   => '1',
        ]);
             

        $tareaAlumno = new AlumnoEntregaTarea();
        $resultado = $tareaAlumno->corregirEntrega($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_listar_entregas_re_hacer()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idTareas'   => $idTareas[0]->id,
            'idAlumnos'   => '44444444',
        ]);
             

        $tareaAlumno = new AlumnoEntregaTarea();
        $resultado = $tareaAlumno->entregaAlumnoReHacer($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_re_hacer_tarea_alumno()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idTareas'   => $idTareas[0]->id,
            'idAlumnos'   => '44444444',
        ]);
             

        $tareaAlumno = new AlumnoEntregaTarea();
        $resultado = $tareaAlumno->reHacerTarea($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    
    public function test_listar_entrega_alumno()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idTareas'   => $idTareas[0]->id,
            'idAlumnos'   => '44444444',
        ]);
             

        $tareaAlumno = new AlumnoEntregaTarea();
        $resultado = $tareaAlumno->entregaAlumno($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_listar_entregas_alumnos()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idAlumnos'   => '44444444',
        ]);
             

        $tareaAlumno = new AlumnoEntregaTarea();
        $resultado = $tareaAlumno->listarEntregasAlumno($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_listar_entregas()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idTareas'   => $idTareas[0]->id,
            'idGrupo'   => 'Sitio',
            'idMateria'   => '2',
        ]);
             

        $tareaAlumno = new AlumnoEntregaTarea();
        $resultado = $tareaAlumno->listarEntregas($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_destroy_tarea()
    {
        $this->withoutExceptionHandling();
        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'idTareas'   => $idTareas[0]->id,
        ]);
             

        $tarea = new ProfesorCreaTarea();
        $resultado = $tarea->destroy($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    
    public function test_agendar_clase()
    {
        $this->withoutExceptionHandling();
        $request = new Request([
            'idProfesor'   => '55555555',
            'idMateria'   => '2',
            'idGrupo'   => 'Sitio',
            'fecha_inicio'   => '2021-12-16T22:18',
            'fecha_fin'   => ' 2021-12-17T00:21',
        ]);
             

        $clase = new AgendaClaseVirtualController();
        $resultado = $clase->store($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_consultar_agenda_alumno()
    {
        $this->withoutExceptionHandling();
        $request = new Request([
            'idUsuario'   => '44444444',
        ]);
             

        $clase = new AgendaClaseVirtualController();
        $resultado = $clase->consultaAlumno($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_consultar_agenda_profesor()
    {
        $this->withoutExceptionHandling();
        $request = new Request([
            'idUsuario'   => '55555555',
        ]);
             

        $clase = new AgendaClaseVirtualController();
        $resultado = $clase->consultaProfesor($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_update_agenda_clase()
    {
        $this->withoutExceptionHandling();
        $idClase = DB::table('agenda_clase_virtual')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'id'   => $idClase[0]->id,
            'fecha_inicio'   => '2021-12-17T22:18',
            'fecha_fin'   => '2021-12-18T00:21',
        ]);
             

        $clase = new AgendaClaseVirtualController();
        $resultado = $clase->update($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 

    public function test_destroy_agenda_clase()
    {
        $this->withoutExceptionHandling();
        $idClase = DB::table('agenda_clase_virtual')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $request = new Request([
            'id'   => $idClase[0]->id,
        ]);
             

        $clase = new AgendaClaseVirtualController();
        $resultado = $clase->destroy($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    } 
    
    
    





}
