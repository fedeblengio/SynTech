<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use LdapRecord\Models\ActiveDirectory\User;
class UsuariosAD extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function crear_usuario()
    {
        $this ->withoutExceptionHandling();
        $user = (new User)->inside('ou=UsuarioSistema,dc=local,dc=com');
        $user->cn = 'John Doe';
        $user->unicodePwd = 'SecretPassword';
        $user->samaccountname = 'jdoe';
        $user->userPrincipalName = 'jdoe@acme.org';

        $user->save();
    }
}
