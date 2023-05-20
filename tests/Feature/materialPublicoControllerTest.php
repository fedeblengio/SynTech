<?php

namespace Tests\Feature;


use App\Models\alumnos;
use App\Models\MaterialPublico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


use App\Models\token;
use App\Models\profesores;
use App\Models\usuarios;
class materialPublicoControllerTest extends TestCase
{

    use RefreshDatabase;
    public function createDataNecesariaParaTest()
    {
        $token = token::factory()->create();
        $profesor = profesores::find($this->createUser("Profesor"));
        $alumno = alumnos::find($this->createUser("Alumno"));
        return ['profesor' => $profesor, 'alumno' => $alumno, 'token' => $token->token];
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

    // Route::post('/noticia','App\Http\Controllers\materialPublicoController@store');
    // Route::delete('/noticia/{id}','App\Http\Controllers\materialPublicoController@destroy'); 

    public function test_publicar_noticia(){
        $info = $this->createDataNecesariaParaTest();
        $mensaje = [
            'titulo' =>"titulo", 
            'mensaje' => "mensaje",
            'idUsuario' => $info['profesor']->id,
        ];
        $response = $this->post('api/noticia', $mensaje, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('material_publicos', [
            'idUsuario' => $info['profesor']->id,
            'titulo' => $mensaje['titulo'],
            'mensaje' => $mensaje['mensaje'],
        ]);
    }

    public function test_error_publicar_noticia(){
        $info = $this->createDataNecesariaParaTest();
        $mensaje = [
            'titulo' =>"titulo", 
            'mensaje' => "mensaje",
        ];
        $response = $this->post('api/noticia', $mensaje, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(302);   
    }

    public function test_error_publicar_noticia_if_alumno(){
        $info = $this->createDataNecesariaParaTest();
        $mensaje = [
            'titulo' =>"titulo", 
            'mensaje' => "mensaje",
            'idUsuario' => $info['alumno']->id,
        ];
        $response = $this->post('api/noticia', $mensaje, [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(401);   
    }

    public function test_eliminar_noticia(){
        $info = $this->createDataNecesariaParaTest();
        $noticia = MaterialPublico::factory()->create([
            'idUsuario' => $info['profesor']->id,
        ]);

        $response = $this->delete('api/noticia/'.$noticia->id, [], [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('material_publicos', [
            'id' => $noticia->id,
        ]);
    }
    public function test_error_eliminar_noticia(){
        $info = $this->createDataNecesariaParaTest();
        $randomId= rand(1000, 9999);

        $response = $this->delete('api/noticia/'.$randomId, [], [
            'token' => [
                $info['token'],
            ],
        ]);
        $response->assertStatus(404);
    }

    public function test_get_noticias(){
        $info = $this->createDataNecesariaParaTest();
        $noticia = MaterialPublico::factory()->create([
            'idUsuario' => $info['profesor']->id,
        ]);
        $response = $this->get('api/noticia');
        $response->assertStatus(200);
        $this->assertEquals(1,count($response->json()));
        $this->assertEquals($noticia->id, $response[0]['data']['id']);
    }

}
