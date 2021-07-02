<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use LdapRecord\Models\ActiveDirectory\User;

class usuariosController extends Controller
{
    public function update(Request $request)
    {
        try {
            $user = User::find('cn=' . $request->username . ',ou=UsuarioSistema,dc=syntech,dc=intra');
            $user->unicodePwd = $request->newPassword;
            $user->save();
            $user->refresh();
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

}
