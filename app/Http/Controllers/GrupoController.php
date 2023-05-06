<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\listaClaseVirtual;
use App\Models\agendaClaseVirtual;
use App\Models\usuarios;
use App\Http\Controllers\RegistrosController;
use Carbon\Carbon;
use App\PDF;

class GrupoController extends Controller
{

    public function listarAlumnos($idGrupo,$idMateria)
    {
        $alumnos = $this->getAlumnosGrupoMateria($idGrupo,$idMateria);
        $profesor = $this->getProfesorGrupoMateria($idGrupo,$idMateria);

        $p = [
            "idGrupo" => $profesor->idGrupo,
            "idProfesor" => $profesor->idProfesor,
            "nombre" => $profesor->nombreProfesor,
            "imagen_perfil" => base64_encode(Storage::disk('ftp')->get($profesor->imagen_perfil)),
        ];
        $listaAlumnos = array();

        foreach ($alumnos as $a) {

            $alumno = [
                "idGrupo" => $a->idGrupo,
                "idAlumnos" => $a->idAlumnos,
                "nombre" => $a->nombreAlumno,
                "imagen_perfil" => base64_encode(Storage::disk('ftp')->get($a->imagen_perfil)),
            ];
            array_push($listaAlumnos, $alumno);
        }


        $data = [
            "Profesor" => $p,
            "Alumnos" => $listaAlumnos,
        ];
        return response()->json($data);
    }

   
    public function store(Request $request)
    {
        try {

            foreach ($request->presentes as $presente) {
                $this->insertPresentesAulaVirtual($request, $presente);
            }
            foreach ($request->ausentes as $ausente) {
                $this->insertAusentesAulaVirtual($request, $ausente);
            }
            RegistrosController::store("LISTA",$request->header('token'),"CREATE","");

         return response()->json(['status' => 'Success'], 200);
       } catch (\Throwable $th) {
           return response()->json(['status' => 'Bad Request'], 400);
        }
    }


    public function index(Request $request)
    {
        return  self::registroListarTodo($request->idProfesor);
    }

    public function registroListarTodo($idProfesor)
    {
        return response()->json(DB::table('lista_aula_virtual')
            ->select('lista_aula_virtual.idClase', 'agenda_clase_virtual.idGrupo', 'agenda_clase_virtual.idProfesor as IdProfesor', 'materias.nombre as materia', 'materias.id AS idMateria', 'lista_aula_virtual.created_at')
            ->join('agenda_clase_virtual', 'lista_aula_virtual.idClase', '=', 'agenda_clase_virtual.id')
            ->join('materias', 'agenda_clase_virtual.idMateria', '=', 'materias.id')
            ->where('agenda_clase_virtual.idProfesor', $idProfesor)
            ->distinct()
            ->get());
    }

    public function mostrarFaltasTotalesGlobal(Request $request)
    {

        $alumnos = $this->getAlumnosGrupoMateria($request);
        $cantClasesListadas = $this->getCantidadClases($request);

        $listadoAlumnos = array();

    foreach ($alumnos as $a) {

        $cantFaltas = $this->getCantidadFaltas($request, $a);

        $fechas_ausencia = $this->getFechasFaltas($request, $a);
        $alumno = [
            "idAlumno" => $a->idAlumnos,
            "nombreAlumno" => $a->nombreAlumno,
            "fechas_ausencia"=> $fechas_ausencia,
            "cantidad_faltas" => $cantFaltas[0]->totalClase,
            "total_clases" => $cantClasesListadas[0]->totalClase
        ];
        array_push($listadoAlumnos, $alumno);
    }
    return response()->json($listadoAlumnos);

    }

    public function mostrarFaltasTotalesGlobalPorMes(Request $request)
    {

        $fecha_1 = Carbon::parse($request->fecha_filtro);
        $peticionSQL = DB::table('lista_aula_virtual')
            ->select('lista_aula_virtual.idAlumnos', DB::raw('count(*) as total'))
            ->join('agenda_clase_virtual', 'lista_aula_virtual.idClase', '=', 'agenda_clase_virtual.id')
            ->where('agenda_clase_virtual.idMateria', $request->idMateria)
            ->where('agenda_clase_virtual.idGrupo', $request->idGrupo)
            ->where('lista_aula_virtual.asistencia', "0")
            ->whereYear('created_at', $fecha_1('Y'))   
            ->groupBy('idAlumnos')
            ->get();


        return response()->json($peticionSQL);
    }

    public function registroClase(Request $request)
    {

        $registroClase = listaClaseVirtual::all()->where('idClase', $request->idClase);
        $chequeo = "";
        $dataResponse = array();
        foreach ($registroClase as $p) {
            $usuarios = usuarios::where('id', $p->idAlumnos)->first();
            if ($p->asistencia == "1") {
                $chequeo = "Presente";
            } else {
                $chequeo = "Ausente";
            }
            $datos = [
                "idClase" => $p->idClase,
                "idAlumno" => $p->idAlumnos,
                "asistencia" => $chequeo,
                "nombre" => $usuarios->nombre,
                "imagen_perfil" => base64_encode(Storage::disk('ftp')->get($usuarios->imagen_perfil)),
            ];

            array_push($dataResponse, $datos);
        }

        return response()->json($dataResponse);
    }

    public function registroAlumno(Request $request)
    {

        $registroAlumno = listaClaseVirtual::all()->where('idALumnos', $request->idAlumnos);


        foreach ($registroAlumno as $p) {
            $usuarios = usuarios::where('id', $p->idAlumnos)->first();
            if ($p->asistencia == "1") {
                $chequeo = true;
            } else {
                $chequeo = false;
            }
            $datos = [
                "idClase" => $p->idClase,
                "idAlumno" => $p->idAlumnos,
                "asistencia" => $chequeo,
                "nombre" => $usuarios->nombre,
                "imagen_perfil" => base64_encode(Storage::disk('ftp')->get($usuarios->imagen_perfil)),
            ];

            array_push($dataResponse, $datos);
        }

        return response()->json($dataResponse);
    }



    public function update(Request $request)
    {
        try {

            foreach ($request->presentes as $presente) {
                DB::update('UPDATE lista_aula_virtual set asistencia = 1 where idAlumnos = ?  AND idClase= ?', [$presente,  $request->idClase]);
            }
            foreach ($request->ausentes as $ausente) {
                DB::update('UPDATE lista_aula_virtual set asistencia = 0 where idAlumnos = ? AND idClase= ?', [$ausente, $request->idClase]);
            }

            RegistrosController::store("LISTA",$request->header('token'),"UPDATE","");

            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function getAlumnosGrupoMateria($idGrupo,$idMateria)
    {
        $alumnos = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo', 'alumnos_pertenecen_grupos.idAlumnos as idAlumnos', 'usuarios.nombre as nombreAlumno', 'usuarios.imagen_perfil')
            ->join('usuarios', 'usuarios.id', '=', 'alumnos_pertenecen_grupos.idAlumnos')
            ->join('profesor_estan_grupo_foro', 'profesor_estan_grupo_foro.idGrupo', '=', 'alumnos_pertenecen_grupos.idGrupo')
            ->where('alumnos_pertenecen_grupos.idGrupo', $idGrupo)
            ->where('profesor_estan_grupo_foro.idMateria', $idMateria)
            ->get();
        return $alumnos;
    }


    public function getProfesorGrupoMateria($idGrupo,$idMateria)
    {
        $profesor = DB::table('profesor_estan_grupo_foro')
            ->select('profesor_estan_grupo_foro.idGrupo AS idGrupo', 'profesor_estan_grupo_foro.idProfesor', 'usuarios.nombre as nombreProfesor', 'usuarios.imagen_perfil')
            ->join('usuarios', 'usuarios.id', '=', 'profesor_estan_grupo_foro.idProfesor')
            ->where('profesor_estan_grupo_foro.idGrupo', $idGrupo)
            ->where('profesor_estan_grupo_foro.idMateria', $idMateria)
            ->first();
        return $profesor;
    }

  
    public function insertPresentesAulaVirtual(Request $request, $presente): void
    {
        DB::insert('INSERT into lista_aula_virtual (idClase, idAlumnos, asistencia, created_at , updated_at) VALUES (?, ?, ?, ? , ?)', [$request->idClase, $presente, 1, Carbon::now(), Carbon::now()]);
    }


    public function insertAusentesAulaVirtual(Request $request, $ausente): void
    {
        DB::insert('INSERT into lista_aula_virtual (idClase, idAlumnos, asistencia, created_at , updated_at) VALUES (?, ?, ?, ? , ?)', [$request->idClase, $ausente, 0, Carbon::now(), Carbon::now()]);
    }

   
    public function getCantidadClases(Request $request): \Illuminate\Support\Collection
    {
        $cantClasesListadas = DB::table('agenda_clase_virtual')
            ->select(DB::raw('count(*) as totalClase'))
            ->join('lista_aula_virtual', 'agenda_clase_virtual.id', '=', 'lista_aula_virtual.idClase')
            ->where('agenda_clase_virtual.idMateria', $request->idMateria)
            ->where('agenda_clase_virtual.idGrupo', $request->idGrupo)
            ->get();
        return $cantClasesListadas;
    }

  
    public function getCantidadFaltas(Request $request, $a): \Illuminate\Support\Collection
    {
        $cantFaltas = DB::table('agenda_clase_virtual')
            ->select(DB::raw('count(*) as totalClase'))
            ->join('lista_aula_virtual', 'agenda_clase_virtual.id', '=', 'lista_aula_virtual.idClase')
            ->where('agenda_clase_virtual.idMateria', $request->idMateria)
            ->where('agenda_clase_virtual.idGrupo', $request->idGrupo)
            ->where('lista_aula_virtual.idAlumnos', $a->idAlumnos)
            ->where('lista_aula_virtual.asistencia', '0')
            ->get();
        return $cantFaltas;
    }

   
    public function getFechasFaltas(Request $request, $a): \Illuminate\Support\Collection
    {
        $fechas_ausencia = DB::table('agenda_clase_virtual')
            ->select('agenda_clase_virtual.fecha_inicio as fecha_clase')
            ->join('lista_aula_virtual', 'agenda_clase_virtual.id', '=', 'lista_aula_virtual.idClase')
            ->where('agenda_clase_virtual.idMateria', $request->idMateria)
            ->where('agenda_clase_virtual.idGrupo', $request->idGrupo)
            ->where('lista_aula_virtual.idAlumnos', $a->idAlumnos)
            ->where('lista_aula_virtual.asistencia', '0')
            ->get();
        return $fechas_ausencia;
    }
}
