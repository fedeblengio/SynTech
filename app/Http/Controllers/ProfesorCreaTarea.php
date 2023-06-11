<?php

namespace App\Http\Controllers;
use App\Jobs\ProfesorCreaTareaJob;
use App\Models\alumnoGrupo;
use App\Notifications\NuevaTareaNotificacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use App\Models\Tarea;
use App\Models\AlumnoEntrega;
use App\Models\AlumnoReHacerTarea;
use App\Models\archivosEntrega;
use App\Models\GruposProfesores;
use App\Models\ProfesorTarea;
use App\Models\archivosReHacerTarea;
use App\Http\Controllers\RegistrosController;
use Illuminate\Support\Str;
use App\Models\archivosTarea;
use App\Models\usuarios;
use App\Models\materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Mockery\Undefined;
use App\Mail\tareaMail;

class ProfesorCreaTarea extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'idGrupo' => 'required',
            'idMateria' => 'required',
            'idUsuario' => 'required',
            'titulo' => 'required | string',
            'descripcion' => 'required | string',
            'fechaVencimiento' => 'required',
          
        ]);  
        $idTarea = $this->agregarTarea($request);
     
        $this->asignarTarea($request, $idTarea);

        if ($request->archivos) {
            for ($i=0; $i < count($request->nombresArchivo); $i++){
                $this->subirArchivoTarea($request, $i, $idTarea);
            }
        }
        $this->enviarAvisoTarea($request);

        RegistrosController::store("TAREA",$request->header('token'),"CREATE",$request->idGrupo);

        return response()->json(['status' => 'Success'], 200);
    }

    public function enviarAvisoTarea($request){
        $usuario = usuarios::findOrFail($request->idUsuario);
        $materia = materia::findOrFail($request->idMateria);

        $details = [
            'nombreUsuario' => $usuario->nombre,
            'nombreMateria' => $materia->nombre,
            'grupo' => $request->idGrupo,
            'tipo' => 'tarea',
            'deeplink' => '/listado-tareas/'.$request->idGrupo.'/'.$materia->nombre.'/'.$request->idMateria
        ];

        $alumnos = $this->getAlumnos($request);

        foreach ($alumnos as $a){
            $a->notify(new NuevaTareaNotificacion($details));
            dispatch(new ProfesorCreaTareaJob($details,$a));
        }
    }



    public function listarTareas($idGrupo,$idMateria,$idUsuario)
    {
        $usuario = usuarios::findOrFail($idUsuario);
        if ($usuario->ou == 'Profesor') {
            return  self::consultaProfesor($idGrupo,$idMateria,$idUsuario);
        } else if ($usuario->ou == 'Alumno') {
            return self::consultaAlumno($idGrupo,$idMateria,$idUsuario);
        }
    }



    public function consultaProfesor($idGrupo,$idMateria,$idUsuario)
    {
        if($idMateria){
            $peticionSQL = $this->getTareasProfesor($idGrupo,$idMateria,$idUsuario);

            $TareasNoVencidas = array();
            $TareasVencidas = array();
            foreach ($peticionSQL as $p) {
                $fecha_actual = Carbon::now();
                $fecha_inicio = Carbon::parse($p->fecha_vencimiento)->addHours(24);

                if($fecha_inicio>=$fecha_actual ){


                        $datos = [
                            "idTarea" => $p->idTarea,
                            "idProfesor" => $p->idProfesor,
                            "nombre" => $p->nombreUsuario,
                            "idMateria" => $p->idMateria,
                            "nombreMateria" => $p->nombreMateria,
                            "idGrupo" => $p->idGrupo,
                            "turnoGrupo" => $p->turnoGrupo,
                            "titulo" => $p->titulo,
                            "descripcion" => $p->descripcion,
                            "fecha_vencimiento" => $p->fecha_vencimiento,
                        ];

                        array_push($TareasNoVencidas, $datos);
                        }else{

                        $datos1 = [
                            "idTarea" => $p->idTarea,
                            "idProfesor" => $p->idProfesor,
                            "nombre" => $p->nombreUsuario,
                            "idMateria" => $p->idMateria,
                            "nombreMateria" => $p->nombreMateria,
                            "idGrupo" => $p->idGrupo,
                            "turnoGrupo" => $p->turnoGrupo,
                            "titulo" => $p->titulo,
                            "descripcion" => $p->descripcion,
                            "fecha_vencimiento" => $p->fecha_vencimiento,
                        ];
                        array_push($TareasVencidas, $datos1);
                        }

                        }

                        $tareas=[
                            'noVencidas'=>$TareasNoVencidas,
                            'vencidas'=>$TareasVencidas,
                        ];

                       return response()->json($tareas);

    }

    }

    public function traerTarea($id){
        $peticionSQL = $this->getDatosTarea($id);

        if(count($peticionSQL) == 0){
            return response()->json(['status' => 'Tarea no encontrada'], 404);
        }
        $dataResponse = array();

        foreach ($peticionSQL as $p) {

            $peticionSQLFiltrada = $this->getArchivosTarea($p);

            $arrayArchivos = array();
            $arrayImagenes = array();
            $postAuthor = $p->idProfesor;

            $usuario = $this->getImagenPerfil($postAuthor);
            if (!App::environment(['testing'])) {
            $img = base64_encode(Storage::disk('ftp')->get($usuario->imagen_perfil));
            }else{
                $img = $usuario->imagen_perfil;
            }
            foreach ($peticionSQLFiltrada as $p2) {
                $resultado = Str::contains($p2->archivo, ['.pdf','.PDF','.docx']);
            

                if ($resultado != '') {
                    array_push($arrayArchivos, $p2);
                } else {

                    array_push($arrayImagenes, $p2);
                }
            }


            $datos = [
                "idTarea" => $p->idTarea,
                "profile_picture" => $img,
                "idProfesor" => $p->idProfesor,
                "nombreProfesor" => $usuario->nombre,
                "idMateria" => $p->idMateria,
                "fechaVencimiento" => $p->fecha_vencimiento,
                "titulo" => $p->titulo,
                "descripcion" => $p->descripcion,
            ];

            $p = [
                "datos" => $datos,
                "archivos" => $arrayArchivos,
                "imagenes" => $arrayImagenes,
            ];

            array_push($dataResponse, $p);
        }
        return response()->json($dataResponse[0]);



    }



    public function consultaAlumno($idGrupo,$idMateria,$idUsuario)
    {
        $peticionSQL = $this->getTareasMateriaForAlumno( $idUsuario, $idGrupo, $idMateria);
        $peticionSQL2 = $this->getReHacerTareasMateriaForAlumno($idUsuario, $idGrupo, $idMateria);
    

        $tarea=array();
        $re_hacer_tarea=array();
        foreach ($peticionSQL as $t) {


            $fecha_actual = Carbon::now();
            $fecha_vencimiento = Carbon::parse($t->fecha_vencimiento)->addHours(24);
            $booelan = true;

                if($fecha_vencimiento===$fecha_actual || $fecha_vencimiento>$fecha_actual){
                    $booelan = false;

                 $datos = [
                    'idTarea'=> $t->idTareas,
                    'idMateria'=> $t->idMateria,
                    'Materia'=> $t->materia,
                    'idGrupo'=> $t->idGrupo,
                    'idProfesor'=> $t->idProfesor,
                    'Profesor'=> $t->Profesor,
                    'fecha_vencimiento'=> $t->fecha_vencimiento,
                    'titulo'=> $t->titulo,
                    'descripcion'=> $t->descripcion,
                    'vencido' => $booelan,
                ];

                array_push($tarea,$datos);
             }

            }

            foreach ($peticionSQL2 as $p) {
                $reHacer = [
                    'idTarea'=> $p->idTareas,
                    'idMateria'=> $p->idMateria,
                    'Materia'=> $p->materia,
                    'idGrupo'=> $p->idGrupo,
                    'idProfesor'=> $p->idProfesor,
                    'Profesor'=> $p->Profesor,
                    'titulo'=> $p->titulo,
                    'descripcion'=> $p->descripcion,
                ];

                array_push($re_hacer_tarea,$reHacer);
            }
         $tareas=[
             'tareas'=>$tarea,
             're_hacer'=>$re_hacer_tarea,
         ];

        return response()->json($tareas);
    }

 

    

    public function destroy($id, Request $request)
    {

        $eliminarTarea = Tarea::findOrFail($id);
       
        
        $eliminarArchivos = archivosTarea::where('idTarea', $eliminarTarea->id)->get();
        $eliminarArhivosReHacer = archivosReHacerTarea::where('idTareas', $eliminarTarea->id)->get();
        $eliminarArhivosEntrega = archivosEntrega::where('idTareas', $eliminarTarea->id)->get();
   

        if(!empty($eliminarArhivosReHacer)){
            self::deleteReHacerTareas($eliminarArhivosReHacer, $request);
        }
        if(!empty($eliminarArhivosEntrega)){
            self::deleteEntregasTareas($eliminarArhivosEntrega, $request);
        }
        if(!empty($eliminarArchivos)){
            self::deleteTareaProfesor($eliminarArchivos, $request);
        }
    
        ProfesorTarea::where('idTareas', $eliminarTarea->id)->delete();
        $eliminarTarea->delete();
            RegistrosController::store("TAREA",$request->header('token'),"DELETE",$eliminarTarea->titulo);
            return response()->json(['status' => 'Success'], 200);
        
    }

   
    public function agregarTarea(Request $request)
    {
        $tarea = new Tarea;
        $tarea->titulo = $request->titulo;
        $tarea->descripcion = $request->descripcion;
        $tarea->fecha_vencimiento = $request->fechaVencimiento;
        $tarea->save();
     
        return $tarea->id;
    }

   
    public function asignarTarea(Request $request, $idTareas)
    {
        $profesorTareas = new ProfesorTarea;
        $profesorTareas->idMateria = $request->idMateria;
        $profesorTareas->idTareas = $idTareas;
        $profesorTareas->idGrupo = $request->idGrupo;
        $profesorTareas->idProfesor = $request->idUsuario;
        $profesorTareas->save();
    }

  
    public function subirArchivoTarea(Request $request, int $i, $idTareas)
    {
        $nombreArchivo = random_int(0, 1000000) . "_" . $request->nombresArchivo[$i];
        if (!App::environment(['testing'])) {
        Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivos[$i], 'r+'));
        }
        $archivosTarea = new archivosTarea;
        $archivosTarea->idTarea = $idTareas;
        $archivosTarea->nombreArchivo = $nombreArchivo;
        $archivosTarea->save();
    }

  
    public function getAlumnos(Request $request)
    {
        $alumnos = alumnoGrupo::where('idGrupo', $request->idGrupo)->pluck('idAlumnos');

        $usuarios = usuarios::whereIn('id', $alumnos)->get();
         
        return $usuarios;
    }

    
    public function getTareasProfesor($idGrupo,$idMateria,$idUsuario)
    {
        $peticionSQL = DB::table('profesor_crea_tareas')
            ->select('tareas.id AS idTarea', 'profesor_crea_tareas.idProfesor', 'usuarios.nombre AS nombreUsuario', 'materias.id AS idMateria', 'materias.nombre AS nombreMateria', 'profesor_crea_tareas.idGrupo', 'grupos.nombreCompleto AS turnoGrupo', 'tareas.titulo', 'tareas.descripcion', 'tareas.fecha_vencimiento')
            ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
            ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
            ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.id')
            ->where('profesor_crea_tareas.idProfesor', $idUsuario)
            ->where('profesor_crea_tareas.idMateria', $idMateria)
            ->where('profesor_crea_tareas.idGrupo', $idGrupo)
            ->orderBy('profesor_crea_tareas.idTareas', 'desc')
            ->get();
        return $peticionSQL;
    }

   
    public function getDatosTarea($id)
    {
            $peticionSQL = DB::table('tareas')
            ->select('tareas.id AS idTarea', 'profesor_crea_tareas.idProfesor', 'profesor_crea_tareas.idMateria AS idMateria', 'profesor_crea_tareas.idGrupo', 'tareas.titulo', 'tareas.fecha_vencimiento', 'tareas.titulo', 'tareas.descripcion')
            ->join('profesor_crea_tareas', 'tareas.id', '=', 'profesor_crea_tareas.idTareas')
            ->where('tareas.id', $id)
            ->get();
            return $peticionSQL; 
    }

  
    public function getArchivosTarea($p)
    {
        $peticionSQLFiltrada = DB::table('archivos_tarea')
            ->select('id AS idArchivo', 'nombreArchivo AS archivo')
            ->where('idTarea', $p->idTarea)
            ->distinct()
            ->get();
        return $peticionSQLFiltrada;
    }

 
    public function getImagenPerfil($postAuthor)
    {
        $usuario = DB::table('usuarios')
            ->select('imagen_perfil', 'id', 'nombre')
            ->where('id', $postAuthor)
            ->first();
        return $usuario;
    }

  
    public function getIdGrupoAlumno(Request $request)
    {
        $idGrupo = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
            ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
            ->get();
        return $idGrupo;
    }

  
    public function getTareasMateriaForAlumno($idUsuario, $idGrupo ,$idMateria)
    {
    
        $peticionSQL = DB::table('profesor_crea_tareas as A')
        ->select('A.idTareas', 'A.idMateria', 'D.nombre as materia', 'A.idGrupo', 'A.idProfesor', 'E.nombre AS Profesor', 'C.fecha_vencimiento', 'C.descripcion', 'C.titulo')
        ->leftJoin('alumno_entrega_tareas as B', function ($join) use ($idUsuario) {
            $join->on('A.idTareas', '=', 'B.idTareas')
                 ->where('B.idAlumnos', '=', $idUsuario);
        })
        ->join('tareas as C', 'C.id', '=', 'A.idTareas')
        ->join('materias as D', 'D.id', '=', 'A.idMateria')
        ->join('usuarios as E', 'E.id', '=', 'A.idProfesor')
        ->where('A.idGrupo', '=', $idGrupo)
        ->where('A.idMateria', '=', $idMateria)
        ->whereNull('B.idAlumnos')
        ->orderBy('A.idTareas', 'DESC')
        ->get();
    
    
        return $peticionSQL;
    }

    public function getReHacerTareasMateriaForAlumno($idUsuario,$idGrupo,$idMateria)
    {
        $peticionSQL2 = DB::table('profesor_crea_tareas')
            ->select('profesor_crea_tareas.idMateria AS idMateria', 'profesor_crea_tareas.idTareas AS idTareas', 'profesor_crea_tareas.idGrupo AS idGrupo', 'profesor_crea_tareas.idProfesor AS idProfesor', 'tareas.fecha_vencimiento AS fecha_vencimiento', 'materias.nombre AS materia', 'tareas.titulo AS titulo', 'tareas.descripcion AS descripcion', 'grupos.nombreCompleto AS nombreGrupo', 'usuarios.nombre AS Profesor')
            ->join('alumno_entrega_tareas', 'profesor_crea_tareas.idTareas', '=', 'alumno_entrega_tareas.idTareas')
            ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
            ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
            ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.id')
            ->where('profesor_crea_tareas.idGrupo', $idGrupo)
            ->where('alumno_entrega_tareas.idAlumnos', $idUsuario)
            ->where('profesor_crea_tareas.idMateria', $idMateria)
            ->where('alumno_entrega_tareas.re_hacer', "1")
            ->orderBy('profesor_crea_tareas.idTareas', 'desc')
            ->get();
        return $peticionSQL2;
    }

  
 
  
   

   
    public function deleteReHacerTareas($eliminarArchivosReHacer, Request $request)
    {
        foreach ($eliminarArchivosReHacer as $t) {
            if (!App::environment(['testing'])) {
            Storage::disk('ftp')->delete($t->nombreArchivo);
            }
            $archivosId = archivosReHacerTarea::where('id', $t->id)->first();
            $archivosId->delete();
            $t->delete();
        }
       
    }

  
    public function deleteEntregasTareas($eliminarArchivosEntrega, Request $request)
    {
        foreach ($eliminarArchivosEntrega as $u) {
            if (!App::environment(['testing'])) {
            Storage::disk('ftp')->delete($u->nombreArchivo);
            }
            $archivosId = archivosEntrega::where('id', $u->id)->first();
            $archivosId->delete();
            $u->delete();
        }
    }

 
    public function deleteTareaProfesor($eliminarArchivos, Request $request)
    {
        foreach ($eliminarArchivos as $p) {
            if (!App::environment(['testing'])) {
            Storage::disk('ftp')->delete($p->nombreArchivo);
            }
            $archivosId = archivosTarea::where('id', $p->id)->first();
            $archivosId->delete();
            $p->delete();
        }
        
    }


}
