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

    

    public function create(Request $request){
        
       
            
            $usuarioAD = User::find('cn='.$request->cn.',ou='.$request->ou.',dc=syntech,dc=intra'); 
            $usuarioDB = usuarios::where('username',$request->samaccountname)->first();      
            $usuarioDB2 = usuarios::where('email',$request->userPrincipalName)->first();
          
            
            if($usuarioAD){      
                return response()->json(['error' => 'Forbidden'], 403);
                $this->exit();
            }
            if ($usuarioDB2) {
                return response()->json(['error' => 'Forbidden'], 403);
                $this->exit();
            }      
            if ($usuarioDB) {
                return response()->json(['error' => 'Forbidden'], 403);          
            }else{
                $usuarioDB = new usuarios;
                $usuarioDB -> username = $request->samaccountname;
                $usuarioDB -> nombre = $request->cn;
                $usuarioDB -> email = $request->userPrincipalName;            
                $usuarioDB-> ou= $request->ou;
                $usuarioDB -> save();
        
                self::agregarUsuarioAD($request);
                return response()->json(['status' => 'OK'], 200);
            }
           
          
       

       
        
            
    }

    public function agregarUsuarioAD(Request $request)
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
        } catch (\LdapRecord\LdapRecordException $e) {
            return "Fallo al crear usuario ".$e;
        }
    }

   
    public function store()
    {

    }

    public function show(Request $request)
    {
        $userDB = usuarios::where('username', $request->username)->first();
        return response()->json($userDB);
    }

   
    public function edit(Request $request)
    {


    }

   
    public function update(Request $request)
    {

        $usuarios = usuarios::where('nombre', $request->cn)->first();
       
        
        $user = User::find('cn='.$request->cn.',ou='.$request->ou.',dc=syntech,dc=intra');
        $user->unicodePwd = $request->unicodePwd;
        
        $user->save();
        $user->refresh();
     
        /* Cambiar cn ad */

    /*  $user->cn = $request->newcn;
        $user->rename();
         */
        
        
        $usuarios->nombre = $request->newcn;
        $usuarios->email = $request->email;

        $usuarios->save();

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
