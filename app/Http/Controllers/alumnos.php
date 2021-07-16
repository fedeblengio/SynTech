<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class alumnos extends Controller
{
  
    public function index(Request $request)
    {
      
    }

    public function store(Request $request)
    {
        
    }

   
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

    public function update(Request $request, $id)
    {
       
    }

    public function destroy($id)
    {
    
    }
}
