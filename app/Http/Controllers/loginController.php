<?php

namespace App\Http\Controllers;

use App\Models\token;
use App\Models\usuarios;
use Illuminate\Http\Request;
use LdapRecord\Models\ActiveDirectory\User;
use Illuminate\Support\Str;
use LdapRecord\Connection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class loginController extends Controller
{

    public function index()
    {
        $allUsers =  User::all();
        return response()->json($allUsers);
    }



    public function cargarImagen(Request $request)
    {
        try {
            $nombre = "";
            if ($request->hasFile("archivo")) {
                $file = $request->archivo;

                $nombre = time() . "_" . $file->getClientOriginalName();
                Storage::disk('ftp')->put($nombre, fopen($request->archivo, 'r+'));

                self::subirImagen($request, $nombre);
            }

            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Error'], 406);
        }
    }

    public function subirImagen($request, $nombre)
    {
          try { 

        $usuarios = usuarios::where('username', $request->idUsuario)->first();

        if ($usuarios) {
            DB::update('UPDATE usuarios SET imagen_perfil="' . $nombre . '" WHERE username="' . $request->idUsuario . '";');
            if ($usuarios->imagen_perfil !== "default_picture.png") {
                Storage::disk('ftp')->delete($usuarios->imagen_perfil);
            }
        }
        return response()->json(['status' => 'Success'], 200);
           } catch (\Throwable $th) {
        return response()->json(['status' => 'Bad Request'], 400);

          }
    }

    public function traerImagen(Request $request)
    {
        $usuario = usuarios::where('username', $request->username)->first();
        $base64imagen = base64_encode(Storage::disk('ftp')->get($usuario->imagen_perfil));
        return $base64imagen;
    }
}
