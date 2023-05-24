<?php

namespace Tests\Feature;

use App\Models\datosForo;
use App\Models\Foro;
use App\Models\ProfesorForoGrupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\alumnoGrupo;
use App\Models\alumnos;
use App\Models\grupos;
use App\Models\GruposProfesores;
use App\Models\token;
use App\Models\materia;
use App\Models\profesores;
use App\Models\usuarios;

class ProfesorEscribeForoControllerTest extends TestCase
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
        $foro = ProfesorForoGrupo::factory()->create([
            'idGrupo' => $grupo->idGrupo,
            'idMateria' => $materia->id,
            'idProfesor' => $profesor->id,
        ]);

        return ['grupo' => $grupo, 'materia' => $materia, 'profesor' => $profesor, 'alumno' => $alumno, 'token' => $token->token, 'foro' => $foro];
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

    public function test_get_publicaciones_foro()
    {
        $info = $this->createDataNecesariaParaTest();
        $publicacion = datosForo::factory()->create([
            'idForo' => $info['foro']->idForo,
            'idUsuario' => $info['alumno']->id,
        ]);

        $response = $this->get('api/foro/grupo/' . $info['grupo']->idGrupo . '/usuario/' . $info['alumno']->id . '/5', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json()));
        $this->assertEquals($publicacion->mensaje, $response->json()[0]['data']['mensaje']);
    }

    public function test_get_publicaciones_foro_materia()
    {
        $info = $this->createDataNecesariaParaTest();
        $publicacion = datosForo::factory()->create([
            'idForo' => $info['foro']->idForo,
            'idUsuario' => $info['alumno']->id,
        ]);
        $response = $this->get('api/foro/grupo/' . $info['grupo']->idGrupo . '/usuario/' . $info['alumno']->id . '/materia/' . $info['materia']->id . '/5', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json()));
        $this->assertEquals($publicacion->mensaje, $response->json()[0]['data']['mensaje']);
    }

    public function test_delete_publicacion_foro(){
        $info = $this->createDataNecesariaParaTest();
        $publicacion = datosForo::factory()->create([
            'idForo' => $info['foro']->idForo,
            'idUsuario' => $info['alumno']->id,
        ]);

        $response = $this->delete('api/foro/'.$publicacion->id,[], [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('datosForo', [
            'id' => $publicacion->id,
            'idForo' => $info['foro']->idForo,
            'idUsuario' => $info['alumno']->id,
        ]);
    }
    public function test_error_delete_publicacion(){
        $info = $this->createDataNecesariaParaTest();
        $randomID=mt_rand(9999, 9999999);
        $response = $this->delete('api/foro/'.$randomID,[], [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(404);
    }

    public function test_store_publicacion_foro()
    {
        $info = $this->createDataNecesariaParaTest();
        $mensaje = [
            'idGrupo' => $info['grupo']->idGrupo,
            'idMateria' => $info['materia']->id,
            'idUsuario' => $info['profesor']->id,
            'mensaje' => 'Mensaje de prueba',
        ];
        $response = $this->post('api/foro', $mensaje, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('datosForo', [
            'idForo' => $info['foro']->idForo,
            'idUsuario' => $info['profesor']->id,
            'mensaje' => 'Mensaje de prueba',
        ]);
    }

}