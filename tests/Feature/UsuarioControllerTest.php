<?php

namespace Tests\Feature;

use App\Models\alumnoGrupo;
use App\Models\alumnos;
use App\Models\grupos;
use App\Models\token;
use App\Models\usuarios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use LdapRecord\Models\ActiveDirectory\User;
use Tests\TestCase;

class UsuarioControllerTest extends TestCase
{
    use RefreshDatabase;
    public function createDataNecesariaParaTest()
    {
        $token = token::factory()->create();
        $grupo = grupos::factory()->create();
        $alumno = alumnos::find($this->createUser("Alumno"));
        $alumnoGrupo = alumnoGrupo::create([
            'idAlumnos' => $alumno->id,
            'idGrupo' => $grupo->idGrupo,
        ]);

        return ['grupo' => $grupo, 'alumno' => $alumno, 'token' => $token->token];
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
        } 

        return $randomID;
    }    

    public function testGetUsuario()
    {
        $info = $this->createDataNecesariaParaTest();
        $response = $this->get('api/usuario/' . $info['alumno']->id, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'nombre',
            'email',
            'ou',
            'genero',
            'imagen_perfil',
            'created_at',
            'updated_at',
            'username',
            'grupos',
        ]);
        $this->assertEquals($info['alumno']->id, $response['id']);
    }
    public function testErrorGetUsuario()
    {
        $info = $this->createDataNecesariaParaTest();
        $randomID = rand();
        $response = $this->get('api/usuario/' . $randomID, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(404);
    }

    public function testGetUsuarioGrupo(){
        $info = $this->createDataNecesariaParaTest();
        $response = $this->get('api/usuario/' . $info['alumno']->id . '/grupo', [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'idGrupo',
            ],
        ]);
    }
    public function testModificarUsuario(){
        $info = $this->createDataNecesariaParaTest();
        $modifiedData = [
            'nombre' => 'Nuevo Nombre',
            'email' => 'email@email.com',
            'genero' => 'genero',
        ];
        $response = $this->put('api/usuario/' . $info['alumno']->id, $modifiedData, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $alumno = usuarios::find($info['alumno']->id);
        $this->assertEquals($alumno->nombre, $modifiedData['nombre']);
        $this->assertEquals($alumno->email, $modifiedData['email']);
        $this->assertEquals($alumno->genero, $modifiedData['genero']);   
    }
    public function testErrorModificarUsuario(){
        $info = $this->createDataNecesariaParaTest();
        $modifiedData = [
            'nombre' => 'Nuevo Nombre',
            'email' => 'email',
            'genero' => 'genero',
        ];
        $randomID = rand();
        $response = $this->put('api/usuario/'.$randomID, $modifiedData, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(302);  
    }





 


}
