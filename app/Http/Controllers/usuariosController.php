<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
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

    public function update_db(Request $request)
    {
        try {
            $usuarios = usuarios::where('username', $request->username)->first();
            if ($request->nuevoEmail == null)
                DB::update('UPDATE usuarios SET genero="' . $request->genero . '" ,  email="' . $usuarios->email . '" WHERE username="' . $request->username . '";');
            if ($request->genero == null)
                DB::update('UPDATE usuarios SET genero="' . $usuarios->genero . '" ,  email="' . $request->nuevoEmail . '" WHERE username="' . $request->username . '";');
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }
    public function show(Request $request){
        $a  = usuarios::where('username', $request->idUsuario)->first();
        $alumno = [
            "username" => $a->username,
            "nombre" => $a->nombre,
            "email" => $a->email,
            "ou" => $a->ou,
            "genero" => $a->genero,
            "imagen_perfil" => base64_encode(Storage::disk('ftp')->get($a->imagen_perfil)),
        ];
        return $alumno;
    }
}
