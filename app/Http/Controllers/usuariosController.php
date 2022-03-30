<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\usuarios;
use Illuminate\Support\Facades\DB;
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

    public function update_db($request)
    {
        try{  
        $usuarios = usuarios::where('username', $request->username)->first();
        if($usuarios){
            DB::update('UPDATE usuarios SET genero="' . $request->genero . '" ,  email="' . $request->nuevoEmail . '" WHERE username="' . $request->username . '";');  
        }
        return response()->json(['status' => 'Success'], 200);
    } catch (\Throwable $th) {
        return response()->json(['status' => 'Bad Request'], 400);
    }
    }

}
