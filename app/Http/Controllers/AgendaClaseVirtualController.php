<?php

namespace App\Http\Controllers;
use App\Models\agendaClaseVirtual;
use App\Models\materia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Http\Request;

class AgendaClaseVirtualController extends Controller
{
    
    public function store(Request $request)
    {
      
        try {
                $agendarClaseVirtual = new agendaClaseVirtual;
                $agendarClaseVirtual->idProfesor = $request->idProfesor;
                $agendarClaseVirtual->idMateria = $request->idMateria;
                $agendarClaseVirtual->idGrupo = $request->idGrupo;
                $agendarClaseVirtual->fecha_inicio = $request->fecha_inicio;
                $agendarClaseVirtual->fecha_fin = $request->fecha_fin;
                $agendarClaseVirtual->save();
                return response()->json(['status' => 'Success'], 200);
           
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function show(Request $request)
    {
        if ($request->ou == 'Profesor') {
            return  self::consultaProfesor($request);
        } else if ($request->ou == 'Alumno') {
            return self::consultaAlumno($request);
        }
    }

    public function consultaAlumno(Request $request)
    {
        $idGrupo = DB::table('alumnos_pertenecen_grupos')
        ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
        ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
        ->get();

        $agendaClase = DB::table('agenda_clase_virtual')
        ->select('idProfesor', 'idGrupo', 'idMateria', 'fecha_inicio', 'fecha_fin')
        ->where('idGrupo', $idGrupo[0]->idGrupo)
        ->orderBy('fecha_inicio', 'asc')
        ->get();


        
        /* $agendaClase=agendaClaseVirtual::all()->where('idGrupo', $idGrupo[0]->idGrupo); */

        $dataResponse = array();
        foreach ($agendaClase as $p) {
            $fecha_actual = Carbon::now()->subHours(3);
            $fecha_inicio = Carbon::parse($p->fecha_inicio);
    
    
                if($fecha_inicio->gt($fecha_actual)){
                    $materia=materia::where('id', $p->idMateria)->first();

                    $datos = [
                        /* "id" => $p->id, */
                        "idProfesor" => $p->idProfesor,
                        "idGrupo" => $p->idGrupo,
                        "idMateria" => $p->idMateria,
                        "materia" => $materia->nombre,
                        "fecha_inicio" => $p->fecha_inicio,
                        "fecha_fin" => $p->fecha_fin,
                    ];
                   
                    array_push($dataResponse, $datos);
            
            
                    }
                    
                    }
            
                 return response()->json($dataResponse);
       
    }

    public function consultaProfesor(Request $request){
        $agendaClase=agendaClaseVirtual::all()->where('idProfesor', $request->idUsuario);
        $dataResponse = array();
        foreach ($agendaClase as $p) {

        $materia=materia::where('id', $p->idMateria)->first();

        $datos = [
            "id" => $p->id,
            "idProfesor" => $p->idProfesor,
            "idGrupo" => $p->idGrupo,
            "idMateria" => $p->idMateria,
            "materia" => $materia->nombre,
            "fecha_inicio" => $p->fecha_inicio,
            "fecha_fin" => $p->fecha_fin,
        ];
        
       array_push($dataResponse, $datos);


        }
        
        return response()->json($dataResponse);

    }


    public function update(Request $request){
       
        try {
         
                $agendarClaseVirtual = agendaClaseVirtual::where('id', $request->id)->first();
                $agendarClaseVirtual->fecha_inicio = $request->fecha_inicio;
                $agendarClaseVirtual->fecha_fin = $request->fecha_fin;
                $agendarClaseVirtual->save();

                return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function destroy(request $request)
    {
        
        $agendaClaseVirtual = agendaClaseVirtual::where('id', $request->id)->first();
        try {
            $agendaClaseVirtual->delete();
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }

    }

}