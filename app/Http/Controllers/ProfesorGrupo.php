<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProfesorGrupo extends Controller
{
    public function listarProfesorGrupo(Request $request)
    {
        $profesor_grupo = DB::table('grupos_tienen_profesor')
        ->select('usuarios.username' ,'usuarios.nombre AS Profesor','materias.id AS idMateria' ,'materias.nombre AS Materia' , 'grupos.idGrupo' , 'grupos.nombreCompleto' , 'grupos.anioElectivo')
        ->join('grupos', 'grupos.idGrupo', '=', 'grupos_tienen_profesor.idGrupo')
        ->join('materias', 'grupos_tienen_profesor.idMateria', '=', 'materias.id')
        ->join('usuarios', 'usuarios.username', '=', 'grupos_tienen_profesor.idProfesor')
        ->where('username', $request->idProfesor)
        ->get();

        return response()->json($profesor_grupo);
    }

    public function listarDatosForo(Request $request)
    {
        $datos_foro = DB::table('datosForo')
        ->select('id','idForo', 'nombre', 'mensaje','titulo', 'datos', 'datosForo.created_at')
        ->join('usuarios', 'idUsuario', '=', 'username')
        ->where('idForo', $request->idForo)
        ->get();

        return response()->json($datos_foro);
    }
}
