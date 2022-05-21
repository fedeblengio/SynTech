<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\Tarea;
use App\Models\AlumnoEntrega;
use App\Models\AlumnoReHacerTarea;
use App\Models\archivosEntrega;
use App\Models\GruposProfesores;
use App\Models\ProfesorTarea;
use App\Models\archivosReHacerTarea;
use Illuminate\Support\Str;
use App\Models\archivosTarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Undefined;

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
        if($request->idMateria){ 
        $peticionSQL = DB::table('profesor_crea_tareas')
            ->select('tareas.id AS idTarea', 'profesor_crea_tareas.idProfesor', 'usuarios.nombre AS nombreUsuario', 'materias.id AS idMateria', 'materias.nombre AS nombreMateria', 'profesor_crea_tareas.idGrupo', 'grupos.nombreCompleto AS turnoGrupo', 'tareas.titulo','tareas.descripcion', 'tareas.fecha_vencimiento')
            ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
            ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
            ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.username')
            ->where('profesor_crea_tareas.idProfesor', $request->idUsuario)
            ->where('profesor_crea_tareas.idMateria', $request->idMateria)
            ->where('profesor_crea_tareas.idGrupo', $request->idGrupo)
            ->orderBy('profesor_crea_tareas.idTareas', 'desc')
            ->get();

            $TareasNoVencidas = array();
            $TareasVencidas = array();
            foreach ($peticionSQL as $p) {
                $fecha_actual = Carbon::now()->subMinutes(23);
                $fecha_inicio = Carbon::parse($p->fecha_vencimiento);
        
        
                    if($fecha_inicio->gt($fecha_actual)){
                      
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
    
    
    /* else{
        $peticionSQL = DB::table('profesor_crea_tareas')
        ->select('tareas.id AS idTarea', 'profesor_crea_tareas.idProfesor', 'usuarios.nombre AS nombreUsuario', 'materias.id AS idMateria', 'materias.nombre AS nombreMateria', 'profesor_crea_tareas.idGrupo', 'grupos.nombreCompleto AS turnoGrupo', 'tareas.titulo','tareas.descripcion','tareas.fecha_vencimiento')
        ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
        ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
        ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
        ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.username')
        ->where('profesor_crea_tareas.idProfesor', $request->idUsuario)
        ->orderBy('profesor_crea_tareas.idTareas', 'desc')
        ->get();

        return response()->json($peticionSQL);
    } */

    }

    public function traerTarea(Request $request){
        $peticionSQL = DB::table('tareas')
        ->select('tareas.id AS idTarea', 'profesor_crea_tareas.idProfesor', 'profesor_crea_tareas.idMateria AS idMateria', 'profesor_crea_tareas.idGrupo', 'tareas.titulo', 'tareas.fecha_vencimiento', 'tareas.titulo', 'tareas.descripcion')
        ->join('profesor_crea_tareas', 'tareas.id', '=', 'profesor_crea_tareas.idTareas')
        ->where('tareas.id', $request->idTarea)
        ->get();


        $dataResponse = array();

        foreach ($peticionSQL as $p) {

            $peticionSQLFiltrada = DB::table('archivos_tarea')
                ->select('id AS idArchivo','nombreArchivo AS archivo')
                ->where('idTarea', $p->idTarea)
                ->distinct()
                ->get();

            $arrayArchivos = array();
            $arrayImagenes = array();
            $postAuthor = $p->idProfesor;

            $usuario = DB::table('usuarios')
                ->select('imagen_perfil','username','nombre')
                ->where('username', $postAuthor)
                ->get();

    
            $img = base64_encode(Storage::disk('ftp')->get($usuario[0]->imagen_perfil));

            foreach ($peticionSQLFiltrada as $p2) {
                $resultado = Str::contains($p2->archivo, ['.pdf','.PDF','.docx']);
              /*   $resultado = strpos($p2->archivo, ".pdf"); */
         
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
                "nombreProfesor" => $usuario[0]->nombre,
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



    public function consultaAlumno(Request $request)
    {
        $idGrupo = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
            ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
            ->get();

        $variable =  $request->idUsuario;
        $variable2 = $idGrupo[0]->idGrupo;
        $variable3 = $request->idMateria;
        if ($request->idMateria){ 
            $peticionSQL = DB::select(
                DB::raw('SELECT A.idTareas , A.idMateria,  D.nombre as materia, A.idGrupo, A.idProfesor,E.nombre AS Profesor, C.fecha_vencimiento ,C.descripcion, C.titulo  FROM (SELECT * from profesor_crea_tareas WHERE idGrupo=:variable2 AND idMateria=:variable3) as A LEFT JOIN (SELECT * FROM alumno_entrega_tareas WHERE idAlumnos=:variable) as B ON A.idTareas = B.idTareas JOIN (SELECT * FROM tareas) as C ON C.id = A.idTareas JOIN (SELECT * FROM materias) as D ON D.id = A.idMateria  JOIN (SELECT * FROM usuarios) as E ON E.username = A.idProfesor WHERE B.idAlumnos IS NULL ORDER BY A.idTareas DESC;'),
                array('variable' => $variable,'variable2' => $variable2, 'variable3' => $variable3)    
            );
            $peticionSQL2 = DB::table('profesor_crea_tareas')
            ->select('profesor_crea_tareas.idMateria AS idMateria', 'profesor_crea_tareas.idTareas AS idTareas', 'profesor_crea_tareas.idGrupo AS idGrupo', 'profesor_crea_tareas.idProfesor AS idProfesor', 'tareas.fecha_vencimiento AS fecha_vencimiento', 'materias.nombre AS materia', 'tareas.titulo AS titulo', 'tareas.descripcion AS descripcion', 'grupos.nombreCompleto AS nombreGrupo', 'usuarios.nombre AS Profesor')
            ->join('alumno_entrega_tareas', 'profesor_crea_tareas.idTareas', '=', 'alumno_entrega_tareas.idTareas')
            ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
            ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
            ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.username')
            ->where('profesor_crea_tareas.idGrupo',  $idGrupo[0]->idGrupo)
            ->where('alumno_entrega_tareas.idAlumnos', $request->idUsuario)
            ->where('profesor_crea_tareas.idMateria', $request->idMateria)
            ->where('alumno_entrega_tareas.re_hacer', "1")
            ->orderBy('profesor_crea_tareas.idTareas', 'desc')
            ->get();
        }else{
            $peticionSQL = DB::select(
                DB::raw('SELECT A.idTareas , A.idMateria,  D.nombre as materia, A.idGrupo, A.idProfesor,E.nombre AS Profesor, C.fecha_vencimiento ,C.descripcion, C.titulo  FROM (SELECT * from profesor_crea_tareas WHERE idGrupo=:variable2) as A LEFT JOIN (SELECT * FROM alumno_entrega_tareas WHERE idAlumnos=:variable) as B ON A.idTareas = B.idTareas JOIN (SELECT * FROM tareas) as C ON C.id = A.idTareas JOIN (SELECT * FROM materias) as D ON D.id = A.idMateria  JOIN (SELECT * FROM usuarios) as E ON E.username = A.idProfesor WHERE B.idAlumnos IS NULL ORDER BY A.idTareas DESC;'),
                array('variable' => $variable,'variable2' => $variable2)    
            );
            $peticionSQL2 = DB::table('profesor_crea_tareas')
            ->select('profesor_crea_tareas.idMateria AS idMateria', 'profesor_crea_tareas.idTareas AS idTareas', 'profesor_crea_tareas.idGrupo AS idGrupo', 'profesor_crea_tareas.idProfesor AS idProfesor', 'tareas.fecha_vencimiento AS fecha_vencimiento', 'materias.nombre AS materia', 'tareas.titulo AS titulo', 'tareas.descripcion AS descripcion', 'grupos.nombreCompleto AS nombreGrupo', 'usuarios.nombre AS Profesor')
            ->join('alumno_entrega_tareas', 'profesor_crea_tareas.idTareas', '=', 'alumno_entrega_tareas.idTareas')
            ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
            ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
            ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.username')
            ->where('profesor_crea_tareas.idGrupo',  $idGrupo[0]->idGrupo)
            ->where('alumno_entrega_tareas.idAlumnos', $request->idUsuario)
            ->where('alumno_entrega_tareas.re_hacer', "1")
            ->orderBy('profesor_crea_tareas.idTareas', 'desc')
            ->get();
        }
        

    
        $tarea=array();
        $re_hacer_tarea=array();
        foreach ($peticionSQL as $t) {

            
            $fecha_actual = Carbon::now()->subMinutes(23);
            $fecha_vencimiento = Carbon::parse($t->fecha_vencimiento);
            $booelan = true;
    
                if($fecha_vencimiento->gt($fecha_actual)){
                    $booelan = false;
                 }else{
                    $booelan = true;
                 }

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

    public function tareasParaCorregir(Request $request){

        $peticionSQL = DB::table('tareas')
        ->select('tareas.id as idTarea','tareas.titulo','profesor_crea_tareas.idMateria','profesor_crea_tareas.idGrupo')
        ->join('profesor_crea_tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
        ->join('alumno_entrega_tareas', 'profesor_crea_tareas.idTareas', '=', 'alumno_entrega_tareas.idTareas')
        ->where('profesor_crea_tareas.idProfesor', $request->idProfesor)
        ->whereNull('alumno_entrega_tareas.calificacion')
        ->distinct()
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
        
        $eliminarTarea = Tarea::where('id', $request->idTareas)->first();
        $eliminarArhivos = archivosTarea::where('idTarea', $request->idTareas)->get();
        $eliminarArhivosReHacer = archivosReHacerTarea::where('idTareas', $request->idTareas)->get();
        $eliminarArhivosEntrega = archivosEntrega::where('idTareas', $request->idTareas)->get();
        /* try {  */
        foreach ($eliminarArhivosReHacer as $t) {
            Storage::disk('ftp')->delete($t->nombreArchivo);
            $arhivosId = archivosReHacerTarea::where('id', $t->id)->first();
            $arhivosId->delete();
        }  
        DB::delete('delete from re_hacer_tareas where idTareas="'.$request->idTareas.'";');
        foreach ($eliminarArhivosEntrega as $u) {
            Storage::disk('ftp')->delete($u->nombreArchivo);
            $arhivosId = archivosEntrega::where('id', $u->id)->first();
            $arhivosId->delete();
        }
        DB::delete('delete from alumno_entrega_tareas where idTareas="'.$request->idTareas.'";');
        foreach ($eliminarArhivos as $p) {
            Storage::disk('ftp')->delete($p->nombreArchivo);
            $arhivosId = archivosTarea::where('id', $p->id)->first();
            $arhivosId->delete();
        }
        DB::delete('delete from profesor_crea_tareas where idTareas="'.$request->idTareas.'";');
        $eliminarTarea->delete();
        

           /*  return response()->json(['status' => 'Success'], 200);
            } catch (\Throwable $th) { 
            return response()->json(['status' => 'Bad Request'], 400);
         }   */
    }

    



    
}
