<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Tarea;
use App\Models\AlumnoEntrega;
use App\Models\archivosEntrega;
use App\Models\GruposProfesores;
use App\Models\ProfesorTarea;
use App\Models\archivosTarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfesorCreaTarea extends Controller
{
    /*  public function index()
    {
        return response()->json(Tarea::all());
    } */


    /*  public function show(Request $request)
    {
        $mostrarTareasGrupos=ProfesorTarea::all()->where('idGrupo', $request->idGrupo)->where('idMateria',££££3£££££££);
        return response()->json($mostrarTareasGrupos);
    } */
    /* 
    public function traerTareasGrupo(Request $request){
        $tarea_grupo = DB::table('profesor_crea_tareas')
        ->select('profesor_crea_tareas.idGrupo AS Grupo', 'profesor_crea_tareas.idTareas AS Tareas', 'profesor_crea_tareas.IdMateria AS idMateria', 'materias.nombre AS nombreMateria', 'tareas.titulo AS tareasTitulo', 'tareas.descripcion AS tareaDescripcion', 'tareas.fecha_vencimiento AS tareaVencimiento', 'tareas.archivo AS tareaArchivo')
        ->join('tareas', 'tareas.id', '=', 'profesor_crea_tareas.idTareas')
        ->join('materias', 'materias.id', '=', 'profesor_crea_tareas.idMateria')
        ->where('profesor_crea_tareas.idGrupo', $request->idGrupo)
        ->where('profesor_crea_tareas.idMateria', $request->idMateria)
        ->get();

        return response()->json($tarea_grupo);
    }

    public function ProfesorGrupo(Request $request)
    {
        $profesorGrupo=GruposProfesores::all()->where('idProfesor', $request->idProfesor);
        return response()->json($profesorGrupo);
    } */




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
    public function tareas(Request $request)
    {
        if ($request->ou == 'Profesor') {
            return  self::crearTarea($request);
        } else if ($request->ou == 'Alumno') {
            return self::subirTarea($request);
        }
    }
    public function subirTarea(Request $request)
    {

        $alumnoTarea = new AlumnoEntrega;
        $alumnoTarea->idTareas = $request->idTareas;
        $alumnoTarea->idAlumnos = $request->idUsuario;
        $alumnoTarea->mensaje = $request->mensaje;
        $alumnoTarea->save();


        $nombreArchivosArray = explode(',', $request->nombreArchivos);
        if ($request->nombreArchivos) {
            foreach ($nombreArchivosArray as $nombres) {
                $archivosEntrega = new archivosEntrega;
                $archivosEntrega->idTareas = $request->idTareas;
                $archivosEntrega->idAlumnos = $request->idUsuario;
                $archivosEntrega->nombreArchivo = $nombres;
                $archivosEntrega->save();
            }
        }
        return response()->json(['status' => 'Success'], 200);
    }



    public function crearTarea(Request $request)
    {
        $tarea = new Tarea;
        $tarea->titulo = $request->titulo;
        $tarea->descripcion = $request->descripcion;
        $tarea->fecha_vencimiento = $request->fechaVencimiento;
        $tarea->save();

        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');

        $profesorTareas = new ProfesorTarea;
        $profesorTareas->idMateria = $request->idMateria;
        $profesorTareas->idTareas = $idTareas[0]->id;
        $profesorTareas->idGrupo = $request->idGrupo;
        $profesorTareas->idProfesor = $request->idUsuario;
        $profesorTareas->save();



        $nombreArchivosArray = explode(',', $request->nombreArchivos);
        if ($request->nombreArchivos) {
            foreach ($nombreArchivosArray as $nombres) {
                $archivosTarea = new archivosTarea;
                $archivosTarea->idTarea = $idTareas[0]->id;
                $archivosTarea->nombreArchivo = $nombres;
                $archivosTarea->save();
            }
        }



        return response()->json(['status' => 'Success'], 200);
    }



    public function listarTareas(Request $request)
    {
        if ($request->ou == 'Profesor') {
            return  self::consultaProfesor($request);
        } else if ($request->ou == 'Alumno') {
            return self::consultaAlumno($request);
        }
    }


    /*                                                      */
    public function consultaProfesor(Request $request)
    {
        $peticionSQL = DB::table('profesor_crea_tareas')
            ->select('tareas.id AS idTareas', 'profesor_crea_tareas.idProfesor', 'usuarios.nombre AS nombreUsuario', 'materias.id AS idMateria', 'materias.nombre AS nombreMateria', 'profesor_crea_tareas.idGrupo', 'grupos.nombreCompleto AS turnoGrupo', 'tareas.titulo', 'tareas.fecha_vencimiento')
            ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
            ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
            ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.username')
            ->where('profesor_crea_tareas.idProfesor', $request->idUsuario)
            ->orderBy('profesor_crea_tareas.idTareas', 'desc')
            ->get();

        return response()->json($peticionSQL);
    }

    public function consultaAlumno(Request $request)
    {
        $idGrupo = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
            ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
            ->get();

        $peticionSQL = DB::table('profesor_crea_tareas')
            ->select('profesor_crea_tareas.idMateria AS idMateria', 'profesor_crea_tareas.idTareas AS idTareas', 'profesor_crea_tareas.idGrupo AS idGrupo', 'profesor_crea_tareas.idProfesor AS idProfesor', 'tareas.fecha_vencimiento AS fecha_vencimiento', 'materias.nombre AS nombreMateria', 'tareas.titulo AS titulo', 'grupos.nombreCompleto AS nombreGrupo', 'usuarios.nombre AS nombreUsuario')
            ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
            ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
            ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.username')
            ->where('profesor_crea_tareas.idGrupo',  $idGrupo[0]->idGrupo)
            ->orderBy('profesor_crea_tareas.idTareas', 'desc')
            ->get();

        return response()->json($peticionSQL);
    }




    public function update(Request $request)
    {
        $modificarDatosTarea = Tarea::where('id', $request->id)->first();

        try {
            $modificarDatosTarea->titulo = $request->titulo;
            $modificarDatosTarea->descripcion = $request->descripcion;
            $modificarDatosTarea->fecha_vencimiento = $request->fecha_vencimiento;
            $modificarDatosTarea->save();
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function destroy(Request $request)
    {
        $eliminarTarea = ProfesorTarea::where('idGrupo', $request->idGrupo)->where('idTareas', $request->idTareas)->first();

        try {
            $eliminarTarea->delete();
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }
}
