<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registros;
use App\Models\token;

class RegistrosController extends Controller
{
    public function index()
    {
        return response()->json(Registros::all());
    }

    public  static function store($objeto,$token,$accion, $info)
    {
        try {
            $registros = new registros;
            $registros->idUsuario = json_decode(base64_decode($token))->username;
            $registros->app = "LMS";
            $registros->accion = $accion;
            switch ($accion) {
                case ('CREATE');
                    $registros->mensaje = "Creo " . $objeto . " " . $info;
                    break;
                case ('UPDATE'):
                    $registros->mensaje = "Modifico " . $objeto . " " . $info;
                    break;
                case ('DELETE'):
                    $registros->mensaje = "Elimino " . $objeto . " " . $info;
                    break;
            }
            $registros->save();
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function show(Request $request)
    {
       
    }
}
