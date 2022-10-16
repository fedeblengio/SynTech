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
        $peticionSQL = $this->getGruposProfesor($request);

        $dataResponse = array();
        $grupo2 = "";

        foreach ($peticionSQL as $p) {
            $grupo=$p->idGrupo;
            if ($grupo != $grupo2) {
                $peticionSQLFiltrada = $this->getMateriasOfProfesorGrupo($request, $p);

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
        $idGrupo = $this->getGruposAlumnos($request);

        $agendaClase = $this->getAgendaClaseOfGrupo($idGrupo[0]);


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
        $idGrupo = $this->getGruposAlumnoDeletedAtNull($request);

        $agendaClase = $this->getAgendaClaseOfGrupo($idGrupo[0]);



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

        $agendaClase = $this->getAgendaClaseProfesor($request);

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
        $agendaClase = $this->getAgendaClaseProfesorWithDetails($request);

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

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getGruposProfesor(Request $request): \Illuminate\Support\Collection
    {
        $peticionSQL = DB::table('grupos')
            ->select('grupos.idGrupo')
            ->join('grupos_tienen_profesor', 'grupos.idGrupo', '=', 'grupos_tienen_profesor.idGrupo')
            ->where('grupos_tienen_profesor.idProfesor', $request->idUsuario)
            ->get();
        return $peticionSQL;
    }

    /**
     * @param Request $request
     * @param $p
     * @return \Illuminate\Support\Collection
     */
    public function getMateriasOfProfesorGrupo(Request $request, $p): \Illuminate\Support\Collection
    {
        $peticionSQLFiltrada = DB::table('grupos_tienen_profesor')
            ->select('grupos_tienen_profesor.idGrupo', 'grupos_tienen_profesor.idMateria', 'materias.nombre')
            ->join('materias', 'grupos_tienen_profesor.idMateria', '=', 'materias.id')
            ->where('grupos_tienen_profesor.idProfesor', $request->idUsuario)
            ->where('grupos_tienen_profesor.idGrupo', $p->idGrupo)
            ->get();
        return $peticionSQLFiltrada;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getGruposAlumnos(Request $request): \Illuminate\Support\Collection
    {
        $idGrupo = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
            ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
            ->get();
        return $idGrupo;
    }

    /**
     * @param $idGrupo
     * @return \Illuminate\Support\Collection
     */
    public function getAgendaClaseOfGrupo($idGrupo): \Illuminate\Support\Collection
    {
        $agendaClase = DB::table('agenda_clase_virtual')
            ->select('idProfesor', 'idGrupo', 'idMateria', 'fecha_inicio', 'fecha_fin')
            ->where('idGrupo', $idGrupo->idGrupo)
            ->orderBy('fecha_inicio', 'asc')
            ->get();
        return $agendaClase;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getGruposAlumnoDeletedAtNull(Request $request): \Illuminate\Support\Collection
    {
        $idGrupo = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
            ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
            ->where('alumnos_pertenecen_grupos.deleted_at', NULL)
            ->get();
        return $idGrupo;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getAgendaClaseProfesor(Request $request): \Illuminate\Support\Collection
    {
        $agendaClase = DB::table('agenda_clase_virtual')
            ->select('id', 'idProfesor', 'idGrupo', 'idMateria', 'fecha_inicio', 'fecha_fin')
            ->where('idProfesor', $request->idUsuario)
            ->orderBy('fecha_inicio', 'asc')
            ->get();
        return $agendaClase;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getAgendaClaseProfesorWithDetails(Request $request): \Illuminate\Support\Collection
    {
        $agendaClase = DB::table('agenda_clase_virtual')
            ->select('agenda_clase_virtual.id', 'agenda_clase_virtual.idProfesor', 'agenda_clase_virtual.idGrupo', 'agenda_clase_virtual.idMateria', 'agenda_clase_virtual.fecha_inicio', 'agenda_clase_virtual.fecha_fin')
            ->where('agenda_clase_virtual.idProfesor', $request->idUsuario)
            ->orderBy('fecha_inicio', 'asc')
            ->get();
        return $agendaClase;
    }

}
