<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\listaClaseVirtual;
use App\Models\agendaClaseVirtual;
use App\Models\usuarios;
use Carbon\Carbon;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listarAlumnos(Request $request)
    {
        $alumnos = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo', 'alumnos_pertenecen_grupos.idAlumnos as idAlumnos', 'usuarios.nombre as nombreAlumno', 'usuarios.imagen_perfil')
            ->join('usuarios', 'usuarios.username', '=', 'alumnos_pertenecen_grupos.idAlumnos')
            ->join('profesor_estan_grupo_foro', 'profesor_estan_grupo_foro.idGrupo', '=', 'alumnos_pertenecen_grupos.idGrupo')
            ->where('alumnos_pertenecen_grupos.idGrupo', $request->idGrupo)
            ->where('profesor_estan_grupo_foro.idMateria', $request->idMateria)
            ->get();
        $profesor = DB::table('profesor_estan_grupo_foro')
            ->select('profesor_estan_grupo_foro.idGrupo AS idGrupo', 'profesor_estan_grupo_foro.idProfesor', 'usuarios.nombre as nombreProfesor', 'usuarios.imagen_perfil')
            ->join('usuarios', 'usuarios.username', '=', 'profesor_estan_grupo_foro.idProfesor')
            ->where('profesor_estan_grupo_foro.idGrupo', $request->idGrupo)
            ->where('profesor_estan_grupo_foro.idMateria', $request->idMateria)
            ->get();

        $p = [
            "idGrupo" => $profesor[0]->idGrupo,
            "idProfesor" => $profesor[0]->idProfesor,
            "nombre" => $profesor[0]->nombreProfesor,
            "imagen_perfil" => base64_encode(Storage::disk('ftp')->get($profesor[0]->imagen_perfil)),
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $listaPresentes = explode(',', $request->presentes);
            $listaAusentes = explode(',', $request->ausentes);

            foreach ($listaPresentes as $presente) {
                DB::insert('INSERT into lista_aula_virtual (idClase, idAlumnos, asistencia, created_at , updated_at) VALUES (?, ?, ?, ? , ?)', [$request->idClase, $presente, 1, Carbon::now(), Carbon::now()]);
            }
            foreach ($listaAusentes as $ausente) {
                DB::insert('INSERT into lista_aula_virtual (idClase, idAlumnos, asistencia, created_at , updated_at) VALUES (?, ?, ?, ? , ?)', [$request->idClase, $ausente, 0, Carbon::now(), Carbon::now()]);
            }


            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }


    public function index(Request $request)
    {
        return $request->idMateria ? self::registroListaMaterias($request->idMateria, $request->idProfesor) :   self::registroListarTodo($request->idProfesor);
    }

    public function registroListaMaterias($idMateria, $idProfesor)
    {
        return response()->json(DB::table('lista_aula_virtual')
        ->select('lista_aula_virtual.idClase','agenda_clase_virtual.idGrupo','agenda_clase_virtual.idProfesor as IdProfesor', 'materias.nombre as materia' , 'lista_aula_virtual.created_at')
        ->join('agenda_clase_virtual', 'lista_aula_virtual.idClase', '=', 'agenda_clase_virtual.id')
        ->join('materias', 'agenda_clase_virtual.idMateria', '=', 'materias.id')
        ->where('agenda_clase_virtual.idProfesor', $idProfesor)
        ->where('agenda_clase_virtual.idMateria', $idMateria)
        ->get());
    }

    public function registroListarTodo($idProfesor)
    {
        return response()->json(DB::table('lista_aula_virtual')
        ->select('lista_aula_virtual.idClase','agenda_clase_virtual.idGrupo','agenda_clase_virtual.idProfesor as IdProfesor', 'materias.nombre as materia' , 'lista_aula_virtual.created_at')
        ->join('agenda_clase_virtual', 'lista_aula_virtual.idClase', '=', 'agenda_clase_virtual.id')
        ->join('materias', 'agenda_clase_virtual.idMateria', '=', 'materias.id')
        ->where('agenda_clase_virtual.idProfesor', $idProfesor)
        ->get());
    }


    public function mostrarFaltasTotalesGlobal(Request $request)
    {
        return response()->json(DB::table('lista_aula_virtual')
            ->select('lista_aula_virtual.idAlumnos', DB::raw('count(*) as total'))
            ->join('agenda_clase_virtual', 'lista_aula_virtual.idClase', '=', 'agenda_clase_virtual.id')
            ->where('agenda_clase_virtual.idMateria', $request->idMateria)
            ->where('agenda_clase_virtual.idGrupo', $request->idGrupo)
            ->where('lista_aula_virtual.asistencia', "0")
            ->groupBy('idAlumnos')
            ->get());
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
            ->whereYear('created_at', $fecha_1('Y'))        //->whereYear('created_at', date('Y')) EJEMPLO->whereYear('created_at', date('Y'))
            ->groupBy('idAlumnos')
            ->get();
        /* 
        $alumno=$peticionSQL[0]->idAlumnos;
        $faltas=0;

        $dataResponse = array();
        foreach ($peticionSQL as $p) {
            $fecha_1 = Carbon::parse($request->fecha_filtro)->format('m');
            $fecha_2 = Carbon::parse($p->created_at)->format('m');

            if($fecha_1 === $fecha_2){  ///COMPARA FECHAS PARA VER SI ES EL MIMSMO MES DE FILTRO
                $alumno2=$p->idAlumnos;

            if($alumno == $alumno2){ ///COMPARA SI EL ALUMNO SIGUE SIENDO EL MISMO PARA PODER SUMARLE LA FATLA
                $faltas=$faltas+1;
                $alumno=$p->idAlumnos;
            }else{ ///CUANDO EL ALUMNO ES DIFERENTE, TOMA EL ANTERIOR ALUMNO Y GUARADA LAS FALTAS, Y PONE FALTAS=1 PARA EL NUEVO y ALUMNO LO IGUALA AL NUEVO
                $datos = [
                    "idAlumnos" => $alumno,
                    "faltas" => $faltas,
                ];
               
                array_push($dataResponse, $datos);
                $fatlas=1;
                $alumno=$p->idAlumnos;
            }

            }
                    } */

        return response()->json($peticionSQL);
    }



    public function registroClase(Request $request)
    {

        $registroClase = listaClaseVirtual::all()->where('idClase', $request->idClase);
        $chequeo = "";
        $dataResponse = array();
        foreach ($registroClase as $p) {
            $usuarios = usuarios::where('username', $p->idAlumnos)->first();
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

    public function registroAlumno(Request $request)
    {

        $registroAlumno = listaClaseVirtual::all()->where('idALumnos', $request->idAlumnos);


        foreach ($registroAlumno as $p) {
            $usuarios = usuarios::where('username', $p->idAlumnos)->first();
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






    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        DB::delete('delete from lista_aula_virtual where idClase="' . $request->idClase . '";');
        return  self::store($request);
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
