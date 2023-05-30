<?php

namespace App\Http\Controllers;

use App\Models\token;
use App\Models\usuarios;
use App\Models\GruposProfesores;
use App\Models\alumnoGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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

    public function cerrarSesion(Request $request)
    {
        $token = token::where('token', $request->header('token'))->first();
        if($token){
            $token->delete();
        }
        return response()->json(['message' => 'Sesion cerrada'], 200);
    }


    public function connect(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        $u = usuarios::where('id', $request->username)->first();
        
        if(empty($u) || !$this->isUserValidForSite($u)){
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
   
        $connection = new Connection([
            'hosts' => [env('LDAP_HOST')],
        ]);
    
        $connection->connect();

        if ($connection->auth()->attempt($request->username . '@syntech.intra', $request->password, $stayBound = true)) {
            $datos = self::traerDatos($u);
            return [
                'connection' => 'Success',
                'datos' => $datos,
            ];
        } else {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
    }

    private function isUserValidForSite($u){
        
        $grupoProfesor = GruposProfesores::where('idProfesor', $u->id)->first();
        $grupoAlumno = alumnoGrupo::where('idAlumnos', $u->id)->first();
      
        if($u->ou == 'Bedelias' || $u->ou == 'Profesor' && !$grupoProfesor || $u->ou == 'Alumno' && !$grupoAlumno ){
            return false;
        }
        return true;
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

            if ($request->hasFile("archivo") && !App::environment(['testing']) ) {
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
                if ($usuarios->imagen_perfil !== "default_picture.png" && !App::environment(['testing'])) {
                    Storage::disk('ftp')->delete($usuarios->imagen_perfil);
                }
            }
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function traerImagen($id)
    {
        $usuario = usuarios::findOrFail($id);
        $base64imagen=" ";
        if(!App::environment(['testing'])){
            $base64imagen = base64_encode(Storage::disk('ftp')->get($usuario->imagen_perfil));
        }
       
        return $base64imagen;
    }
}
