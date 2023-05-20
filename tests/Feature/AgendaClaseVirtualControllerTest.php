<?php

namespace Tests\Feature;

use App\Models\agendaClaseVirtual;
use App\Models\alumnoGrupo;
use App\Models\alumnos;
use App\Models\grupos;
use App\Models\GruposProfesores;
use App\Models\listaClaseVirtual;
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
    public function test_eliminar_clase_virtual(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
        ]);

        $response = $this->delete('api/agenda-clase/'.$claseVirtual->id,[],[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $clase = agendaClaseVirtual::find($claseVirtual->id);
        $this->assertNull($clase);

        
    }

    public function test_error_eliminar_clase_virtual(){
        $info = $this->createDataNecesariaParaTest();
        $randomID = rand();
        $response = $this->delete('api/agenda-clase/'.$randomID,[],[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(404);
    }

    public function test_listar_clase_virtual_grupo_profesor()
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
        
        $this->assertEquals(1, count($response->json()));
        $this->assertEquals($response->json()[0]['idProfesor'], $claseVirtual->idProfesor);
        $this->assertEquals($response->json()[0]['idMateria'], $claseVirtual->idMateria);
        $this->assertEquals($response->json()[0]['idGrupo'], $claseVirtual->idGrupo);
    }

    public function test_listar_clase_virtual_grupo_alumno()
    {
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
        ]);

        $response = $this->get('api/agenda-clase/usuario/' . $info['alumno']->id . '/grupo/' . $info['grupo']->idGrupo, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        
        $this->assertEquals(1, count($response->json()));
        $this->assertEquals($response->json()[0]['idProfesor'], $claseVirtual->idProfesor);
        $this->assertEquals($response->json()[0]['idMateria'], $claseVirtual->idMateria);
        $this->assertEquals($response->json()[0]['idGrupo'], $claseVirtual->idGrupo);
    }

    public function test_listar_materias_from_grupo_profesor(){
      
        $info = $this->createDataNecesariaParaTest();
        $grupoProfesor = GruposProfesores::factory()->create([
            'idProfesor' => $info['profesor']->id,
        ]);
    
        $response = $this->get('api/agenda-clase/profesor/'.$info['profesor']->id.'/grupo/'.$grupoProfesor['idGrupo'].'/materia', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json()));
        $this->assertEquals($response->json()[0]['id'], $grupoProfesor['idMateria']);

    }

    public function test_error_listar_materias_from_grupo(){
      
        $info = $this->createDataNecesariaParaTest();

        $response = $this->get('api/agenda-clase/profesor/'.$info['profesor']->id.'/grupo/'.$info['grupo']->idGrupo.'/materia', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(0, count($response->json()));
    }

    public function test_pasar_lista_clase_virtual(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
        ]);

        $alumno2 = alumnos::find($this->createUser("Alumno"));

        $body = [
            'presentes' => [$info['alumno']->id],
            'ausentes' => [$alumno2->id],
        ];

        $response = $this->post('api/agenda-clase/'.$claseVirtual->id.'/asistencia',$body,[
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(200);
        $listaClase = listaClaseVirtual::where('idClase', $claseVirtual->id)->get();
        $this->assertEquals(2, count($listaClase));
    }

    public function test_error_pasar_lista_clase_virtual(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
        ]);

       
        $response = $this->post('api/agenda-clase/'.$claseVirtual->id.'/asistencia',[],[
            'token' => [
                $info['token'],
            ],
        ]);

        $response->assertStatus(400);
    }
    public function test_error_body_pasar_lista_clase_virtual(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
        ]);

        $body = [
            'presentes' => "B",
            'ausentes' => "A",
        ];
       
        $response = $this->post('api/agenda-clase/'.$claseVirtual->id.'/asistencia',$body,[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(302);
    }

    public function test_update_lista_clase_virtual(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
        ]);
        $listaClase = listaClaseVirtual::factory()->create([
            'idClase' => $claseVirtual->id,
            'idAlumnos' => $info['alumno']->id,
            'asistencia' => 0,
        ]);
        $body = [
            'presentes' =>[ $info['alumno']->id],
            'ausentes' => [],
        ];
        $response = $this->put('api/agenda-clase/'.$claseVirtual->id.'/asistencia',$body,[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $listaClase = listaClaseVirtual::where('idClase', $claseVirtual->id)->where('idAlumnos',$info['alumno']->id)->first();
        $this->assertEquals(1, $listaClase->asistencia);
    }

    public function test_error_update_lista_clase_virtual(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
        ]);
        $listaClase = listaClaseVirtual::factory()->create([
            'idClase' => $claseVirtual->id,
            'idAlumnos' => $info['alumno']->id,
            'asistencia' => 0,
        ]);
        $body = [
            'presentes' => $info['alumno']->id,
            'ausentes' => [],
        ];
        $response = $this->put('api/agenda-clase/'.$claseVirtual->id.'/asistencia',$body,[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(302);
        $listaClase = listaClaseVirtual::where('idClase', $claseVirtual->id)->where('idAlumnos',$info['alumno']->id)->first();
        $this->assertEquals(0, $listaClase->asistencia);
    }

    public function test_get_registro_clases_pasadas(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'fecha_inicio' => Carbon::now()->subDays(2),
            'fecha_fin' => Carbon::now()->subDays(2),
        ]);
        $listaClase = listaClaseVirtual::factory()->create([
            'idClase' => $claseVirtual->id,
            'idAlumnos' => $info['alumno']->id,
            'asistencia' => 0,
        ]);
    
        $response = $this->get('api/agenda-clase/registro/profesor/'.$info['profesor']->id,[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json()));
    }

    public function test_error_get_registro_clases_pasadas(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'fecha_inicio' => Carbon::now()->subDays(2),
            'fecha_fin' => Carbon::now()->subDays(2),
        ]);
    
        $response = $this->get('api/agenda-clase/registro/profesor/'.$info['profesor']->id,[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(0, count($response->json()));
    }

    public function test_get_lista_clase_virtual(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'fecha_inicio' => Carbon::now()->subDays(2),
            'fecha_fin' => Carbon::now()->subDays(2),
        ]);
        $listaClase = listaClaseVirtual::factory()->create([
            'idClase' => $claseVirtual->id,
            'idAlumnos' => $info['alumno']->id,
            'asistencia' => 0,
        ]);

        $response = $this->get('api/agenda-clase/'.$claseVirtual->id.'/asistencia/',[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json()));
        $this->assertEquals($response->json()[0]['idAlumno'], $info['alumno']->id);
    }

    public function test_error_get_lista_clase_virutal(){
        $info = $this->createDataNecesariaParaTest();
        $randomID = rand();
        $response = $this->get('api/agenda-clase/'.$randomID.'/asistencia/',[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(0, count($response->json()));
       
    }

    public function test_get_eventos_hoy_alumno(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'fecha_inicio' => Carbon::now(),
            'fecha_fin' => Carbon::now()->addHour(),
        ]);

        $response = $this->get('api/evento/usuario/'. $info['alumno']->id,[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json()));
    }

    public function test_get_eventos_hoy_profesor(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'fecha_inicio' => Carbon::now(),
            'fecha_fin' => Carbon::now()->addHour(),
        ]);

        $response = $this->get('api/evento/usuario/'. $info['profesor']->id,[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json()));
    }

    public function test_error_get_eventos_hoy_usuario(){
        $info = $this->createDataNecesariaParaTest();
        $claseVirtual = agendaClaseVirtual::factory()->create([
            'idProfesor' => $info['profesor']->id,
            'idMateria' => $info['materia']->id,
            'idGrupo' => $info['grupo']->idGrupo,
            'fecha_inicio' => Carbon::now()->addDay(),
            'fecha_fin' => Carbon::now()->addDay()->addHour(),
        ]);

        $response = $this->get('api/evento/usuario/'. $info['profesor']->id,[
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(0, count($response->json()));
    }

    
 











   




}