<?php

namespace App\Http\Controllers;

use App\Models\agendaClaseVirtual;
use App\Models\alumnoGrupo;
use App\Models\materia;
use App\Models\GruposProfesores;
use App\Models\listaClaseVirtual;
use App\Http\Controllers\RegistrosController;
use App\Models\usuarios;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Http\Request;

class AgendaClaseVirtualController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'idProfesor' => 'required',
            'idMateria' => 'required',
            'idGrupo' => 'required | string',
            'fecha_fin' => 'required',
            'fecha_inicio' => 'required',
        ]);

        try {
            $agendarClaseVirtual = new agendaClaseVirtual;
            $agendarClaseVirtual->fill($request->all());
            $agendarClaseVirtual->save();
            RegistrosController::store("Clase Virtual", $request->header('token'), "CREATE", $request->idGrupo);
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function show($id, $idGrupo)
    {
        $usuario = usuarios::findOrFail($id);

        if ($usuario->ou == 'Profesor') {
            return self::consultaProfesor($usuario, $idGrupo);
        } else if ($usuario->ou == 'Alumno') {
            return self::consultaAlumno($usuario, $idGrupo);
        }
    }

    public function consultaEventos(Request $request, $id)
    {
        $usuario = usuarios::findOrFail($id);
        if ($usuario->ou == 'Profesor') {
            return self::consultaProfesorEvento($usuario);
        } else if ($usuario->ou == 'Alumno') {
            return self::consultaAlumnoEvento($usuario);
        }
    }

    public function getMateriasFromProfesorGrupo($idProfesor, $idGrupo)
    {
        $materiasId = GruposProfesores::where('idProfesor', $idProfesor)->where('idGrupo', $idGrupo)->pluck('idMateria');
        return materia::whereIn('id', $materiasId)->get();
    }

    public function consultaAlumno($usuario, $idGrupo)
    {

        $agendaClase = $this->getAgendaClaseOfGrupo($idGrupo);

        $dataResponse = array();
        foreach ($agendaClase as $clase) {
            $materia = materia::find($clase->idMateria);
            $datos = [
                "id" => $clase->id,
                "idProfesor" => $clase->idProfesor,
                "idGrupo" => $clase->idGrupo,
                "idMateria" => $clase->idMateria,
                "materia" => $materia->nombre,
                "fecha_inicio" => $clase->fecha_inicio,
                "fecha_fin" => $clase->fecha_fin,
            ];
            array_push($dataResponse, $datos);

        }

        return response()->json($dataResponse);

    }

    private function getAlumnoGrupos($usuario)
    {
        return alumnoGrupo::where('idAlumnos', $usuario->id)->pluck('idGrupo');
    }

    public function consultaAlumnoEvento($usuario)
    {
        $idGrupos = $this->getAlumnoGrupos($usuario);

        $agendaClase = $this->getAgendaClasesVirtualTodayAlumno($idGrupos);

        $dataResponse = array();
        foreach ($agendaClase as $clase) {
            $materia = materia::where('id', $clase->idMateria)->first();
            $datos = [
                "id" => $clase->id,
                "idProfesor" => $clase->idProfesor,
                "idGrupo" => $clase->idGrupo,
                "idMateria" => $clase->idMateria,
                "materia" => $materia->nombre,
                "fecha_inicio" => $clase->fecha_inicio,
                "fecha_fin" => $clase->fecha_fin,
            ];

            array_push($dataResponse, $datos);
        }

        return response()->json($dataResponse);
    }

    public function consultaProfesor($usuario, $idGrupo)
    {
        $agendaClase = $this->getAgendaClaseProfesor($usuario, $idGrupo);

        $dataResponse = array();

        foreach ($agendaClase as $clase) {
            $materia = materia::where('id', $clase->idMateria)->first();

            $datos = [
                "id" => $clase->id,
                "idProfesor" => $clase->idProfesor,
                "idGrupo" => $clase->idGrupo,
                "idMateria" => $clase->idMateria,
                "materia" => $materia->nombre,
                "fecha_inicio" => $clase->fecha_inicio,
                "fecha_fin" => $clase->fecha_fin,
            ];

            array_push($dataResponse, $datos);
        }
        return response()->json($dataResponse);
    }

    private function getAgendaClaseProfesor($usuario, $idGrupo)
    {
        return agendaClaseVirtual::where('idProfesor', $usuario->id)->where('idGrupo', $idGrupo)->whereDate('fecha_fin', '>', Carbon::now())->orderBy('fecha_inicio', 'asc')->get();
    }

    public function consultaProfesorEvento($usuario)
    {
        $agendaClase = $this->getAgendaClaseVirtualToday($usuario);
        $dataResponse = array();

        foreach ($agendaClase as $clase) {
            $materia = materia::where('id', $clase->idMateria)->first();
            $datos = [
                "id" => $clase->id,
                "idProfesor" => $clase->idProfesor,
                "idGrupo" => $clase->idGrupo,
                "idMateria" => $clase->idMateria,
                "materia" => $materia->nombre,
                "fecha_inicio" => $clase->fecha_inicio,
                "fecha_fin" => $clase->fecha_fin,
            ];
            array_push($dataResponse, $datos);
        }

        return response()->json($dataResponse);

    }

    public function destroy(request $request, $id)
    {
        $agendaClaseVirtual = agendaClaseVirtual::findOrFail($id);

        try {
            $listaClaseVirtual = listaClaseVirtual::where('idClase', $id)->delete();
            $agendaClaseVirtual->delete();
            RegistrosController::store("Clase Virtual", $request->token, "DELETE", "");

            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }

    }

    public function getGruposProfesor(Request $request)
    {
        $peticionSQL = DB::table('grupos')
            ->select('grupos.idGrupo')
            ->join('grupos_tienen_profesor', 'grupos.idGrupo', '=', 'grupos_tienen_profesor.idGrupo')
            ->where('grupos_tienen_profesor.idProfesor', $request->idUsuario)
            ->get();
        return $peticionSQL;
    }


    public function getGruposAlumnos(Request $request)
    {
        $idGrupo = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
            ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
            ->get();
        return $idGrupo;
    }


    public function getAgendaClaseOfGrupo($idGrupo)
    {
        return agendaClaseVirtual::where('idGrupo', $idGrupo)->whereDate('fecha_fin', '>', Carbon::now())->orderBy('fecha_inicio', 'asc')->get();
    }

    public function getAgendaClasesVirtualTodayAlumno($idGrupos)
    {
        return agendaClaseVirtual::whereIn('idGrupo', $idGrupos)->whereDate('fecha_fin', '>', Carbon::now())->whereDate('fecha_inicio', Carbon::today())->orderBy('fecha_inicio', 'asc')->get();
    }


    public function getAgendaClaseVirtualToday($usuario)
    {
        return agendaClaseVirtual::where('idProfesor', $usuario->id)->whereDate('fecha_fin', '>', Carbon::now())->whereDate('fecha_inicio', Carbon::today())->orderBy('fecha_inicio', 'asc')->get();
    }
}