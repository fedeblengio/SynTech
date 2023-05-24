<?php

namespace App\Http\Controllers;

use App\Models\alumnoGrupo;
use App\Models\GruposProfesores;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\usuarios;
use App\Models\token;
use Carbon\Carbon;
use App\Http\Controllers\RegistrosController;
use Illuminate\Support\Facades\DB;
use LdapRecord\Models\ActiveDirectory\User;
use Illuminate\Support\Facades\App;

class usuariosController extends Controller
{
    public function changePassword(Request $request,$id)
    {
        $request->validate([
            'newPassword' => 'required|string|min:8'
        ]);
       
        try {
            $user = User::find('cn=' . $id . ',ou=UsuarioSistema,dc=syntech,dc=intra');
            $user->unicodePwd = $request->newPassword;
            $user->save();
            $user->refresh();
            RegistrosController::store("CONTRASEÑA",$request->header('token'),"UPDATE","");
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function updateUserInfo(Request $request,$id)
    {
        $request->validate([
            'nombre' => 'string',
            'email' => 'email',
            'genero' => 'string'
        ]);
        
        $usuario = usuarios::findOrFail($id);
        try {
            $usuario->fill($request->all());
            $usuario->save();
            RegistrosController::store("USUARIO",$request->header('token'),"UPDATE","");
            return response()->json(["token" => self::updateToken($request,$id), "user"=> self::show($id)], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function show($id)
    {
        $user  = usuarios::findOrFail($id);
        $user['username'] = $user->id;
        $user['grupos'] =$this->getUserGrupos($user);
        if(!App::environment(['testing'])){
            $user['imagen_perfil'] = base64_encode(Storage::disk('ftp')->get($user->imagen_perfil));
        }
        return $user;
    }
    private function getUserGrupos($user)
    {
        if($user->ou == "Profesor"){
            $grupos = GruposProfesores::where('idProfesor', $user->id)->pluck('idGrupo');
            return $grupos;
        }else{
            $grupos = alumnoGrupo::where('idAlumnos', $user->id)->pluck('idGrupo');
            return $grupos;
        }
    }

    public function updateToken($request,$id)
    {

        $t = token::where('token', $request->header('token'))->first();
        if ($t) {
            $t->delete();
        }

        $u = usuarios::findOrFail($id);

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
        }
        self::guardarToken($base64data);

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
