<?php

namespace App\Http\Controllers;

use LdapRecord\Models\ActiveDirectory\User;
use Illuminate\Http\Request;

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



    public function connect(Request $request)
    {
        $token =env('JWT_SECRET','vqILwvJW6Vxup3KMGhiooseXlFwpuT60rvr71tAi2bVwpVgs3rUgnlrik54AFQDb');
        $connection = new \LdapRecord\Connection([
            'hosts' => ['syntech2021.ddns.net'],
        ]);

        if ($connection->auth()->attempt($request->username, $request->password)) {
            return [
                'connection' => 'Success',
                'username' => $request->username,
                'token' => $token
                 ];
        } else {
            // Invalid credentials.
            return "Credenciales Erroneas";
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
