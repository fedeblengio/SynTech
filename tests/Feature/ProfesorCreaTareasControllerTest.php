<?php

namespace Tests\Feature;

use App\Models\AlumnoEntrega;
use App\Models\AlumnoReHacerTarea;
use App\Models\ProfesorTarea;
use App\Models\Tarea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

use App\Models\alumnoGrupo;
use App\Models\alumnos;
use App\Models\grupos;
use App\Models\GruposProfesores;
use App\Models\token;
use App\Models\materia;
use App\Models\profesores;
use App\Models\usuarios;

class ProfesorCreaTareasControllerTest extends TestCase
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
    public function test_listar_tareas_alumno()
    {
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);

        $response = $this->get('api/grupo/' . $info['grupo']->idGrupo . '/materia/' . $info['materia']->id . '/usuarios/' . $info['alumno']->id . '/tarea', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json()['tareas']));
        $this->assertEquals($tarea->idTareas, $response->json()['tareas'][0]['idTarea']);
    }

    public function test_listar_tarea()
    {
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);

        $response = $this->get('api/tarea/' . $tarea->idTareas, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals($tarea->idTareas, $response->json()['datos']['idTarea']);
    }

    public function test_error_listar_tarea()
    {
        $info = $this->createDataNecesariaParaTest();
        $randomID = rand(300, 1200);

        $response = $this->get('api/tarea/' . $randomID, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(404);
    }

    public function test_profesor_crear_tarea()
    {
        $info = $this->createDataNecesariaParaTest();

        $tarea = [
            'idGrupo' => $info['grupo']->idGrupo,
            'idMateria' => $info['materia']->id,
            'idUsuario' => $info['profesor']->id,
            'titulo' => 'Un titulo de ejemplo',
            'descripcion' => 'Una descripcion',
            'fechaVencimiento' => Carbon::now()->addDays(2),
        ];

        $response = $this->post('api/tarea', $tarea, [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tareas', [
            'titulo' => $tarea['titulo'],
            'descripcion' => $tarea['descripcion'],
            'fecha_vencimiento' => $tarea['fechaVencimiento'],
        ]);

        $this->assertDatabaseHas('profesor_crea_tareas', [
            'idMateria' => $tarea['idMateria'],
            'idProfesor' => $tarea['idUsuario'],
            'idGrupo' => $tarea['idGrupo'],
        ]);
    }

    public function test_profesor_crear_tarea_sin_token()
    {
        $info = $this->createDataNecesariaParaTest();

        $tarea = [
            'idGrupo' => $info['grupo']->idGrupo,
            'idMateria' => $info['materia']->id,
            'idUsuario' => $info['profesor']->id,
            'titulo' => 'Un titulo de ejemplo',
            'descripcion' => 'Una descripcion',
            'fechaVencimiento' => Carbon::now()->addDays(2),
        ];
        $response = $this->post('api/tarea', $tarea);
        $response->assertStatus(401);
    }

    public function test_error_profesor_crea_tarea()
    {
        $info = $this->createDataNecesariaParaTest();

        $tarea = [
            'idGrupo' => $info['grupo']->idGrupo,
            'idMateria' => null,
            'idUsuario' => $info['profesor']->id,
            'titulo' => 'Un titulo de ejemplo',
            'descripcion' => 'Una descripcion',
            'fechaVencimiento' => Carbon::now()->addDays(2),
        ];

        $response = $this->post('api/tarea', $tarea, [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(302);
    }
    public function test_profesor_corrige_tarea()
    {
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
        $calificacion = [
            'calificacion' => 10,
            'mensaje' => 'Un mensaje de prueba',
            're_hacer' => 0,
            're_entrega' => 0,
        ];

        $response = $this->put('api/tarea/' . $tarea->idTareas . '/alumno/' . $info['alumno']->id . '/correccion', $calificacion, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('alumno_entrega_tareas', [
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
            'calificacion' => $calificacion['calificacion'],
            'mensaje_profesor' => $calificacion['mensaje'],
        ]);
    }

    public function test_error_profesor_corrige_tarea()
    {
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
        $calificacion = [
            'calificacion' => null,
            'mensaje' => null,
            're_hacer' => null,
            're_entrega' => null,
        ];

        $response = $this->put('api/tarea/' . $tarea->idTareas . '/alumno/' . $info['alumno']->id . '/correccion', $calificacion, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(302);
    }

    public function test_profesor_re_corrige_tarea()
    {
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $entrega = AlumnoReHacerTarea::factory()->create([
            'idTareasNueva' => $tarea->idTareas,
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
            'calificacion' => null,
        ]);
        $calificacion = [
            'calificacion' => 8,
            'mensaje' => 'Un mensaje de prueba',
            're_hacer' => 0,
            're_entrega' => 1,
        ];

        $response = $this->put('api/tarea/' . $tarea->idTareas . '/alumno/' . $info['alumno']->id . '/correccion', $calificacion, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('re_hacer_tareas', [
            'idTareas' => $tarea->idTareas,
            'idAlumnos' => $info['alumno']->id,
            'calificacion' => $calificacion['calificacion'],
            'mensaje_profesor' => $calificacion['mensaje'],
        ]);
    }

    public function test_profesor_eliminar_tarea()
    {
        $info = $this->createDataNecesariaParaTest();
        $tarea = ProfesorTarea::factory()->create([
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'idProfesor' => $info['profesor']->id,
        ]);
        $response = $this->delete('api/tarea/' . $tarea->idTareas, [], [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('tareas', [
            'id' => $tarea->idTareas,
        ]);
    }

    public function test_error_profesor_eliminar_tarea()
    {
        $info = $this->createDataNecesariaParaTest();
        $randomID = rand(300, 1200);
        $response = $this->delete('api/tarea/' . $randomID, [], [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(404);
    }



}