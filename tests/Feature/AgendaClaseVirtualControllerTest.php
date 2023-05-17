<?php

namespace Tests\Feature;

use App\Models\agendaClaseVirtual;
use App\Models\alumnoGrupo;
use App\Models\alumnos;
use App\Models\grupos;
use App\Models\token;
use App\Models\materia;
use App\Models\profesores;
use App\Models\usuarios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AgendaClaseVirtualControllerTest extends TestCase
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
    public function test_crear_clase_virtual()
    {
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = [
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'fecha_fin' => Carbon::now(),
            'fecha_inicio' => Carbon::now()->addHour(),
        ];

        $response = $this->post('api/agenda-clase', $claseVirtual, [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertSee($claseVirtual['idProfesor']);
        $response->assertSee($claseVirtual['idMateria']);
        $response->assertSee($claseVirtual['idGrupo']);
    }

    public function test_error_crear_clase_virtual_sin_token()
    {
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = [
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'fecha_fin' => Carbon::now(),
            'fecha_inicio' => Carbon::now()->addHour(),
        ];

        $response = $this->post('api/agenda-clase', $claseVirtual);
        $response->assertStatus(401);
    }
    public function test_error_crear_clase_virtual()
    {
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = [
            'idGrupo' => $info['grupo']->idGrupo,
            'fecha_fin' => Carbon::now(),
            'fecha_inicio' => Carbon::now()->addHour(),
        ];

        $response = $this->post('api/agenda-clase', $claseVirtual, [
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(302);
    }

    public function test_listar_clase_virtual_grupo()
    {
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
        ]);

        $response = $this->get('api/agenda-clase/usuario/' . $info['profesor']->id . '/grupo/' . $info['grupo']->idGrupo, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        dd($response->json());
        $this->assertEquals(1, count($response->json()));
        $this->assertEquals($response->json()[0]['idProfesor'], $claseVirtual->idProfesor);
        $this->assertEquals($response->json()[0]['idMateria'], $claseVirtual->idMateria);
        $this->assertEquals($response->json()[0]['idGrupo'], $claseVirtual->idGrupo);

        dd($response);

    }



}