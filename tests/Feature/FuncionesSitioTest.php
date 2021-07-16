<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Http\Controllers\ProfesorGrupo;
use App\Http\Controllers\ProfesorEscribeForo;
use App\Http\Controllers\alumnos;
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

    public function test_listar_foro_grupo()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'idForo'   => '2'
        ]);
             

        $profesor = new ProfesorGrupo();
        $resultado = $profesor->listarDatosForo($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    }  

    public function test_agregar_post()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'idForo'   => '2',
            'idUsuario'=>'55555555',
            'titulo'=>'EJEMPLO DE TITULO', 
            'mensaje'=>'Ejemplo de mensaje'
        ]);
             

        $profesor = new ProfesorEscribeForo();
        $resultado = $profesor->store($request);
        $salida1 = json_encode($resultado);
        var_dump($salida1);
        $this->assertTrue(true);
    }  

    public function test_un_listar_foro()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'idMateria'   => '3',
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
            'idForo'   => '2'
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
}
