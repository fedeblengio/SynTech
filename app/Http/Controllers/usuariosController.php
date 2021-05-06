<?php

namespace App\Http\Controllers;
use App\Models\usuarios;
use Illuminate\Http\Request;
use LdapRecord\Models\ActiveDirectory\User;

class usuariosController extends Controller
{
   
    public function index(Request $request)
    {
        return response()->json(usuarios::all());
    }

    

    public function agregarUsuarioDB(Request $request){
        $usuario = new usuarios;

            $usuario -> username = $request->samaccountname;
            $usuario -> nombre = $request->cn;
            $usuario -> email = $request->userPrincipalName;
            $usuario-> ou= $request->ou;

            $usuario -> save();
    }

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
            self::agregarUsuarioDB($request);
            $user->save();
            return "Usuario creado";
        } catch (\LdapRecord\LdapRecordException $e) {
            return "Fallo al crear usuario ".$e;
        }
    }

   
    public function store()
    {

    }

    public function show(Request $request)
    {
        $userDB = usuarios::find($request->username);
        return response()->json($userDB);
    }

   
    public function edit(Request $request)
    {


    }

   
    public function update(Request $request)
    {
        $user = User::find('cn='.$request->cn.',ou='.$request->ou.',dc=syntech,dc=intra');

        $user->unicodePwd = $request->unicodePwd;

        $user->save();

        return "Usuario Modificado";


    }

  
    public function destroy(Request $request)
    {
        $user = User::find('cn='.$request->cn.',ou='.$request->ou.',dc=syntech,dc=intra');
    
       /*  $user->userAccountControl = 2;
        $user->refresh(); */
        
        $user->delete();
        $u = usuarios::where('username', $request->username)->first();
        $u->delete();
    
        return "Usuario Eliminado";
    }
}
