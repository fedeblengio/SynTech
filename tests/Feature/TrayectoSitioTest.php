<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class TrayectoSitioTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_login()
    {

        $data = ['username' => '55555555','password'=>'1'];
        $response = $this->postJson('/api/login', $data);
        $response->assertStatus(200);
    }

    public function test_listar_profesor_grupo()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $data = ['idProfesor' => '55555555'];


        $response = $this->withHeaders([
            'token' => $token,
        ])->getJson('/api/profesor-grupo', $data);

        $response->assertStatus(200);
    }

    public function test_listar_foro_grupo()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $data = ['idForo' => '15'];


        $response = $this->withHeaders([
            'token' => $token,
        ])->getJson('/api/foro-grupo', $data);

        $response->assertStatus(200);
    }

    public function test_agregar_post()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $data = ['idForo' => '15','idUsuario'=>'55555555', 'titulo'=>'EJEMPLO DE TITULO','mensaje'=>'Ejemplo de mensaje'];


        $response = $this->withHeaders([
            'token' => $token,
        ])->postJson('/api/foro', $data);
        
        $response->assertStatus(200);
    }

    public function test_un_listar_foro()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $data = ['idMateria' => '46','idGrupo'=>'Sitio'];


        $response = $this->withHeaders([
            'token' => $token,
        ])->getJson('/api/foros', $data);
        
        $response->assertStatus(200);
    }

    public function test_listar_datos_foro()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $data = ['idForo' => '15'];


        $response = $this->withHeaders([
            'token' => $token,
        ])->getJson('/api/foro', $data);
        
        $response->assertStatus(200);
    }

   /*  public function test_cambiar_contraseña()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $data = ['username' => '44444444' , 'newPassword' => '2'];


        $response = $this->withHeaders([
            'token' => $token,
        ])->putJson('/api/usuario', $data);
        
        $response->assertStatus(200);
    } */

    public function test_profesor_alumno_grupo()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $data = ['idAlumno' => '44444444'];


        $response = $this->withHeaders([
            'token' => $token,
        ])->getJson('/api/alumno', $data);
        
        $response->assertStatus(200);
    }

    public function test_update_profesor_foro()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $idDatos = DB::table('datosForo')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $data = ['idDatos' => $idDatos[0]->id , 'titulo' => 'TITULO CAMBIADO' , 'mensaje' => 'MENSAJE CAMBIADO'];


        $response = $this->withHeaders([
            'token' => $token,
        ])->putJson('/api/foro', $data);
        
        $response->assertStatus(200);
    }

    public function test_eliminar_post_de_foro()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $idDatos = DB::table('datosForo')->orderBy('created_at', 'desc')->limit(1)->get('id');
        $data = ['idDatos' => $idDatos[0]->id];


        $response = $this->withHeaders([
            'token' => $token,
        ])->deleteJson('/api/foro', $data);
        
        $response->assertStatus(200);
    }

}
