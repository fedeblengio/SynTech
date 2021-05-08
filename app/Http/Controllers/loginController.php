<?php

namespace App\Http\Controllers;

use LdapRecord\Models\ActiveDirectory\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LdapRecord\Connection;
use App\Models\usuarios;

class loginController extends Controller
{
    
    public function index()
    {
        $allUsers =  User::all();
        return response()->json($allUsers);
    }

   
    public function create()
    {
      
    }

    public function traerDatos($request){
        $u = usuarios::where('username', $request->username)->first();
       
        $datos=[
            "username" => $u->nombre,
            "ou" => $u->ou
        ];
        return $datos;
    }




    
    public function connect(Request $request)
    {
        $token = Str::random(60);
        $connection = new Connection([
            'hosts' => ['192.168.1.73'],
        ]);

        $datos = self::traerDatos($request);

        $connection-> connect();

        if ($connection->auth()->attempt($request->username.'@syntech.intra', $request->password, $stayBound = true)) {
            return [
                'connection' => 'Success',
                'datos' => $datos,
                'token' => $token
                 ];
        }else {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        } 

    }

   



    public function show(User $user)
    {
       
    }

    
    public function edit(User $user)
    {
        
    }

    
    public function update(Request $request, User $user)
    {
        
    }

   
    public function destroy(User $user)
    {
       
    }


}
