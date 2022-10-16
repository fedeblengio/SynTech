<?php

namespace App\Http\Controllers;

use App\Models\token;
use App\Models\usuarios;
use App\Models\GruposProfesores;
use App\Models\alumnoGrupo;
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

    public function connect(Request $request)
    {

        $u = usuarios::where('id', $request->username)->first();
        $grupoProfesor = GruposProfesores::where('idProfesor', $request->username)->first();
        $grupoAlumno = alumnoGrupo::where('idAlumnos', $request->username)->first();

        switch ($u->ou) {
            case ('Bedelias');
                return response()->json(['error' => 'Unauthenticated.'], 401);
                break;
            case ('Profesor'):
                if(!$grupoProfesor){
                return response()->json(['error' => 'Unauthenticated.'], 401);
                }
                break;
            case ('Alumno'):
                if(!$grupoAlumno){
                return response()->json(['error' => 'Unauthenticated.'], 401);
                }
                break;
        }

        $connection = new Connection([
            'hosts' => ['192.168.50.139'],
        ]);

        $datos = self::traerDatos($u);

        $connection->connect();


        if ($connection->auth()->attempt($request->username . '@syntech.intra', $request->password, $stayBound = true)) {
            return [
                'connection' => 'Success',
                'datos' => $datos,
            ];
        } else {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
    }

    public function traerDatos($u)
    {


        $datos = [
            "username" => $u->id,
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

            $usuarios = usuarios::where('id', $request->idUsuario)->first();

            if ($usuarios) {
                DB::update('UPDATE usuarios SET imagen_perfil="' . $nombre . '" WHERE id="' . $request->idUsuario . '";');
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
        $usuario = usuarios::where('id', $request->username)->first();
        $base64imagen = base64_encode(Storage::disk('ftp')->get($usuario->imagen_perfil));
        return $base64imagen;
    }
}
