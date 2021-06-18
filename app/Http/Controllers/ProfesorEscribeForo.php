<?php

namespace App\Http\Controllers;
use App\Models\datosForo;
use App\Models\Foro;
use Illuminate\Http\Request;
use App\Models\ProfesorForoGrupo;

class ProfesorEscribeForo extends Controller
{
    public function index(Request $request)
    {
        return response()->json(ProfesorForoGrupo::where('idMateria', $request->idMateria)->where('idGrupo', $request->idGrupo)->first());
    }

    public function show(Request $request)
    {
        $mostrarMensajes=datosForo::all()->where('idForo', $request->idForo);
        return response()->json($mostrarMensajes);
    }

    public function store(Request $request)
    {
        try {
                $datosForo = new datosForo;
                $datosForo->idForo = $request->idForo;
                $datosForo->idUsuario = $request->idUsuario;
                $datosForo->mensaje = $request->mensaje;
                $datosForo->datos = "3";
                $datosForo->save();
                return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
                return response()->json(['status' => 'Bad Request'], 400);
             }   

    }

    public function update(Request $request)
    {
        $modificarDatosForo = datosForo::where('id', $request->idDatos)->first();
       
        //try{
            $modificarDatosForo->mensaje = $request->mensaje;
            $modificarDatosForo->save();
            return response()->json(['status' => 'Success'], 200);
        //} catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        //}
    }

    public function destroy(Request $request)
    {
        $eliminarDatosForo = datosForo::where('idDatos', $request->idDatos)->first();
        try {
            $eliminarDatosForo->delete();
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }







}
