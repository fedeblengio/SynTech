<?php

namespace App\Http\Controllers;
use App\Models\agendaClaseVirtual;
use App\Models\materia;
use App\Models\GruposProfesores;
use App\Models\listaClaseVirtual;
use App\Http\Controllers\RegistrosController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Http\Request;

class AgendaClaseVirtualController extends Controller
{
    
    public function store(Request $request)
    {
       
        try {
            $materia = materia::where('nombre', $request->materia)->first();


                $agendarClaseVirtual = new agendaClaseVirtual;
                $agendarClaseVirtual->idProfesor = $request->idProfesor;
                $agendarClaseVirtual->idMateria = $materia->id;
                $agendarClaseVirtual->idGrupo = $request->idGrupo;
                $agendarClaseVirtual->fecha_inicio = $request->fecha_inicio;
                $agendarClaseVirtual->fecha_fin = $request->fecha_fin;
                $agendarClaseVirtual->save();
       
                RegistrosController::store("Clase Virtual",$request->header('token'),"CREATE",$request->idGrupo);

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

    
    public function consultaEvento(Request $request)
    {
        if ($request->ou == 'Profesor') {
            return  self::consultaProfesorEvento($request);
        } else if ($request->ou == 'Alumno') {
            return self::consultaAlumnoEvento($request);
        }
    }

      
    public function consultaGruposMateria(Request $request)
    {
        $peticionSQL=DB::table('grupos')
        ->select('grupos.idGrupo')
        ->join('grupos_tienen_profesor', 'grupos.idGrupo', '=', 'grupos_tienen_profesor.idGrupo')
        ->where('grupos_tienen_profesor.idProfesor', $request->idUsuario)
        ->get();

        $dataResponse = array();
        $grupo2 = "";

        foreach ($peticionSQL as $p) {
            $grupo=$p->idGrupo;
            if ($grupo != $grupo2) {
            $peticionSQLFiltrada = DB::table('grupos_tienen_profesor')
            ->select('grupos_tienen_profesor.idGrupo', 'grupos_tienen_profesor.idMateria', 'materias.nombre')
            ->join('materias', 'grupos_tienen_profesor.idMateria', '=', 'materias.id')
            ->where('grupos_tienen_profesor.idProfesor', $request->idUsuario)
            ->where('grupos_tienen_profesor.idGrupo', $p->idGrupo)
            ->get();

            $materias = array();
           

            foreach ($peticionSQLFiltrada as $p2) {

            array_push($materias, $p2->nombre);

            }


        $p2 = [
            "idGrupo" => $p->idGrupo,
            "materias" => $materias
        ];

        array_push($dataResponse, $p2);
        $grupo2=$p->idGrupo;
    }
}
    return response()->json($dataResponse);
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
            $fecha_actual = Carbon::now()->addMinutes(23);
            $fecha_inicio = Carbon::parse($p->fecha_fin);
    
    
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

    public function consultaAlumnoEvento(Request $request)
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
            $fecha_actual2 = Carbon::now()->addMinutes(23);
            $fecha_inicio1 = Carbon::parse($p->fecha_fin);
    
    
               
            $fecha_actual = Carbon::now()->addMinutes(23)->format('d-m-Y');
            $fecha_inicio = Carbon::parse($p->fecha_inicio)->format('d-m-Y');
            $fecha_fin = Carbon::parse($p->fecha_fin)->format('d-m-Y');
            
    
                if($fecha_inicio1->gt($fecha_actual2)){
                if($fecha_inicio === $fecha_actual || $fecha_fin === $fecha_actual ){
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
                    }
            
                 return response()->json($dataResponse);
       
    }

    public function consultaProfesor(Request $request){

        $agendaClase = DB::table('agenda_clase_virtual')
        ->select('id', 'idProfesor', 'idGrupo', 'idMateria', 'fecha_inicio', 'fecha_fin')
        ->where('idProfesor', $request->idUsuario)
        ->orderBy('fecha_inicio', 'asc')
        ->get();

        $dataResponse = array();

        foreach ($agendaClase as $p) {

        $fecha_actual = Carbon::now()->addMinutes(23);
        $fecha_inicio = Carbon::parse($p->fecha_fin);
    
    
        if($fecha_inicio->gt($fecha_actual)){
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
        }
        
        return response()->json($dataResponse);

    }

    public function consultaProfesorEvento(Request $request){
        $agendaClase = DB::table('agenda_clase_virtual')
        ->select('id','idProfesor', 'idGrupo', 'idMateria', 'fecha_inicio', 'fecha_fin')
        ->where('idProfesor', $request->idUsuario)
        ->orderBy('fecha_inicio', 'asc')
        ->get();

        $dataResponse = array();

        foreach ($agendaClase as $p) {

            $fecha_actual2 = Carbon::now()->addMinutes(23);
            $fecha_inicio1 = Carbon::parse($p->fecha_fin);
    
    
               
            $fecha_actual = Carbon::now()->addMinutes(23)->format('d-m-Y');
            $fecha_inicio = Carbon::parse($p->fecha_inicio)->format('d-m-Y');
            $fecha_fin = Carbon::parse($p->fecha_fin)->format('d-m-Y');
            
    
                if($fecha_inicio1->gt($fecha_actual2)){
                if($fecha_inicio === $fecha_actual || $fecha_fin === $fecha_actual){
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
        }
        }
        
        return response()->json($dataResponse);

    }




    public function destroy(request $request)
    {
        $agendaClaseVirtual = agendaClaseVirtual::where('id', $request->idClase)->first();
        DB::delete('delete from lista_aula_virtual where idClase="'.$request->idClase.'";');

        try {
            $agendaClaseVirtual->delete();

            RegistrosController::store("Clase Virtual",$request->token,"DELETE","");

            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }

    }

}
