<?php

namespace Tests\Feature;

use App\Models\AlumnoEntrega;
use App\Models\AlumnoReHacerTarea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ProfesorTarea;
use App\Models\Tarea;
use App\Models\alumnoGrupo;
use App\Models\alumnos;
use App\Models\grupos;
use App\Models\GruposProfesores;
use App\Models\token;
use App\Models\materia;
use App\Models\profesores;
use App\Models\usuarios;

class AlumnoEntregaTareaControllerTest extends TestCase
{
    use RefreshDatabase;
    public function createDataNecesariaParaTest()
    {
        $token = token::factory()->create();
        $grupo = grupos::factory()->create();
        $materia = materia::factory()->create();
        $profesor = profesores::find($this->createUser("Profesor"));
        $alumno = alumnos::find($this->createUser("Alumno"));

        $alumnoGrupo = alumnoGrupo::create([
            'idAlumnos' => $alumno->id,
            'idGrupo' => $grupo->idGrupo,
        ]);

        $profesor->materia()->sync(array($materia->id));

        $profesorGrupo = GruposProfesores::factory()->create([
            'idProfesor' => $profesor->id,
            'idGrupo' => $grupo->idGrupo,
            'idMateria' => $materia->id,
        ]);


        return ['grupo' => $grupo, 'materia' => $materia, 'profesor' => $profesor, 'alumno' => $alumno, 'token' => $token->token];
    }

    public function createUser($tipo)
    {
        $padded_number = str_pad(mt_rand(1, 9999999), 1 - strlen('1'), '0', STR_PAD_LEFT);
        $randomID = "1" . $padded_number;
        $user = usuarios::factory()->create([
            'id' => $randomID,
            'ou' => $tipo
        ]);

        if ($tipo == "Alumno") {
            alumnos::factory()->create([
                'id' => $randomID,
                'Cedula_Alumno' => $randomID,
            ]);
        } else {
            profesores::factory()->create([
                'id' => $randomID,
                'Cedula_Profesor' => $randomID,
            ]);
        }

        return $randomID;
    }
    public function testAlumnoEntregaTarea()
    {
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = [
            'mensaje' => 'Entrega de una tarea ejemplo',
            're_hacer' => 0,
        ];
        $response = $this->post('api/tarea/' . $tarea->idTareas . '/alumno/' . $info['alumno']->id . '/entrega', $entrega, [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('alumno_entrega_tareas', [
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
            'mensaje' => $entrega['mensaje'],
            're_hacer' => $entrega['re_hacer'],
        ]);
    }

    public function testErrorAlumnoEntregaTarea(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = [
            'mensaje' => null,
            're_hacer' => null,
        ];
        $response = $this->post('api/tarea/' . $tarea->idTareas . '/alumno/' . $info['alumno']->id . '/entrega', $entrega, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(302);
    }

    public function testAlumnoEntregaTareaReHacer()
    {
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = [
            'mensaje' => 'Re-entrega de una tarea ejemplo',
            're_hacer' => 1,
        ];
        $response = $this->post('api/tarea/' . $tarea->idTareas . '/alumno/' . $info['alumno']->id . '/entrega', $entrega, [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('re_hacer_tareas', [
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
            'mensaje' => $entrega['mensaje'],
        ]);
    }
    public function testListarEntregaAlumno(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = AlumnoEntrega::factory()->create([
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
        ]);

        $response = $this->get('api/tarea/'.$tarea->idTareas.'/alumno/'.$info['alumno']->id.'/entrega', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals($tarea->idTareas, $response->json()[0]['data']['idTareas']); 
        $this->assertEquals($entrega->mensaje, $response->json()[0]['data']['mensaje']);
    }

    public function testErrorListarEntregaAlumno(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);

        $response = $this->get('api/tarea/'.$tarea->idTareas.'/alumno/'.$info['alumno']->id.'/entrega', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(404);
    }


    public function testListarReEntregaAlumno(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = AlumnoReHacerTarea::factory()->create([
            'idTareas' => $tarea->idTareas,
            'idTareasNueva' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
        ]);

        $response = $this->get('api/tarea/'.$tarea->idTareas.'/alumno/'.$info['alumno']->id.'/re-entrega', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals($tarea->idTareas, $response->json()[0]['data']['idTareas']); 
        $this->assertEquals($entrega->mensaje, $response->json()[0]['data']['mensaje']);
    }

    public function testErrorListarReEntregaAlumno(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);

        $response = $this->get('api/tarea/'.$tarea->idTareas.'/alumno/'.$info['alumno']->id.'/re-entrega', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(404);
    }

    public function testListarEntregasDeUnaTarea(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);

        $entrega = AlumnoEntrega::factory()->create([
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
            'calificacion' => null
        ]);

    
        
        $response = $this->get('api/grupo/'.$info['grupo']->idGrupo.'/materia/'.$info['materia']->id.'/tarea/'.$tarea->idTareas.'/entrega', [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals($tarea->idTareas, $response->json()['entregas_totalesNoCorregidas']['entregas_tareas_no_corregidas'][0]['idTarea']);
        $this->assertEquals(1,count($response->json()['entregas_totalesNoCorregidas']['entregas_tareas_no_corregidas']));
    }

    public function testListarEntregasCorregidasDeUnaTarea(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);

        $entrega = AlumnoEntrega::factory()->create([
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
        ]);

    
        
        $response = $this->get('api/grupo/'.$info['grupo']->idGrupo.'/materia/'.$info['materia']->id.'/tarea/'.$tarea->idTareas.'/entrega', [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals($tarea->idTareas, $response->json()['entregas_totalesCorregidas']['entregas_tareas_corregidas'][0]['idTarea']);
        $this->assertEquals(1,count($response->json()['entregas_totalesCorregidas']['entregas_tareas_corregidas']));
    }


    public function testListarReEntregasDeUnaTarea(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = AlumnoReHacerTarea::factory()->create([
            'idTareas' => $tarea->idTareas,
            'idTareasNueva' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
            'calificacion' => null
        ]);

    
        
        $response = $this->get('api/grupo/'.$info['grupo']->idGrupo.'/materia/'.$info['materia']->id.'/tarea/'.$tarea->idTareas.'/entrega', [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals($tarea->idTareas, $response->json()['entregas_totalesNoCorregidas']['re_hacer_no_corregidas'][0]['idTarea']);
        $this->assertEquals(1,count($response->json()['entregas_totalesNoCorregidas']['re_hacer_no_corregidas']));
    }

    public function testListarReEntregasCorregidasDeUnaTarea(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = AlumnoReHacerTarea::factory()->create([
            'idTareas' => $tarea->idTareas,
            'idTareasNueva' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
       
        ]);

    
        
        $response = $this->get('api/grupo/'.$info['grupo']->idGrupo.'/materia/'.$info['materia']->id.'/tarea/'.$tarea->idTareas.'/entrega', [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals($tarea->idTareas, $response->json()['entregas_totalesCorregidas']['re_hacer_corregidas'][0]['idTarea']);
        $this->assertEquals(1,count($response->json()['entregas_totalesCorregidas']['re_hacer_corregidas']));
    }

    public function testListarEntregasDeUnAlumno(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = AlumnoEntrega::factory()->create([
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
        ]);

        $response = $this->get('api/tarea/alumno/'.$info['alumno']->id.'/entregas', [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals($tarea->idTareas, $response->json()[0]['idTareas']);
        $this->assertEquals(1,count($response->json()));
    }

    public function testListarRegistroTarea(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = AlumnoEntrega::factory()->create([
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
        ]);
        $reEntrega = AlumnoReHacerTarea::factory()->create([
            'idTareas' => $tarea->idTareas,
            'idTareasNueva' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
        ]);
        $response = $this->get('api/tarea/'.$tarea->idTareas.'/alumno/'.$info['alumno']->id.'/registro', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals($tarea->idTareas, $response->json()['primera_entrega']['entrega'][0]['idTareas']);
        $this->assertEquals($tarea->idTareas, $response->json()['segunda_entrega']['entrega'][0]['idTareas']);
        
        $this->assertEquals($entrega->calificacion, $response->json()['primera_entrega']['entrega'][0]['calificacion']);
        $this->assertEquals($reEntrega->calificacion, $response->json()['segunda_entrega']['entrega'][0]['calificacion']);
        $this->assertEquals(1,count($response->json()['primera_entrega']['entrega']));
        $this->assertEquals(1,count($response->json()['segunda_entrega']['entrega']));
    }

    public function testGenerarPromedioGrupoMateria(){
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = AlumnoEntrega::factory()->create([
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
            'calificacion'=> 10
        ]);

        $tarea2 = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega2 = AlumnoEntrega::factory()->create([
            'idTareas' => $tarea2->idTareas,
            'idAlumnos' => $info['alumno']->id,
            'calificacion'=> 12
        ]);

        $promedio = ($entrega['calificacion'] + $entrega2['calificacion']) / 2;
        $response = $this->get('api/grupo/'.$info['grupo']->idGrupo.'/materia/'.$info['materia']->id.'/promedio', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals($info['alumno']->id, $response->json()[0]['idAlumnos']);
        $this->assertEquals($promedio, $response->json()[0]['promedio']);
    }



 
}