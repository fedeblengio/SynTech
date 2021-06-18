<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class alumnos extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
/* 
        select grupos_tienen_profesor.idMateria , materias.nombre , grupos_tienen_profesor.idGrupo, idAlumnos from  alumnos_pertenecen_grupos 
        JOIN  grupos_tienen_profesor ON alumnos_pertenecen_grupos.idGrupo = grupos_tienen_profesor.idGrupo 
        JOIN materias ON materias.id= grupos_tienen_profesor.idMateria;

        $profesor_materia = DB::table('profesor_dicta_materia')
        ->select('usuarios.username AS cedulaProfesor', 'usuarios.nombre AS nombreProfesor', 'materias.id AS idMateria', 'materias.nombre AS nombreMateria')
        ->join('materias', 'materias.id', '=', 'profesor_dicta_materia.idMateria')
        ->join('usuarios', 'usuarios.username', '=', 'profesor_dicta_materia.idProfesor')
        ->get();

        return response()->json(DB::table('vista_alumno_grupo_profesor')->where("idAlumnos", $request->idAlumno)->get()); */

        $alumno_grupo_profesor = DB::table('alumnos_pertenecen_grupos')
        ->select('grupos_tienen_profesor.idMateria', 'materias.nombre', 'grupos_tienen_profesor.idGrupo', 'idAlumnos')
        ->join('grupos_tienen_profesor', 'alumnos_pertenecen_grupos.idGrupo', '=', 'grupos_tienen_profesor.idGrupo')
        ->join('materias', 'materias.id', '=', 'grupos_tienen_profesor.idMateria')
        ->where('idAlumnos', $request->idAlumno)
        ->get();
        
        return response()->json($alumno_grupo_profesor);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
