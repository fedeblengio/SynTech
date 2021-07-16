<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use App\Models\Tarea;
use App\Models\GruposProfesores;
use App\Models\ProfesorTarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfesorCreaTarea extends Controller
{
    public function index()
    {
        return response()->json(Tarea::all());
    }

    
    public function show(Request $request)
    {
        $mostrarTareasGrupos=ProfesorTarea::all()->where('idGrupo', $request->idGrupo)->where('idMateria');
        return response()->json($mostrarTareasGrupos);
    }

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
    }

    public function store(Request $request)
    {
       try { 
            $nombre="";
                if($request->hasFile("archivo")){
                    $file=$request->archivo;
                   
                    if($file->guessExtension()=="pdf"){
                        $nombre = time()."_".$file->getClientOriginalName();                       
                        Storage::disk('ftp')->put($nombre, fopen($request->archivo, 'r+'));                  
                       
                    }

                }
                self::subirTarea($request, $nombre);
                return response()->json(['status' => 'Success'], 200);         
             }catch (\Throwable $th) {
                    return response()->json(['status' => 'Error'], 406);
            } 
    }

    public function subirTarea($request, $nombre)
    {
                $tarea = new Tarea;
                $tarea->titulo = $request->titulo;
                $tarea->descripcion = $request->descripcion;
                $tarea->fecha_vencimiento = $request->fecha_vencimiento;
                $tarea->archivo = $nombre;
                $tarea->save();

                $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');

                $profesorTareas = new ProfesorTarea;
                $profesorTareas->idMateria = $request->idMateria;
                $profesorTareas->idTareas = $idTareas[0]->id;
                $profesorTareas->idGrupo = $request->idGrupo;
                $profesorTareas->idProfesor = $request->idProfesor;
                $profesorTareas->save();

                return response()->json(['status' => 'Success'], 200);
    }

    public function traerArchivo(Request $request)
    {
        return Storage::disk('ftp')->get($request->archivo);
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