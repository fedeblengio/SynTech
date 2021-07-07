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
    public function index(Request $request)
    {
      
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
