<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Tarea;
use App\Models\GruposProfesores;
use App\Models\ProfesorTarea;
use App\Models\AlumnoEntrega;
use App\Models\archivosEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoEntregaTarea extends Controller
{

    public function index()
    {
        return response()->json(AlumnoEntrega::all());
    }

 

    /* public function store(Request $request)
    {
        try {       
            if ($request->hasFile("archivo")) {
                $nombreArchivo = $request->nombre;
                Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivo, 'r+'));
            }
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Error'], 406);
        }
    } */

    public function subirTarea(Request $request, $nombre)
    {

        $alumnoTarea = new AlumnoEntrega;
        $alumnoTarea->idTareas = $request->idTareas;
        $alumnoTarea->idAlumnos = $request->idAlumnos;
        $alumnoTarea->mensaje = $request->mensaje;
        $alumnoTarea->save();


        $nombreArchivosArray = explode(',', $request->nombre_archivos);
                if ($request->nombre_archivos) {
                    foreach ($nombreArchivosArray as $nombres) {
                        $archivosEntrega = new archivosEntrega;
                        $archivosEntrega->idTareas = $request->idTareas;
                        $archivosEntrega->idAlumnos = $request->idAlumnos;
                        $archivosEntrega->nombreArchivo = $nombres;
                        $archivosEntrega->save();

                    }
                }

    }



    

    public function listarEntregas(Request $request){

        $entregas=DB::table('profesor_crea_tareas')
                    ->select('alumno_entrega_tareas.idTareas AS idTareas', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'alumno_entrega_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario' ,'profesor_crea_tareas.idGrupo')
                    ->join('alumno_entrega_tareas', 'alumno_entrega_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
                    ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.username')   
                    ->where('profesor_crea_tareas.idGrupo',$request->idGrupo)
                    ->orderBy('profesor_crea_tareas.idTareas', 'desc')
                    ->get();

        return response()->json($entregas);

    }


    public function entregaAlumno(Request $request)
    {

        $peticionSQL = DB::table('alumno_entrega_tareas')
            ->select('alumno_entrega_tareas.idTareas AS idTareas', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'alumno_entrega_tareas.created_at AS fecha', 'alumno_entrega_tareas.calificacion AS calificacion', 'alumno_entrega_tareas.mensaje AS mensaje', 'usuarios.nombre AS nombreUsuario')
            ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.username')
            ->where('alumno_entrega_tareas.idTareas', $request->idTareas)
            ->where('alumno_entrega_tareas.idAlumnos', $request->idAlumnos)
            ->get();

        $dataResponse = array();

        foreach ($peticionSQL as $p) {

            $peticionSQLFiltrada = DB::table('archivos_entrega')
                ->select('id AS idArchivo','nombreArchivo AS archivo')
                ->where('idTareas', $p->idTareas)
                ->where('idAlumnos', $p->idAlumnos)
                ->distinct()
                ->get();

            $arrayDeArchivos = array();
            $postAuthor = $p->idAlumnos;

            $imgPerfil = DB::table('usuarios')
                ->select('imagen_perfil')
                ->where('username', $postAuthor)
                ->get();

            $img = base64_encode(Storage::disk('ftp')->get($imgPerfil[0]->imagen_perfil));

            array_push($arrayDeArchivos, $peticionSQLFiltrada);

            
            $datos = [
                "idTareas" => $p->idTareas,
                "profile_picture" => $img,
                "idAlumnos" => $p->idAlumnos,
                "mensaje" => $p->mensaje,
                "calificacion" => $p->calificacion,
                "nombreUsuario" => $p->nombreUsuario,
                "fecha" => $p->fecha
            ];

            $p = [
                "data" => $datos,
                "archivos" => $arrayDeArchivos,
            ];

            array_push($dataResponse, $p);
        }
        return response()->json($dataResponse);
    }


    public function corregirEntrega(Request $request)
    {

        $existe = AlumnoEntrega::where('idTareas', $request->idTareas)->where('idAlumnos', $request->idAlumnos)->first();
        try {
            if ($existe) {

                DB::update('UPDATE alumno_entrega_tareas SET calificacion="' . $request->calificacion . '" , mensaje_profesor="' . $request->mensaje . '" WHERE idTareas="' . $request->idTareas . '" AND idAlumnos="' . $request->idAlumnos . '";');
                return response()->json(['status' => 'Success'], 200);
            }
            return response()->json(['status' => 'Bad Request'], 400);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }


}
