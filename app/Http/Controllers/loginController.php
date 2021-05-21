<?php

namespace App\Http\Controllers;
use App\Models\token;
use App\Models\usuarios;
use Illuminate\Http\Request;
use LdapRecord\Models\ActiveDirectory\User;
use Illuminate\Support\Str;
use LdapRecord\Connection;
use Carbon\Carbon;


class loginController extends Controller
{
    
    public function index()
    {
        $allUsers =  User::all();
        return response()->json($allUsers);
    }

    public function connect(Request $request)
    {
        
        $connection = new Connection([
            'hosts' => ['192.168.1.73'],
        ]);

        $datos = self::traerDatos($request); 

        $connection-> connect();

        if ($connection->auth()->attempt($request->username.'@syntech.intra', $request->password, $stayBound = true)) {
            return [
                'connection' => 'Success',
                 'datos' => $datos, 
                 ];
        }else {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        } 

    }

    public function traerDatos($request){


        $u = usuarios::where('username', $request->username)->first(); 

        $datos=[
            "username" => $u->username,
            "nombre" => $u->nombre,
            "ou" => $u->ou
        ];

        $base64data = base64_encode(json_encode($datos));
        $tExist = token::where('token', $base64data)->first();
        
       
        if($tExist){
            $tExist->delete();
            self::guardarToken($base64data);

        }else{
            self::guardarToken($base64data);
        }

        return  $base64data;
    }




    public function guardarToken($token){
        $t = new token;
        $t->token=$token;
        $t->fecha_vencimiento=Carbon::now()->addMinutes(60);
        $t->save();
    }
   









   
}
