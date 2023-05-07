<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Tarea;
use App\Models\GruposProfesores;
use App\Models\ProfesorTarea;
use App\Models\AlumnoEntrega;
use App\Models\archivosEntrega;
use App\Models\archivosReHacerTarea;
use App\Models\AlumnoReHacerTarea;
use App\Models\alumnoGrupo;
use App\Http\Controllers\RegistrosController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class AlumnoEntregaTarea extends Controller
{

    public function index()
    {
        return response()->json(AlumnoEntrega::all());
    }



   
    public function visualizarEntrega($idTarea,$idAlumno)
    {
        $primera_entrega = $this->getPrimeraEntregaAlumno($idTarea,$idAlumno);

        $segunda_entrega = $this->getSegundaEntregaAlumno($idTarea,$idAlumno);


        $imagen_perfil_alumno = $this->getImagenPerfilAlumno($idAlumno);


        $archivosAlumno1 = array();
        $archivosAlumno2 = array();


        $traerArchivosTareaAlumno = $this->getArchivosTareaAlumno($idTarea,$idAlumno);
        $traerArchivosTareaAlumno2 = $this->getArchivosTareaAlumno2($idTarea,$idAlumno);

        foreach ($traerArchivosTareaAlumno as $archivosA1) {
            array_push($archivosAlumno1, $archivosA1->archivoAlumno);
        }
        foreach ($traerArchivosTareaAlumno2 as $archivosA2) {
            array_push($archivosAlumno2, $archivosA2->archivoAlumno);
        }


        $primeraE = [
            "entrega" => $primera_entrega,
            "archivosAlumno" => $archivosAlumno1,
        ];

        $segundaE = [
            "entrega" => $segunda_entrega,
            "archivosAlumno" => $archivosAlumno2,
        ];

        $aux = [
            "imagen_perfil_alumno" => base64_encode(Storage::disk('ftp')->get($imagen_perfil_alumno)),
            "primera_entrega" => $primeraE,
            "segunda_entrega" => $segundaE
        ];

        return response()->json($aux);
    }
    public function entregarTarea($idTarea,$idAlumno,Request $request)
    {
        $request->validate([
            'mensaje' => 'required | string',
            're_hacer' => 'required | boolean',
            'archivos'=> 'array',
            'nombresArchivo' => 'array'
        ]);

        return $request->re_hacer == 1 ? self::reHacerTarea($request,$idTarea,$idAlumno) : self::subirTarea($request,$idTarea,$idAlumno);
    }


    public function TareaNotaAlumnoMateria($idGrupo,$idMateria,$idUsuario)
    {

        $primera_entrega = self::getCalificacionPrimeraEntrega($idGrupo,$idMateria,$idUsuario);

        $tareasNotas = array();
        $tareasNotasReHacer = array();

        foreach ($primera_entrega as $p) {
            $segunda_entrega = self::getCalificacionSegundaEntrega($p, $idGrupo,$idMateria,$idUsuario);


            $calificacion= [
                "calificacion" => 0,
            ];
            if($segunda_entrega == null){
                $datos = [
                    "idTareas" => $p->idTareas,
                    "titulo" => $p->titulo,
                    "idAlumnos"=>$p->idAlumnos,
                    'nombreAlumno' => $p->nombreAlumno,
                    "calificacion" => $p->calificacion,
                    'descripcion' => $p->descripcion,
                    "nota_reHacer"=>$calificacion,
                ];
            }else{
                $datos = [
                    "idTareas" => $p->idTareas,
                    "titulo" => $p->titulo,
                    'descripcion' => $p->descripcion,
                    "idAlumnos"=>$p->idAlumnos,
                    'nombreAlumno' => $p->nombreAlumno,
                    "calificacion" => $p->calificacion,
                    "nota_reHacer"=>$segunda_entrega,
                ];
            }





            array_push($tareasNotas, $datos);
        }





        return response()->json($tareasNotas);
    }


    public function promedioMateria(Request $request){

        $fecha_actual = Carbon::now()->subMinutes(23);
        $cantClasesListadas = self::getCantidadClasesListadas($request, $fecha_actual);


        $tareasTotales = self::getTareasTotalesMateriaGrupo($request);

        $totalTarea= $tareasTotales->totalTareas;

        $alumnos = alumnoGrupo::where('idGrupo', $request->idGrupo)->get();

        $alumnos = self::getAlumnosGrupo($request);


        $dataResponse = array();


        $sumaNotaPrimera = 0;
        $sumaNotaSegunda = 0;
            foreach ($alumnos as $a){
                $cantFaltas = self::cantidadFaltasPorAlumno($request, $a);

                $primera_entrega = self::datosPrimeraEntrega($a, $request);
                $segunda_entrega = self::datosSegundaEntrega($a, $request);
                foreach ($segunda_entrega as $s){
                    foreach ($primera_entrega as $p) {
                        if($p->idTareas == $s->idTareas){ 
                            $sumaNotaSegunda = $sumaNotaSegunda + $s->calificacion;
                        }else{
                            $sumaNotaPrimera = $sumaNotaPrimera + $p->calificacion;
                        } 
                    }       
            }

            $sumaTotal=$sumaNotaPrimera+$sumaNotaSegunda;

            if($totalTarea==0){
                $promedio=0;
            }else{
                $promedio=$sumaTotal/$totalTarea;
            }

            $cantidadFaltas=$cantFaltas[0]->totalClase;
            $totalClases=$cantClasesListadas[0]->totalClase;

            if($totalClases==0){
                $porcentajeFaltas=0;
            }else{
                $porcentajeFaltas=(100*$cantidadFaltas)/$totalClases;
            }

            $datos = [
                "idAlumnos"=>$a->idAlumnos,
                "nombreAlumno"=>$a->nombre,
                "promedio" => round($promedio),
                "asistencia"=>$a->idAlumnos,
                "porcentajeFaltas"=>round($porcentajeFaltas),
                "cantidadFaltas"=>$cantidadFaltas,
                "cantidadClases"=>$totalClases
            ];

            array_push($dataResponse, $datos);


        }

        return response()->json($dataResponse);

    }

    public function subirTarea($request,$idTarea,$idAlumno)
    {
        $this->subirEntrega($request,$idTarea,$idAlumno);

        if ($request->archivos) {
            $this->subirArchivosEntrega($request,$idTarea,$idAlumno);
        }
        RegistrosController::store("ENTREGA TAREA",$request->header('token'),"CREATE",$idAlumno);
        return response()->json(['status' => 'Success'], 200);
    }

    public function reHacerTarea($request,$idTarea,$idAlumno)
    {
        try{
            $this->subirReHacerTarea($request,$idTarea,$idAlumno);

            if ($request->archivos) {
    
                $this->subirArchivosReHacerTarea($request,$idTarea,$idAlumno);
            }   
                AlumnoEntrega::where('idTareas', $idTarea)->where('idAlumnos', $idAlumno)->update(['re_hacer' => 0]);
                RegistrosController::store("RE-ENTREGA TAREA",$request->header('token'),"CREATE",$idAlumno);
    
            return response()->json(['status' => 'Success'], 200);
           
        }catch(\Exception $e){
            return response()->json(['status' => 'Error'], 400);
        }
           
    }





    public function listarEntregas($idGrupo,$idMateria,$idTarea)
    {

        $entregas = $this->getAllEntregasGrupoMateria($idGrupo,$idMateria,$idTarea);

        $entregasCorregidas = $this->getEntregasCorregidasGrupoMateria($idGrupo,$idMateria,$idTarea);


        $entregasReHacer = $this->getAllEntregasReHacerGrupoMateria($idGrupo,$idMateria,$idTarea);

        $entregasReHacerCorregidas = $this->getEntregasReHacerCorregidasGrupoMateria($idGrupo,$idMateria,$idTarea);


        $entregas_tarea = array();
        $entregas_tarea_corregidas = array();
        $entregas_re_hacer_tarea = array();
        $entregas_re_hacer_tarea_corregidas = array();
        foreach ($entregas as $t) {

            $datos = [
                'idTarea' => $t->idTareas,
                'idAlumnos' => $t->idAlumnos,
                'calificacion' => $t->calificacion,
                'usuario' => $t->nombreUsuario,
                'idMateria' => $t->idMateria,
                'idGrupo' => $t->idGrupo,
                'idProfesor' => $t->idProfesor,
                'titulo' => $t->titulo,
                'descripcion' => $t->descripcion,

            ];

            array_push($entregas_tarea, $datos);
        }

        foreach ($entregasCorregidas as $t) {

            $existe = AlumnoReHacerTarea::where('idTareas', $t->idTareas)->first();
            if (!$existe) {
                $datosCorregidos = [
                    'idTarea' => $t->idTareas,
                    'idAlumnos' => $t->idAlumnos,
                    'calificacion' => $t->calificacion,
                    'usuario' => $t->nombreUsuario,
                    'idMateria' => $t->idMateria,
                    'idGrupo' => $t->idGrupo,
                    'idProfesor' => $t->idProfesor,
                    'titulo' => $t->titulo,
                    'descripcion' => $t->descripcion,

                ];

                array_push($entregas_tarea_corregidas, $datosCorregidos);
            }
        }

        foreach ($entregasReHacer as $p) {
            $reHacer = [
                'idTarea' => $p->idTareas,
                'idAlumnos' => $p->idAlumnos,
                'calificacion' => $p->calificacion,
                'usuario' => $p->nombreUsuario,
                'idMateria' => $p->idMateria,
                'idGrupo' => $p->idGrupo,
                'idProfesor' => $p->idProfesor,
                'titulo' => $p->titulo,
                'descripcion' => $p->descripcion,
            ];

            array_push($entregas_re_hacer_tarea, $reHacer);
        }

        foreach ($entregasReHacerCorregidas as $p) {
            $reHacerCorregidas = [
                'idTarea' => $p->idTareas,
                'idAlumnos' => $p->idAlumnos,
                'calificacion' => $p->calificacion,
                'usuario' => $p->nombreUsuario,
                'idMateria' => $p->idMateria,
                'idGrupo' => $p->idGrupo,
                'idProfesor' => $p->idProfesor,
                'titulo' => $p->titulo,
                'descripcion' => $p->descripcion,
            ];

            array_push($entregas_re_hacer_tarea_corregidas, $reHacerCorregidas);
        }
        $entregas_totalesNoCorregidas = [
            'entregas_tareas_no_corregidas' => $entregas_tarea,
            're_hacer_no_corregidas' => $entregas_re_hacer_tarea,
        ];
        $entregas_totalesCorregidas = [
            'entregas_tareas_corregidas' => $entregas_tarea_corregidas,
            're_hacer_corregidas' => $entregas_re_hacer_tarea_corregidas,
        ];

        $entregas_totales = [
            'entregas_totalesNoCorregidas' => $entregas_totalesNoCorregidas,
            'entregas_totalesCorregidas' => $entregas_totalesCorregidas,
        ];

        return response()->json($entregas_totales);
    }

    public function listarEntregasAlumno($idAlumno)
    {
        $entregas = $this->getEntregasAlumno($idAlumno);
        return $entregas;
    }

    public function entregaAlumno($idTarea,$idAlumno)
    {

        $peticionSQL = $this->getEntregaAlumno($idTarea,$idAlumno);

        $dataResponse = array();

        foreach ($peticionSQL as $p) {
           
            $peticionSQLFiltrada = $this->getArchivosEntrega($p);

            $arrayDeArchivos = array();
            $arrayImagenes = array();
            $postAuthor = $p->idAlumnos;

            $imgPerfil = $this->getImgPerfil($postAuthor);

            $img = base64_encode(Storage::disk('ftp')->get($imgPerfil));

            foreach ($peticionSQLFiltrada as $p2) {

                strpos($p2->archivo, ".pdf") != null ?  array_push($arrayDeArchivos, $p2->archivo) :  array_push($arrayImagenes, $p2->archivo);
            }


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
                "imagenes" => $arrayImagenes,
            ];

            array_push($dataResponse, $p);
        }
        return response()->json($dataResponse);
    }

    public function entregaAlumnoReHacer($idTarea,$idAlumno)
    {

        $peticionSQL = $this->getReHacerEntregaAlumno($idTarea,$idAlumno);

        $dataResponse = array();

        
            foreach ($peticionSQL as $p) {
               
                $peticionSQLFiltrada = $this->getArchivosReHacerEntrega($p);
    
                $arrayDeArchivos = array();
                $arrayImagenes = array();
                $postAuthor = $p->idAlumnos;
    
                $imgPerfil = $this->getImgPerfil($postAuthor);
    
                $img = base64_encode(Storage::disk('ftp')->get($imgPerfil));
    
                foreach ($peticionSQLFiltrada as $p2) {
    
                    strpos($p2->archivo, ".pdf") != null ?  array_push($arrayDeArchivos, $p2->archivo) :  array_push($arrayImagenes, $p2->archivo);
                }
    
    
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
                    "imagenes" => $arrayImagenes,
                ];
    
                array_push($dataResponse, $p);
            }
            return response()->json($dataResponse);
    }
 
    public function verificarCorreccion($idTarea,$idAlumno,Request $request)
    {
        $request->validate([
            'calificacion' => 'required | numeric',
            'mensaje' => 'required | string',
            're_hacer' => 'required | boolean',
            're_entrega' => 'required | boolean',
        ]);

     
        if ($request->re_entrega) {
            return self::corregirEntregaReHacer($idTarea,$idAlumno,$request);
        } else {
            return self::corregirEntrega($idTarea,$idAlumno,$request);
        }
    }

    public function corregirEntrega($idTarea,$idAlumno,$request)
    {
        try {
                AlumnoEntrega::where('idTareas', $idTarea)->where('idAlumnos', $idAlumno)->update([
                    'calificacion' => $request->calificacion,
                    'mensaje_profesor' => $request->mensaje,
                    're_hacer' => $request->re_hacer,
                ]);
                RegistrosController::store("CORRECION ENTREGA",$request->header('token'),"UPDATE","");
                return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }


    public function corregirEntregaReHacer($idTarea,$idAlumno,Request $request)
    {   
       
        try {
                AlumnoReHacerTarea::where('idTareas', $idTarea)->where('idAlumnos', $idAlumno)->update(['calificacion' => $request->calificacion, 'mensaje_profesor' => $request->mensaje]);
                RegistrosController::store("CORRECION RE-ENTREGA",$request->header('token'),"UPDATE","");
                return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

 
    public function getPrimeraEntregaAlumno($idTarea,$idAlumno)
    {
        $primera_entrega = DB::table('alumno_entrega_tareas')
            ->select('alumno_entrega_tareas.idTareas AS idTareas', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'usuarios.nombre AS nombreAlumno', 'alumno_entrega_tareas.created_at AS fecha', 'alumno_entrega_tareas.calificacion AS calificacion', 'alumno_entrega_tareas.mensaje AS mensajeAlumno', 'alumno_entrega_tareas.mensaje_profesor AS mensajeProfesor')
            ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.id')
            ->where('alumno_entrega_tareas.idTareas', $idTarea)
            ->where('alumno_entrega_tareas.idAlumnos', $idAlumno)
            ->get();
        return $primera_entrega;
    }

    public function getSegundaEntregaAlumno($idTarea,$idAlumno)
    {
        $segunda_entrega = DB::table('re_hacer_tareas')
            ->select('re_hacer_tareas.idTareas AS idTareas', 're_hacer_tareas.idAlumnos AS idAlumnos', 'usuarios.nombre AS nombreAlumno', 're_hacer_tareas.created_at AS fecha_entrega', 're_hacer_tareas.calificacion AS calificacion', 're_hacer_tareas.mensaje AS mensajeAlumno', 're_hacer_tareas.mensaje_profesor AS mensajeProfesor')
            ->join('usuarios', 're_hacer_tareas.idAlumnos', '=', 'usuarios.id')
            ->where('re_hacer_tareas.idTareas', $idTarea)
            ->where('re_hacer_tareas.idAlumnos', $idAlumno)
            ->get();
        return $segunda_entrega;
    }

    
    public function getImagenPerfilAlumno($idAlumno)
    {
        $usuario = DB::table('usuarios')
            ->select('usuarios.imagen_perfil AS img')
            ->where('usuarios.id', $idAlumno)
            ->first();
        return $usuario->img;
    }

  
    public function getArchivosTareaAlumno($idTarea,$idAlumno)
    {
        $traerArchivosTareaAlumno = DB::table('archivos_entrega')
            ->select('nombreArchivo AS archivoAlumno')
            ->where('idTareas', $idTarea)
            ->where('idAlumnos', $idAlumno)
            ->distinct()
            ->get();
        return $traerArchivosTareaAlumno;
    }

  
    public function getArchivosTareaAlumno2($idTarea,$idAlumno)
    {
        $traerArchivosTareaAlumno2 = DB::table('archivos_re_hacer_tarea')
            ->select('nombreArchivo AS archivoAlumno')
            ->where('idTareas', $idTarea)
            ->where('idAlumnos', $idAlumno)
            ->distinct()
            ->get();
        return $traerArchivosTareaAlumno2;
    }

   
    public function getCalificacionPrimeraEntrega($idGrupo,$idMateria,$idUsuario)
    {
        $primera_entrega = DB::table('alumno_entrega_tareas')
            ->select('alumno_entrega_tareas.idTareas AS idTareas', 'alumno_entrega_tareas.idAlumnos', 'usuarios.nombre as nombreAlumno', 'tareas.titulo', 'tareas.descripcion', 'alumno_entrega_tareas.created_at AS fecha', 'alumno_entrega_tareas.calificacion AS calificacion', 'alumno_entrega_tareas.mensaje AS mensajeAlumno', 'alumno_entrega_tareas.mensaje_profesor AS mensajeProfesor')
            ->join('profesor_crea_tareas', 'alumno_entrega_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
            ->join('tareas', 'alumno_entrega_tareas.idTareas', '=', 'tareas.id')
            ->join('usuarios', 'usuarios.id', '=', 'alumno_entrega_tareas.idAlumnos')
            ->where('alumno_entrega_tareas.idAlumnos', $idUsuario)
            ->where('profesor_crea_tareas.idMateria', $idMateria)
            ->where('profesor_crea_tareas.idGrupo', $idGrupo)
            ->get();
        return $primera_entrega;
    }

   
    public function getCalificacionSegundaEntrega($p, $idGrupo,$idMateria,$idAlumno)
    {
        $segunda_entrega = DB::table('re_hacer_tareas')
            ->select('re_hacer_tareas.calificacion')
            ->join('profesor_crea_tareas', 're_hacer_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
            ->join('alumno_entrega_tareas', 're_hacer_tareas.idTareas', '=', 'alumno_entrega_tareas.idTareas')
            ->join('tareas', 'tareas.id', '=', 're_hacer_tareas.idTareas')
            ->where('re_hacer_tareas.idTareas', $p->idTareas)
            ->where('alumno_entrega_tareas.idAlumnos', $idAlumno)
            ->where('profesor_crea_tareas.idMateria', $idMateria)
            ->where('profesor_crea_tareas.idGrupo', $idGrupo)
            ->first();
        return $segunda_entrega;
    }

 
    public function getCantidadClasesListadas(Request $request, Carbon $fecha_actual)
    {
        $cantClasesListadas = DB::table('agenda_clase_virtual')
            ->select(DB::raw('count(*) as totalClase'))
            ->where('agenda_clase_virtual.idMateria', $request->idMateria)
            ->where('agenda_clase_virtual.idGrupo', $request->idGrupo)
            ->where('agenda_clase_virtual.fecha_fin', '<=', $fecha_actual)
            ->get();
        return $cantClasesListadas;
    }

   
    public function getTareasTotalesMateriaGrupo(Request $request)
    {
        $tareasTotales = DB::table('profesor_crea_tareas')
            ->select(DB::raw('count(*) as totalTareas'))
            ->where('profesor_crea_tareas.idMateria', $request->idMateria)
            ->where('profesor_crea_tareas.idGrupo', $request->idGrupo)
            ->groupBy('idMateria', 'idGrupo')
            ->first();
        return $tareasTotales;
    }

 
    public function getAlumnosGrupo(Request $request)
    {
        $alumnos = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idAlumnos', 'usuarios.nombre')
            ->join('usuarios', 'alumnos_pertenecen_grupos.idAlumnos', '=', 'usuarios.id')
            ->where('alumnos_pertenecen_grupos.idGrupo', $request->idGrupo)
            ->get();
        return $alumnos;
    }

    public function cantidadFaltasPorAlumno(Request $request, $a)
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

 
    public function datosPrimeraEntrega($a, Request $request)
    {
        $primera_entrega = DB::table('alumno_entrega_tareas')
            ->select('alumno_entrega_tareas.idTareas AS idTareas', 'alumno_entrega_tareas.idAlumnos', 'usuarios.nombre as nombreAlumno', 'tareas.titulo', 'tareas.descripcion', 'alumno_entrega_tareas.created_at AS fecha', 'alumno_entrega_tareas.calificacion AS calificacion', 'alumno_entrega_tareas.mensaje AS mensajeAlumno', 'alumno_entrega_tareas.mensaje_profesor AS mensajeProfesor')
            ->join('profesor_crea_tareas', 'alumno_entrega_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
            ->join('tareas', 'alumno_entrega_tareas.idTareas', '=', 'tareas.id')
            ->join('usuarios', 'usuarios.id', '=', 'alumno_entrega_tareas.idAlumnos')
            ->leftJoin('re_hacer_tareas', 're_hacer_tareas.idTareas', '=', 'alumno_entrega_tareas.idAlumnos')
            ->where('alumno_entrega_tareas.idAlumnos', $a->idAlumnos)
            ->where('profesor_crea_tareas.idMateria', $request->idMateria)
            ->where('profesor_crea_tareas.idGrupo', $request->idGrupo)
            ->get();
        return $primera_entrega;
    }

   

    public function datosSegundaEntrega($a, Request $request)
    {
        $segunda_entrega = DB::table('re_hacer_tareas')
            ->select('re_hacer_tareas.calificacion', 're_hacer_tareas.idTareas')
            ->join('profesor_crea_tareas', 're_hacer_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
            ->join('alumno_entrega_tareas', 're_hacer_tareas.idTareas', '=', 'alumno_entrega_tareas.idTareas')
            ->join('tareas', 'tareas.id', '=', 're_hacer_tareas.idTareas')
            ->where('alumno_entrega_tareas.idAlumnos', $a->idAlumnos)
            ->where('profesor_crea_tareas.idMateria', $request->idMateria)
            ->where('profesor_crea_tareas.idGrupo', $request->idGrupo)
            ->get();
        return $segunda_entrega;
    }

  
    public function subirArchivosEntrega($request,$idTarea,$idAlumno)
    {
        for ($i = 0; $i < count($request->nombresArchivo); $i++) {
            $nombreArchivo = random_int(0, 1000000) . "_" . $request->nombresArchivo[$i];
            Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivos[$i], 'r+'));
            $archivosEntrega = new archivosEntrega;
            $archivosEntrega->idTareas = $idTarea;
            $archivosEntrega->idAlumnos = $idAlumno;
            $archivosEntrega->nombreArchivo = $nombreArchivo;
            $archivosEntrega->save();
        }
    }

  
    public function subirArchivosReHacerTarea($request,$idTarea,$idAlumno)
    {
        for ($i = 0; $i < count($request->nombresArchivo); $i++) {
            $nombreArchivo = random_int(0, 1000000) . "_" . $request->nombresArchivo[$i];
            Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivos[$i], 'r+'));
            $archivosReHacer = new archivosReHacerTarea;
            $archivosReHacer->idTareas = $idTarea;
            $archivosReHacer->idTareasNueva = $idTarea;
            $archivosReHacer->idAlumnos = $idAlumno;
            $archivosReHacer->nombreArchivo = $nombreArchivo;
            $archivosReHacer->save();
        }
    }

   
    public function getAllEntregasGrupoMateria($idGrupo,$idMateria,$idTarea)
    {
        $entregas = DB::table('alumno_entrega_tareas')
            ->select('alumno_entrega_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'alumno_entrega_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario', 'profesor_crea_tareas.idGrupo', 'profesor_crea_tareas.idProfesor', 'profesor_crea_tareas.idMateria')
            ->join('profesor_crea_tareas', 'alumno_entrega_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
            ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.id')
            ->join('tareas', 'alumno_entrega_tareas.idTareas', '=', 'tareas.id')
            ->where('profesor_crea_tareas.idGrupo', $idGrupo)
            ->where('alumno_entrega_tareas.idTareas', $idTarea)
            ->whereNull('alumno_entrega_tareas.calificacion')
            ->where('profesor_crea_tareas.idMateria', $idMateria)
            ->orderBy('alumno_entrega_tareas.created_at', 'desc')
            ->get();
        return $entregas;
    }

    public function getEntregasCorregidasGrupoMateria($idGrupo,$idMateria,$idTarea)
    {
        $entregasCorregidas = DB::table('alumno_entrega_tareas')
            ->select('alumno_entrega_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'alumno_entrega_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario', 'profesor_crea_tareas.idGrupo', 'profesor_crea_tareas.idProfesor', 'profesor_crea_tareas.idMateria')
            ->join('profesor_crea_tareas', 'alumno_entrega_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
            ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.id')
            ->join('tareas', 'alumno_entrega_tareas.idTareas', '=', 'tareas.id')
            ->where('profesor_crea_tareas.idGrupo', $idGrupo)
            ->where('alumno_entrega_tareas.idTareas', $idTarea)
            ->whereNotNull('alumno_entrega_tareas.calificacion')
            ->where('profesor_crea_tareas.idMateria', $idMateria)
            ->orderBy('alumno_entrega_tareas.created_at', 'desc')
            ->get();
        return $entregasCorregidas;
    }


    public function getAllEntregasReHacerGrupoMateria($idGrupo,$idMateria,$idTarea)
    {
        $entregasReHacer = DB::table('re_hacer_tareas')
            ->select('re_hacer_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 're_hacer_tareas.idAlumnos AS idAlumnos', 're_hacer_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario', 'profesor_crea_tareas.idGrupo', 'profesor_crea_tareas.idProfesor', 'profesor_crea_tareas.idMateria')
            ->join('profesor_crea_tareas', 're_hacer_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
            ->join('usuarios', 're_hacer_tareas.idAlumnos', '=', 'usuarios.id')
            ->join('tareas', 're_hacer_tareas.idTareas', '=', 'tareas.id')
            ->where('profesor_crea_tareas.idGrupo', $idGrupo)
            ->where('re_hacer_tareas.idTareas', $idTarea)
            ->whereNull('re_hacer_tareas.calificacion')
            ->where('profesor_crea_tareas.idMateria', $idMateria)
            ->orderBy('re_hacer_tareas.created_at', 'desc')
            ->get();
        return $entregasReHacer;
    }

 
    public function getEntregasReHacerCorregidasGrupoMateria($idGrupo,$idMateria,$idTarea)
    {
        $entregasReHacerCorregidas = DB::table('re_hacer_tareas')
            ->select('re_hacer_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 're_hacer_tareas.idAlumnos AS idAlumnos', 're_hacer_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario', 'profesor_crea_tareas.idGrupo', 'profesor_crea_tareas.idProfesor', 'profesor_crea_tareas.idMateria')
            ->join('profesor_crea_tareas', 're_hacer_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
            ->join('usuarios', 're_hacer_tareas.idAlumnos', '=', 'usuarios.id')
            ->join('tareas', 're_hacer_tareas.idTareas', '=', 'tareas.id')
            ->where('profesor_crea_tareas.idGrupo', $idGrupo)
            ->where('re_hacer_tareas.idTareas', $idTarea)
            ->whereNotNull('re_hacer_tareas.calificacion')
            ->where('profesor_crea_tareas.idMateria', $idMateria)
            ->orderBy('re_hacer_tareas.created_at', 'desc')
            ->get();
        return $entregasReHacerCorregidas;
    }

   
    public function subirEntrega($request,$idTarea,$idAlumno)
    {
        $alumnoTarea = new AlumnoEntrega;
        $alumnoTarea->idTareas = $idTarea;
        $alumnoTarea->idAlumnos = $idAlumno;
        $alumnoTarea->mensaje = $request->mensaje;
        $alumnoTarea->re_hacer = 0;
        $alumnoTarea->save();
    }

   
    public function subirReHacerTarea($request,$idTarea,$idAlumno)
    {
        $alumnoReHacer = new AlumnoReHacerTarea;
        $alumnoReHacer->idTareasNueva = $idTarea;
        $alumnoReHacer->idTareas = $idTarea;
        $alumnoReHacer->idAlumnos = $idAlumno;
        $alumnoReHacer->mensaje = $request->mensaje;
        $alumnoReHacer->save();
    }

  
    public function getEntregasAlumno($idAlumno)
    {
        $entregas = DB::table('alumno_entrega_tareas')
            ->select('alumno_entrega_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'alumno_entrega_tareas.re_hacer AS re_hacer', 'tareas.descripcion', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'alumno_entrega_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario', 'profesor_crea_tareas.idGrupo', 'profesor_crea_tareas.idProfesor', 'profesor_crea_tareas.idMateria')
            ->join('profesor_crea_tareas', 'alumno_entrega_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
            ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.id')
            ->join('tareas', 'alumno_entrega_tareas.idTareas', '=', 'tareas.id')
            ->where('alumno_entrega_tareas.idAlumnos', $idAlumno)
            ->orderBy('alumno_entrega_tareas.created_at', 'desc')
            ->get();
        return $entregas;
    }

   
    public function getEntregaAlumno($idTarea,$idAlumno)
    {
        $peticionSQL = DB::table('alumno_entrega_tareas')
            ->select('alumno_entrega_tareas.idTareas AS idTareas', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'alumno_entrega_tareas.created_at AS fecha', 'alumno_entrega_tareas.calificacion AS calificacion', 'alumno_entrega_tareas.mensaje AS mensaje', 'usuarios.nombre AS nombreUsuario')
            ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.id')
            ->where('alumno_entrega_tareas.idTareas', $idTarea)
            ->where('alumno_entrega_tareas.idAlumnos', $idAlumno)
            ->get();
        return $peticionSQL;
    }

  
    public function getArchivosEntrega($p)
    {
        $peticionSQLFiltrada = DB::table('archivos_entrega')
            ->select('id AS idArchivo', 'nombreArchivo AS archivo')
            ->where('idTareas', $p->idTareas)
            ->where('idAlumnos', $p->idAlumnos)
            ->distinct()
            ->get();
        return $peticionSQLFiltrada;
    }

  
    public function getImgPerfil($postAuthor)
    {
        $usuario = DB::table('usuarios')
            ->select('imagen_perfil')
            ->where('id', $postAuthor)
            ->first();
        return $usuario->imagen_perfil;
    }

  
    public function getReHacerEntregaAlumno($idTarea,$idAlumno)
    {
        $peticionSQL = DB::table('re_hacer_tareas')
            ->select('re_hacer_tareas.idTareas AS idTareas', 're_hacer_tareas.idAlumnos AS idAlumnos', 're_hacer_tareas.created_at AS fecha','re_hacer_tareas.mensaje AS mensaje', 're_hacer_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario')
            ->join('usuarios', 're_hacer_tareas.idAlumnos', '=', 'usuarios.id')
            ->where('re_hacer_tareas.idTareas', $idTarea)
            ->where('re_hacer_tareas.idAlumnos', $idAlumno)
            ->get();
        return $peticionSQL;
    }


    public function getArchivosReHacerEntrega($p)
    {
        $peticionSQLFiltrada = DB::table('archivos_re_hacer_tarea')
            ->select('id AS idArchivo', 'nombreArchivo AS archivo')
            ->where('idTareas', $p->idTareas)
            ->where('idAlumnos', $p->idAlumnos)
            ->distinct()
            ->get();
        return $peticionSQLFiltrada;
    }
}
