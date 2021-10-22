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

    public function listarMateriasGrupo(Request $request)
    {
        $idGrupo=DB::table('alumnos_pertenecen_grupos')
                 ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
                 ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
                 ->get();

        $materias=DB::table('grupos_tienen_profesor')
                  ->select('grupos_tienen_profesor.idMateria AS idMateria', 'materias.nombre AS Materia', 'grupos_tienen_profesor.idGrupo AS idGrupo', 'grupos_tienen_profesor.idProfesor AS idProfesor' )
                  ->join('materias', 'grupos_tienen_profesor.idMateria', '=', 'materias.id')
                  ->where('grupos_tienen_profesor.idGrupo', $idGrupo[0]->idGrupo)
                  ->get();

        return response()->json($materias);
    }


}


