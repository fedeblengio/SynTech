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
        $t = token::where('token', $request->header('token'))->first();
        if($t){
            $fechaActual = Carbon::now();
            $fechaVencimiento = Carbon::parse($t->fecha_vencimiento);
            return $this->comprobarTokenEsValido($fechaVencimiento, $fechaActual, $t, $next, $request);
        }else{
            return response()->json(['error' => 'Invalid Token','status'=> 401], 401);
        }

    }


    public function comprobarTokenEsValido(Carbon $fechaVencimiento, Carbon $fechaActual, $t, Closure $next, Request $request)
    {
        if ($fechaVencimiento->gt($fechaActual)) {
            $this->actualizarFechaVencimiento($t,$fechaActual,$fechaVencimiento);
            return $next($request);
        } else {
            $t->delete();
            return response()->json(['error' => 'Forbidden.'], 401);
        }
    }

    public function actualizarFechaVencimiento($t,$fechaActual,$fechaVencimiento)
    {
        if($fechaActual->diffInMinutes($fechaVencimiento) < 30){
            $t->fecha_vencimiento = Carbon::now()->addMinutes(120);
            $t->save();
        }

    }
}
