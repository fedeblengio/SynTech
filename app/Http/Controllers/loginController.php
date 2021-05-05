<?php

namespace App\Http\Controllers;

use LdapRecord\Models\ActiveDirectory\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LdapRecord\Connection;
use App\Models\usuarios;

class loginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allUsers =  User::all();
        return response()->json($allUsers);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function traerNombreUsuario($request){
        $u = usuarios::where('username', $request->username)->first();
        return $u->nombre;
    }

    public function connect(Request $request)
    {
        $token = Str::random(60);
        $connection = new \LdapRecord\Connection([
            'hosts' => ['192.168.1.73'],
        ]);
            $usuario = self::traerNombreUsuario($request);
            
        if ($connection->auth()->attempt($request->username.'@syntech.intra', $request->password)) {
            return [
                'connection' => 'Success',
                'username' => $usuario,
                'token' => $token
                 ];
        }else {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }


}
