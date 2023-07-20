<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Traits\verificarUsuarioPerteneceGrupoAD;

class ControlarAccesoProfesor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    use verificarUsuarioPerteneceGrupoAD;
    public function handle(Request $request, Closure $next)
    {
        if(App::environment(['testing'])){
            return $next($request);
        }
        $id = json_decode(base64_decode($request->header('token')))->username;
        $grupos = [
        'Profesor'
        ];
        if ($this->verificarPerteneceGrupoAD($id,$grupos)) {
            return $next($request);
        }
        else{
            return response()->json(['error' => 'Forbidden.'], 401);
        }
    }
}
