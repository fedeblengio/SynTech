<?php

namespace Tests\Feature;

use App\Models\alumnoGrupo;
use App\Models\alumnos;
use App\Models\grupos;
use App\Models\token;
use App\Models\usuarios;
use App\Notifications\TestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use LdapRecord\Models\ActiveDirectory\User;
class CambiarContraTest extends TestCase
{
    use RefreshDatabase;   
    public function buildUpDataForTesting(){
        $credentials = $this->createNewUser();
        
        $alumno = alumnos::where('id', $credentials['username'])->first();
        $grupo = grupos::factory()->create();
      
        $alumnoGrupo = alumnoGrupo::create([
            'idAlumnos' => $alumno->id,
            'idGrupo' => $grupo->idGrupo,
        ]);
     
        return $alumno;
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

        $user = (new User)->inside('ou=UsuarioSistema,dc=syntech,dc=intra');
        $user->cn =$cedula;
        $user->unicodePwd = $cedula;
        $user->samaccountname = $cedula;
        $user->save();
        $user->refresh();
        $user->userAccountControl = 66048;
        $user->save();
       
    }

    public function test_cambiar_passwd(){
        $alumno = $this->buildUpDataForTesting();
        $token = token::factory()->create();

        $response = $this->put('api/usuario/'.$alumno->id.'/contrasenia', [
            'newPassword' => "12345678"
        ], [
            'token' => [
                $token['token'],
            ],
        ]);
        $response->assertStatus(200);

        // Test Error case in the same functions cuz Active Directory is a s...
        $response2 = $this->put('api/usuario/'.$alumno->id.'/contrasenia', [
            'newPassword' => null
        ], [
            'token' => [
                $token['token'],
            ],
        ]);
        $response2->assertStatus(302);
        $this->deleteUserFromLdap($alumno);
    }

    private function deleteUserFromLdap($alumno){
        $user = User::find('cn=' . $alumno->id . ',ou=UsuarioSistema,dc=syntech,dc=intra');
        $user->delete();
    }



}
