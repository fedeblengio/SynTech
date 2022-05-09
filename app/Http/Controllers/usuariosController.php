<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\usuarios;
use App\Models\token;
use Carbon\Carbon;
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
            if ($request->nuevoEmail == null && $request->nuevoNombre == null) {
                DB::update('UPDATE usuarios SET genero="' . $request->genero . '" ,nombre="' . $usuarios->nombre . '" ,  email="' . $usuarios->email . '" WHERE username="' . $request->username . '";');
            }
            if ($request->genero == null && $request->nuevoNombre == null) {
                DB::update('UPDATE usuarios SET genero="' . $usuarios->genero . '" , nombre="' . $usuarios->nombre . '" ,  email="' . $request->nuevoEmail . '" WHERE username="' . $request->username . '";');
            }
            if ($request->genero == null && $request->nuevoEmail == null) {
                DB::update('UPDATE usuarios SET genero="' . $usuarios->genero . '" , nombre="' . $request->nuevoNombre . '" ,  email="' . $usuarios->email . '" WHERE username="' . $request->username . '";');
            }
            return response()->json(["token" => self::updateToken($request)], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function show(Request $request)
    {
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

    public function updateToken($request)
    {

        $t = token::where('token', $request->header('token'))->first();
        if ($t) {
            $t->delete();
        }

        $u = usuarios::where('username', $request->username)->first();

        $datos = [
            "username" => $u->username,
            "nombre" => $u->nombre,
            "ou" => $u->ou,
            "email" => $u->email,
            "genero" => $u->genero,
            "imagen_perfil" => $u->imagen_perfil,
        ];

        $base64data = base64_encode(json_encode($datos));
        $tExist = token::where('token', $base64data)->first();
        if ($tExist) {
            $tExist->delete();
            self::guardarToken($base64data);
        } else {
            self::guardarToken($base64data);
        }

        return  $base64data;
    }

    public function guardarToken($token)
    {
        $t = new token;
        $t->token = $token;
        $t->fecha_vencimiento = Carbon::now()->addMinutes(90);
        $t->save();
    }
}
