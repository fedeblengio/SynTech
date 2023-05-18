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

   
    public function pasarListaClaseVirtual($idClase, Request $request)
    {
        $request->validate([
            'presentes' => 'array',
            'ausentes' => 'array',
        ]);
        try {
            foreach ($request->presentes as $presente) {
                $this->insertPresentesAulaVirtual($idClase, $presente);
            }
            foreach ($request->ausentes as $ausente) {
                $this->insertAusentesAulaVirtual($idClase, $ausente);
            }
            RegistrosController::store("LISTA",$request->header('token'),"CREATE","");

         return response()->json(['status' => 'Success'], 200);
       } catch (\Throwable $th) {
           return response()->json(['status' => 'Bad Request'], 400);
        }
    }


    public function getAllListasFromProfesor($idProfesor)
    {
       return listaClaseVirtual::query()
                ->select('lista_aula_virtual.idClase', 'agenda_clase_virtual.idGrupo', 'agenda_clase_virtual.idProfesor as IdProfesor', 'materias.nombre as materia', 'materias.id AS idMateria', 'lista_aula_virtual.created_at')
                ->join('agenda_clase_virtual', 'lista_aula_virtual.idClase', '=', 'agenda_clase_virtual.id')
                ->join('materias', 'agenda_clase_virtual.idMateria', '=', 'materias.id')
                ->where('agenda_clase_virtual.idProfesor', $idProfesor)
                ->distinct()
                ->get();
    }

    public function mostrarFaltasTotalesGlobal($idGrupo,$idMateria)
    {

        $alumnos = $this->getAlumnosGrupoMateria($idGrupo,$idMateria);
        $cantClasesListadas = $this->getCantidadClases($idGrupo,$idMateria);

        $listadoAlumnos = array();

    foreach ($alumnos as $a) {

        $cantFaltas = $this->getCantidadFaltas($idGrupo,$idMateria, $a);

        $fechas_ausencia = $this->getFechasFaltas($idGrupo,$idMateria, $a);
        $alumno = [
            "idAlumno" => $a->idAlumnos,
            "nombreAlumno" => $a->nombreAlumno,
            "fechas_ausencia"=> $fechas_ausencia,
            "cantidad_faltas" => $cantFaltas->totalClase,
            "total_clases" => $cantClasesListadas->totalClase
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

    public function registroClase($idClase)
    {

        $registroClase = listaClaseVirtual::all()->where('idClase', $idClase);
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




    public function modificarLista($idClase,Request $request)
    {
        $request->validate([
            'presentes' => 'array',
            'ausentes' => 'array',
        ]);
        try {

            foreach ($request->presentes as $presente) {
                listaClaseVirtual::where('idAlumnos', $presente)->where('idClase', $idClase)->update(['asistencia' => 1]);
               
            }
            foreach ($request->ausentes as $ausente) {
                listaClaseVirtual::where('idAlumnos', $ausente)->where('idClase', $idClase)->update(['asistencia' => 0]);
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

  
    public function insertPresentesAulaVirtual($idClase, $presente)
    {
    
        $nuevaLista = new listaClaseVirtual();
        $nuevaLista->idClase = $idClase;
        $nuevaLista->idAlumnos = $presente;
        $nuevaLista->asistencia = 1;
        $nuevaLista->save();
    }


    public function insertAusentesAulaVirtual($idClase, $ausente)
    {
        $nuevaLista = new listaClaseVirtual();
        $nuevaLista->idClase = $idClase;
        $nuevaLista->idAlumnos = $ausente;
        $nuevaLista->asistencia = 0;
        $nuevaLista->save();

    }

   
    public function getCantidadClases($idGrupo,$idMateria)
    {
        $cantClasesListadas = DB::table('agenda_clase_virtual')
            ->select(DB::raw('count(*) as totalClase'))
            ->join('lista_aula_virtual', 'agenda_clase_virtual.id', '=', 'lista_aula_virtual.idClase')
            ->where('agenda_clase_virtual.idMateria', $idMateria)
            ->where('agenda_clase_virtual.idGrupo', $idGrupo)
            ->first();
        return $cantClasesListadas;
    }

  
    public function getCantidadFaltas($idGrupo,$idMateria, $a)
    {
        $cantFaltas = DB::table('agenda_clase_virtual')
            ->select(DB::raw('count(*) as totalClase'))
            ->join('lista_aula_virtual', 'agenda_clase_virtual.id', '=', 'lista_aula_virtual.idClase')
            ->where('agenda_clase_virtual.idMateria', $idMateria)
            ->where('agenda_clase_virtual.idGrupo', $idGrupo)
            ->where('lista_aula_virtual.idAlumnos', $a->idAlumnos)
            ->where('lista_aula_virtual.asistencia', '0')
            ->first();
        return $cantFaltas;
    }

   
    public function getFechasFaltas($idGrupo,$idMateria, $a)
    {
        $fechas_ausencia = DB::table('agenda_clase_virtual')
            ->select('agenda_clase_virtual.fecha_inicio as fecha_clase')
            ->join('lista_aula_virtual', 'agenda_clase_virtual.id', '=', 'lista_aula_virtual.idClase')
            ->where('agenda_clase_virtual.idMateria', $idMateria)
            ->where('agenda_clase_virtual.idGrupo', $idGrupo)
            ->where('lista_aula_virtual.idAlumnos', $a->idAlumnos)
            ->where('lista_aula_virtual.asistencia', '0')
            ->get();
        return $fechas_ausencia;
    }
}
