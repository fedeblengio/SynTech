<?php

namespace Tests\Feature;


use App\Models\alumnoGrupo;
use App\Models\alumnos;
use App\Models\grupos;
use App\Models\token;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;



use App\Models\usuarios;

use LdapRecord\Models\ActiveDirectory\User;


class LoginTest extends TestCase
{
    use RefreshDatabase;
   
    public function testLogin(){
        $credentials = $this->createNewUser();
       
        $alumno = alumnos::where('id', $credentials['username'])->first();
        $grupo = grupos::factory()->create();
      
        $alumnoGrupo = alumnoGrupo::create([
            'idAlumnos' => $alumno->id,
            'idGrupo' => $grupo->idGrupo,
        ]);
     
        $response = $this->post('api/login',$credentials);
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'connection',
            'datos',
        ]);
       
    }

    private function createNewUser(){
        $padded_number = str_pad(mt_rand(1, 9999999), 1 - strlen('1'), '0', STR_PAD_LEFT);
        $randomID = "1". $padded_number;
       
        $user = usuarios::factory()->create([
            'id' => $randomID,
            'ou' => 'Alumno'
        ]);
      
        $alumno = alumnos::factory()->create([
            'id' => $randomID,
            'Cedula_Alumno' => $randomID,
        ]);

        
        $this->crearUsuarioLDAP($randomID);

        return ['username' => $randomID, 'password' => $randomID];
    }

    private function crearUsuarioLDAP($cedula)
    {

        $this->deleteAllUsersInOU();

        $user = (new User)->inside('ou=Testing,dc=syntech,dc=intra');
        $user->cn =$cedula;
        $user->unicodePwd = $cedula;
        $user->samaccountname = $cedula;
        $user->save();
        $user->refresh();
        $user->userAccountControl = 66048;
        $user->save();
       
    }


    public function deleteAllUsersInOU()
    {
        $users = User::in('ou=Testing,dc=syntech,dc=intra')->get();
        foreach ($users as $user) {
            $user->delete();
        }
    }

    public function testErrorLogin()
    {
        $response = $this->post('api/login',[],[]);
        $response->assertStatus(302);
    }

    public function testLogout(){
        $token = token::factory()->create();
        $response = $this->post('api/logout',[],[
            'token' => [
                $token->token,
            ],
        ]);
        $response->assertStatus(200);   
    }
}
