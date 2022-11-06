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
        $this->agregarTarea($request);

        $idTareas = DB::table('tareas')->orderBy('created_at', 'desc')->limit(1)->get('id');

        $this->asignarTarea($request, $idTareas[0]);

        $nombreUsuario = usuarios::where('id', $request->idUsuario)->first();
        $nombreMateria = materia::where('id', $request->idMateria)->first();

        if ($request->archivos) {
            for ($i=0; $i < count($request->nombresArchivo); $i++){
                $this->subirArchivoTarea($request, $i, $idTareas[0]);
            }
        }

        $details = [
            'nombreUsuario' => $nombreUsuario->nombre,
            'nombreMateria' => $nombreMateria->nombre,
            'grupo' => $request->idGrupo
        ];

        $alumnos = $this->getAlumnosEmail($request);

        foreach ($alumnos as $a){
            Mail::to($a->email)->send(new tareaMail($details));
        }


        RegistrosController::store("TAREA",$request->header('token'),"CREATE",$request->idGrupo);

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
            $peticionSQL = $this->getTareasProfesor($request);

            $TareasNoVencidas = array();
            $TareasVencidas = array();
            foreach ($peticionSQL as $p) {
                $fecha_actual = Carbon::now()->subMinutes(23);
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

    public function traerTarea(Request $request){
        $peticionSQL = $this->getDatosTarea($request);


        $dataResponse = array();

        foreach ($peticionSQL as $p) {

            $peticionSQLFiltrada = $this->getArchivosTarea($p);

            $arrayArchivos = array();
            $arrayImagenes = array();
            $postAuthor = $p->idProfesor;

            $usuario = $this->getImagenPerfil($postAuthor);


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
        $variable =  $request->idUsuario;
        $variable2 = $request->idGrupo;
        $variable3 = $request->idMateria;
        if ($request->idMateria){
            $peticionSQL = $this->getTareasMateriaForAlumno($variable, $variable2, $variable3);
            $peticionSQL2 = $this->getReHacerTareasMateriaForAlumno($request);
        }else{
            $peticionSQL = $this->getTareasForAlumno($variable, $variable2);
            $peticionSQL2 = $this->getReHacerTareasForAlumno($request);
        }



        $TareasNoVencidas = array();
        $TareasVencidas = array();
        $tarea=array();
        $re_hacer_tarea=array();
        foreach ($peticionSQL as $t) {


            $fecha_actual = Carbon::now()->subMinutes(23);
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

    public function tareasParaCorregir(Request $request){

        $peticionSQL = $this->getTareasSinCalificacion($request);

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
            RegistrosController::store("TAREA",$request->header('token'),"UPDATE",$request->titulo);
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function destroy(Request $request)
    {

        $eliminarTarea = Tarea::where('id', $request->idTareas)->first();
        $eliminarArchivos = archivosTarea::where('idTarea', $request->idTareas)->get();
        $eliminarArhivosReHacer = archivosReHacerTarea::where('idTareas', $request->idTareas)->get();
        $eliminarArhivosEntrega = archivosEntrega::where('idTareas', $request->idTareas)->get();
          /* try { */
             self::deleteReHacerTareas($eliminarArhivosReHacer, $request);
             self::deleteEntregasTareas($eliminarArhivosEntrega, $request);
             self::deleteTareaProfesor($eliminarArchivos, $request, $eliminarTarea);

             RegistrosController::store("TAREA",$request->header('token'),"DELETE",$eliminarTarea->titulo);


             return response()->json(['status' => 'Success'], 200);
           /*   } catch (\Throwable $th) {  */
            return response()->json(['status' => 'Bad Request'], 400);
          /* }  */
    }

    /**
     * @param Request $request
     * @return void
     */
    public function agregarTarea(Request $request): void
    {
        $tarea = new Tarea;
        $tarea->titulo = $request->titulo;
        $tarea->descripcion = $request->descripcion;
        $tarea->fecha_vencimiento = $request->fechaVencimiento;
        $tarea->save();
    }

    /**
     * @param Request $request
     * @param $idTareas
     * @return void
     */
    public function asignarTarea(Request $request, $idTareas): void
    {
        $profesorTareas = new ProfesorTarea;
        $profesorTareas->idMateria = $request->idMateria;
        $profesorTareas->idTareas = $idTareas->id;
        $profesorTareas->idGrupo = $request->idGrupo;
        $profesorTareas->idProfesor = $request->idUsuario;
        $profesorTareas->save();
    }

    /**
     * @param Request $request
     * @param int $i
     * @param $idTareas
     * @return void
     * @throws \Exception
     */
    public function subirArchivoTarea(Request $request, int $i, $idTareas): void
    {
        $nombreArchivo = random_int(0, 1000000) . "_" . $request->nombresArchivo[$i];
        Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivos[$i], 'r+'));
        $archivosTarea = new archivosTarea;
        $archivosTarea->idTarea = $idTareas->id;
        $archivosTarea->nombreArchivo = $nombreArchivo;
        $archivosTarea->save();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getAlumnosEmail(Request $request): \Illuminate\Support\Collection
    {
        $alumnos = DB::table('alumnos_pertenecen_grupos')
            ->select('usuarios.email')
            ->join('usuarios', 'alumnos_pertenecen_grupos.idAlumnos', '=', 'usuarios.id')
            ->where('alumnos_pertenecen_grupos.idGrupo', $request->idGrupo)
            ->get();
        return $alumnos;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getTareasProfesor(Request $request): \Illuminate\Support\Collection
    {
        $peticionSQL = DB::table('profesor_crea_tareas')
            ->select('tareas.id AS idTarea', 'profesor_crea_tareas.idProfesor', 'usuarios.nombre AS nombreUsuario', 'materias.id AS idMateria', 'materias.nombre AS nombreMateria', 'profesor_crea_tareas.idGrupo', 'grupos.nombreCompleto AS turnoGrupo', 'tareas.titulo', 'tareas.descripcion', 'tareas.fecha_vencimiento')
            ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
            ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
            ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.id')
            ->where('profesor_crea_tareas.idProfesor', $request->idUsuario)
            ->where('profesor_crea_tareas.idMateria', $request->idMateria)
            ->where('profesor_crea_tareas.idGrupo', $request->idGrupo)
            ->orderBy('profesor_crea_tareas.idTareas', 'desc')
            ->get();
        return $peticionSQL;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getDatosTarea(Request $request): \Illuminate\Support\Collection
    {
        $peticionSQL = DB::table('tareas')
            ->select('tareas.id AS idTarea', 'profesor_crea_tareas.idProfesor', 'profesor_crea_tareas.idMateria AS idMateria', 'profesor_crea_tareas.idGrupo', 'tareas.titulo', 'tareas.fecha_vencimiento', 'tareas.titulo', 'tareas.descripcion')
            ->join('profesor_crea_tareas', 'tareas.id', '=', 'profesor_crea_tareas.idTareas')
            ->where('tareas.id', $request->idTarea)
            ->get();
        return $peticionSQL;
    }

    /**
     * @param $p
     * @return \Illuminate\Support\Collection
     */
    public function getArchivosTarea($p): \Illuminate\Support\Collection
    {
        $peticionSQLFiltrada = DB::table('archivos_tarea')
            ->select('id AS idArchivo', 'nombreArchivo AS archivo')
            ->where('idTarea', $p->idTarea)
            ->distinct()
            ->get();
        return $peticionSQLFiltrada;
    }

    /**
     * @param $postAuthor
     * @return \Illuminate\Support\Collection
     */
    public function getImagenPerfil($postAuthor): \Illuminate\Support\Collection
    {
        $usuario = DB::table('usuarios')
            ->select('imagen_perfil', 'id', 'nombre')
            ->where('id', $postAuthor)
            ->get();
        return $usuario;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getIdGrupoAlumno(Request $request): \Illuminate\Support\Collection
    {
        $idGrupo = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
            ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
            ->get();
        return $idGrupo;
    }

    /**
     * @param $variable
     * @param $variable2
     * @param $variable3
     * @return array
     */
    public function getTareasMateriaForAlumno($variable, $variable2, $variable3): array
    {
        $peticionSQL = DB::select(
            DB::raw('SELECT A.idTareas , A.idMateria,  D.nombre as materia, A.idGrupo, A.idProfesor,E.nombre AS Profesor, C.fecha_vencimiento ,C.descripcion, C.titulo  FROM (SELECT * from profesor_crea_tareas WHERE idGrupo=:variable2 AND idMateria=:variable3) as A LEFT JOIN (SELECT * FROM alumno_entrega_tareas WHERE idAlumnos=:variable) as B ON A.idTareas = B.idTareas JOIN (SELECT * FROM tareas) as C ON C.id = A.idTareas JOIN (SELECT * FROM materias) as D ON D.id = A.idMateria  JOIN (SELECT * FROM usuarios) as E ON E.id = A.idProfesor WHERE B.idAlumnos IS NULL ORDER BY A.idTareas DESC;'),
            array('variable' => $variable, 'variable2' => $variable2, 'variable3' => $variable3)
        );
        return $peticionSQL;
    }

    /**
     * @param $idGrupo
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getReHacerTareasMateriaForAlumno(Request $request): \Illuminate\Support\Collection
    {
        $peticionSQL2 = DB::table('profesor_crea_tareas')
            ->select('profesor_crea_tareas.idMateria AS idMateria', 'profesor_crea_tareas.idTareas AS idTareas', 'profesor_crea_tareas.idGrupo AS idGrupo', 'profesor_crea_tareas.idProfesor AS idProfesor', 'tareas.fecha_vencimiento AS fecha_vencimiento', 'materias.nombre AS materia', 'tareas.titulo AS titulo', 'tareas.descripcion AS descripcion', 'grupos.nombreCompleto AS nombreGrupo', 'usuarios.nombre AS Profesor')
            ->join('alumno_entrega_tareas', 'profesor_crea_tareas.idTareas', '=', 'alumno_entrega_tareas.idTareas')
            ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
            ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
            ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.id')
            ->where('profesor_crea_tareas.idGrupo', $request->idGrupo)
            ->where('alumno_entrega_tareas.idAlumnos', $request->idUsuario)
            ->where('profesor_crea_tareas.idMateria', $request->idMateria)
            ->where('alumno_entrega_tareas.re_hacer', "1")
            ->orderBy('profesor_crea_tareas.idTareas', 'desc')
            ->get();
        return $peticionSQL2;
    }

    /**
     * @param $variable
     * @param $variable2
     * @return array
     */
    public function getTareasForAlumno($variable, $variable2): array
    {
        $peticionSQL = DB::select(
            DB::raw('SELECT A.idTareas , A.idMateria,  D.nombre as materia, A.idGrupo, A.idProfesor,E.nombre AS Profesor, C.fecha_vencimiento ,C.descripcion, C.titulo  FROM (SELECT * from profesor_crea_tareas WHERE idGrupo=:variable2) as A LEFT JOIN (SELECT * FROM alumno_entrega_tareas WHERE idAlumnos=:variable) as B ON A.idTareas = B.idTareas JOIN (SELECT * FROM tareas) as C ON C.id = A.idTareas JOIN (SELECT * FROM materias) as D ON D.id = A.idMateria  JOIN (SELECT * FROM usuarios) as E ON E.id = A.idProfesor WHERE B.idAlumnos IS NULL ORDER BY A.idTareas DESC;'),
            array('variable' => $variable, 'variable2' => $variable2)
        );
        return $peticionSQL;
    }

    /**
     * @param $idGrupo
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getReHacerTareasForAlumno(Request $request): \Illuminate\Support\Collection
    {
        $peticionSQL2 = DB::table('profesor_crea_tareas')
            ->select('profesor_crea_tareas.idMateria AS idMateria', 'profesor_crea_tareas.idTareas AS idTareas', 'profesor_crea_tareas.idGrupo AS idGrupo', 'profesor_crea_tareas.idProfesor AS idProfesor', 'tareas.fecha_vencimiento AS fecha_vencimiento', 'materias.nombre AS materia', 'tareas.titulo AS titulo', 'tareas.descripcion AS descripcion', 'grupos.nombreCompleto AS nombreGrupo', 'usuarios.nombre AS Profesor')
            ->join('alumno_entrega_tareas', 'profesor_crea_tareas.idTareas', '=', 'alumno_entrega_tareas.idTareas')
            ->join('tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('grupos', 'profesor_crea_tareas.idGrupo', '=', 'grupos.idGrupo')
            ->join('materias', 'profesor_crea_tareas.idMateria', '=', 'materias.id')
            ->join('usuarios', 'profesor_crea_tareas.idProfesor', '=', 'usuarios.id')
            ->where('profesor_crea_tareas.idGrupo', $request->idGrupo)
            ->where('alumno_entrega_tareas.idAlumnos', $request->idUsuario)
            ->where('alumno_entrega_tareas.re_hacer', "1")
            ->orderBy('profesor_crea_tareas.idTareas', 'desc')
            ->get();
        return $peticionSQL2;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getTareasSinCalificacion(Request $request): \Illuminate\Support\Collection
    {
        $peticionSQL = DB::table('tareas')
            ->select('tareas.id as idTarea', 'tareas.titulo', 'profesor_crea_tareas.idMateria', 'profesor_crea_tareas.idGrupo')
            ->join('profesor_crea_tareas', 'profesor_crea_tareas.idTareas', '=', 'tareas.id')
            ->join('alumno_entrega_tareas', 'profesor_crea_tareas.idTareas', '=', 'alumno_entrega_tareas.idTareas')
            ->where('profesor_crea_tareas.idProfesor', $request->idProfesor)
            ->whereNull('alumno_entrega_tareas.calificacion')
            ->distinct()
            ->get();
        return $peticionSQL;
    }

    /**
     * @param $eliminarArhivosReHacer
     * @param Request $request
     * @return mixed
     */
    public function deleteReHacerTareas($eliminarArchivosReHacer, Request $request)
    {
        foreach ($eliminarArchivosReHacer as $t) {
            Storage::disk('ftp')->delete($t->nombreArchivo);
            $archivosId = archivosReHacerTarea::where('id', $t->id)->first();
            $archivosId->delete();
        }
        DB::delete('delete from re_hacer_tareas where idTareas="' . $request->idTareas . '";');
        
    }

    /**
     * @param $eliminarArhivosEntrega
     * @param Request $request
     * @return mixed
     */
    public function deleteEntregasTareas($eliminarArchivosEntrega, Request $request)
    {
        foreach ($eliminarArchivosEntrega as $u) {
            Storage::disk('ftp')->delete($u->nombreArchivo);
            $archivosId = archivosEntrega::where('id', $u->id)->first();
            $archivosId->delete();
        }
        DB::delete('delete from alumno_entrega_tareas where idTareas="' . $request->idTareas . '";');
      
    }

    /**
     * @param $eliminarArhivos
     * @param Request $request
     * @param $eliminarTarea
     * @return void
     */
    public function deleteTareaProfesor($eliminarArchivos, Request $request, $eliminarTarea): void
    {
        foreach ($eliminarArchivos as $p) {
            Storage::disk('ftp')->delete($p->nombreArchivo);
            $archivosId = archivosTarea::where('id', $p->id)->first();
            $archivosId->delete();
        }
        DB::delete('delete from profesor_crea_tareas where idTareas="' . $request->idTareas . '";');
        $eliminarTarea->delete();
    }


}
