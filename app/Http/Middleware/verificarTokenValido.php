<?php

namespace App\Http\Middleware;
use Carbon\Carbon;
use App\Models\token;
use Closure;
use Illuminate\Http\Request;

class verificarTokenValido
{
    public function handle(Request $request, Closure $next)
    {
        try {
            
        $t = token::where('token', $request->header('token'))->first();
        $fecha_actual = Carbon::now();
        $fecha_vencimiento = Carbon::parse($t->fecha_vencimiento);
        

        if($fecha_vencimiento->gt($fecha_actual)){
            return $next($request);
            
         }else{
            $t->delete();   
            return response()->json(['error' => 'Forbidden.'], 403);
         }
        
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Invalid Token'], 403);
        }
     
        
    }
}
