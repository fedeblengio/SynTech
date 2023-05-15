<?php

namespace App\Traits;
use LdapRecord\Models\ActiveDirectory\User;


trait verificarUsuarioPerteneceGrupoAD
{
    /**
     * Uploads a file to the specified path
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $path
     * @return string
     */

     public function verificarPerteneceGrupoAD($id,$grupos){
        $user = User::find('cn='.$id.',ou=UsuarioSistema,dc=syntech,dc=intra');
        $groupsAD = $user->groups()->get();
        foreach($grupos as $grupo){
            if($groupsAD->contains($grupo)){
                return true;
            }
          }
        return false;
    }

}