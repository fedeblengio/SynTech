<?php

namespace App\Http\Controllers;
use App\Models\GruposProfesores;
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

    /* Select usuarios.username ,usuarios.nombre Profesor,materias.id idMateria ,materias.nombre Materia , grupos.idGrupo , grupos.nombreCompleto , grupos.anioElectivo 
    from grupos_tienen_profesor 
    JOIN grupos ON grupos.idGrupo=grupos_tienen_profesor.idGrupo 
    JOIN materias ON grupos_tienen_profesor.idMateria=materias.id 
    JOIN usuarios ON usuarios.username=grupos_tienen_profesor.idProfesor; */

    public function listarDatosForo(Request $request)
    {
        $datos_foro = DB::table('datosForo')
        ->select('idForo', 'nombre', 'mensaje', 'datos', 'datosForo.created_at')
        ->join('usuarios', 'idUsuario', '=', 'username')
        ->where('idForo', $request->idForo)
        ->get();

        return response()->json($datos_foro);
    }
}

/* Select idForo, nombre , mensaje , datos , created_at from datosForo JOIN usuarios ON idUsuario=username; */

/* $profesor_materia = DB::table('profesor_dicta_materia')
->select('usuarios.username AS cedulaProfesor', 'usuarios.nombre AS nombreProfesor', 'materias.id AS idMateria', 'materias.nombre AS nombreMateria')
->join('materias', 'materias.id', '=', 'profesor_dicta_materia.idMateria')
->join('usuarios', 'usuarios.username', '=', 'profesor_dicta_materia.idProfesor')
->get(); */