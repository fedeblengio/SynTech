<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use LdapRecord\Models\ActiveDirectory\User;
class usuariosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $allUsers =  User::all();
        return $allUsers;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = (new User)->inside('ou='.$request->ou.',dc=syntech,dc=intra');

        $user->cn = $request->cn;
        $user->unicodePwd = $request->unicodePwd;
        $user->samaccountname = $request->samaccountname;
        $user->userPrincipalName = $request->userPrincipalName;

        $user->save();

        // Sync the created users attributes.
        $user->refresh();

        // Enable the user.
        $user->userAccountControl = 66048;


        try {

            $user->save();
            return "Usuario creado";
        } catch (\LdapRecord\LdapRecordException $e) {
            return "Fallo al crear usuario ".$e;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = User::find('cn='.$request->cn.',ou='.$request->ou.',dc=syntech,dc=intra');
        $nombre= $user->getName();
        $ou= $user->getParentDn();

        return "Nombre: ".$nombre." , ".$ou;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = User::find('cn='.$request->cn.',ou='.$request->ou.',dc=syntech,dc=intra');

        $user->unicodePwd = $request->unicodePwd;

        $user->save();

        return "Usuario Modificado";


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = User::find('cn='.$request->cn.',ou='.$request->ou.',dc=syntech,dc=intra');
        $user->delete();

        return "Usuario Eliminado";

    }
}
