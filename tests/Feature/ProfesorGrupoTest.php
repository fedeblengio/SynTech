<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfesorGrupoTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
   /*  public function test_listar_profesor_grupo()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $data = ['idProfesor' => '49895207'];


        $response = $this->withHeaders([
            'token' => $token,
        ])->getJson('/api/profesor-grupo', $data);

        $response->assertStatus(200);
    }

    public function test_listar_datos_foro()
    {
        $token = "c3ludGVjaDIwMjEuZGRucy5uZXQ=";
        $data = ['idForo' => '1'];


        $response = $this->withHeaders([
            'token' => $token,
        ])->getJson('/api/foro-grupo', $data);

        $response->assertStatus(200);
    } */
}
