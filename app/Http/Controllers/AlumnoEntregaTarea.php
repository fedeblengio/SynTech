<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Tarea;
use App\Models\GruposProfesores;
use App\Models\ProfesorTarea;
use App\Models\AlumnoEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoEntregaTarea extends Controller
{

    public function index()
    {
        return response()->json(AlumnoEntrega::all());
    }

    public function traerTareasMateria(Request $request)
    {
        $tarea_materia = DB::table('profesor_crea_tareas')
            ->select('profesor_crea_tareas.idGrupo AS Grupo', 'profesor_crea_tareas.idTareas AS Tareas', 'profesor_crea_tareas.IdMateria AS idMateria', 'tareas.descripcion AS tareaDescripcion', 'tareas.fecha_vencimiento AS tareaVencimiento', 'tareas.archivo AS tareaArchivo')
            ->join('tareas', 'tareas.id', '=', 'profesor_crea_tareas.idTareas')
            ->where('profesor_crea_tareas.idGrupo', $request->idGrupo)
            ->where('profesor_crea_tareas.idMateria', $request->idMateria)
            ->get();

        return response()->json($tarea_materia);
    }

    public function store(Request $request)
    {
        try {
            $nombre = "";
            if ($request->hasFile("archivo")) {
                $file = $request->archivo;

                if ($file->guessExtension() == "pdf") {
                    $nombre = time() . "_" . $file->getClientOriginalName();
                    Storage::disk('ftp')->put($nombre, fopen($request->archivo, 'r+'));
                }
            }
            self::subirTarea($request, $nombre);
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Error'], 406);
        }
    }

    public function subirTarea(Request $request, $nombre)
    {

        $alumnoTarea = new AlumnoEntrega;
        $alumnoTarea->idTareas = $request->idTareas;
        $alumnoTarea->idAlumnos = $request->idAlumnos;
        $alumnoTarea->archivo = $nombre;
        $alumnoTarea->save();
    }

    public function traerArchivo(Request $request)
    {
        return Storage::disk('ftp')->get($request->archivo);
    }

    public function update(Request $request)
    {

        $existe = AlumnoEntrega::where('idTareas', $request->idTareas)->where('idAlumnos', $request->idAlumnos)->first();
        try {
            if ($existe) {

                DB::update('UPDATE alumno_entrega_tareas SET calificacion="' . $request->calificacion . '" WHERE idTareas="' . $request->idTareas . '" AND idAlumnos="' . $request->idAlumnos . '";');
                return response()->json(['status' => 'Success'], 200);
            }
            return response()->json(['status' => 'Bad Request'], 400);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }
}
