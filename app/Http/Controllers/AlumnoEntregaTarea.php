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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

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
    public function seleccion(Request $request)
    {
        if($request->re_hacer){
             return self::reHacerTarea($request);
        }else{
            return  self::subirTarea($request);

        }


    }

    public function subirTarea($request)
    {

        $alumnoTarea = new AlumnoEntrega;
        $alumnoTarea->idTareas = $request->idTareas;
        $alumnoTarea->idAlumnos = $request->idAlumnos;
        $alumnoTarea->mensaje = $request->mensaje;
        $alumnoTarea->re_hacer = 0;
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
            return response()->json(['status' => 'Success'], 200);
    }

    public function reHacerTarea($request)
    {

        $alumnoReHacer = new AlumnoReHacerTarea;
        $alumnoReHacer->idTareasNueva = $request->idTareas;
        $alumnoReHacer->idTareas = $request->idTareas;
        $alumnoReHacer->idAlumnos = $request->idAlumnos;
        $alumnoReHacer->mensaje = $request->mensaje;
        $alumnoReHacer->save();


        $nombreArchivosArray = explode(',', $request->nombre_archivos);
                if ($request->nombre_archivos) {
                    foreach ($nombreArchivosArray as $nombres) {
                        $archivosReHacer = new archivosReHacerTarea;
                        $archivosReHacer->idTareas = $request->idTareas;
                        $archivosReHacer->idTareasNueva = $request->idTareas;
                        $archivosReHacer->idAlumnos = $request->idAlumnos;
                        $archivosReHacer->nombreArchivo = $nombres;
                        $archivosReHacer->save();

                    }

                }
            $existe = AlumnoEntrega::where('idTareas', $request->idTareas)->where('idAlumnos', $request->idAlumnos)->first();
              /*   $reHacer=0; */
                    if ($existe) {
        
                        DB::update('UPDATE alumno_entrega_tareas SET re_hacer="0" WHERE idTareas="' . $request->idTareas . '" AND idAlumnos="' . $request->idAlumnos . '";');
                       
                    }
            return response()->json(['status' => 'Success'], 200);
    }



    

    public function listarEntregas(Request $request){

        $entregas=DB::table('alumno_entrega_tareas')
                    ->select('alumno_entrega_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'alumno_entrega_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario' ,'profesor_crea_tareas.idGrupo' ,'profesor_crea_tareas.idProfesor' ,'profesor_crea_tareas.idMateria')
                    ->join('profesor_crea_tareas', 'alumno_entrega_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
                    ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.username') 
                    ->join('tareas', 'alumno_entrega_tareas.idTareas', '=', 'tareas.id')   
                    ->where('profesor_crea_tareas.idGrupo',$request->idGrupo)
                    ->where('alumno_entrega_tareas.idTareas',$request->idTareas)
                    ->whereNull('alumno_entrega_tareas.calificacion')
                    ->where('profesor_crea_tareas.idMateria',$request->idMateria)
                    ->orderBy('alumno_entrega_tareas.created_at', 'desc')
                    ->get();

        $entregasCorregidas=DB::table('alumno_entrega_tareas')
                    ->select('alumno_entrega_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'alumno_entrega_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario' ,'profesor_crea_tareas.idGrupo' ,'profesor_crea_tareas.idProfesor' ,'profesor_crea_tareas.idMateria')
                    ->join('profesor_crea_tareas', 'alumno_entrega_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
                    ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.username') 
                    ->join('tareas', 'alumno_entrega_tareas.idTareas', '=', 'tareas.id') 
                 
                    ->where('profesor_crea_tareas.idGrupo',$request->idGrupo)
                    ->where('alumno_entrega_tareas.idTareas',$request->idTareas)
                    ->whereNotNull('alumno_entrega_tareas.calificacion')
                    ->where('profesor_crea_tareas.idMateria',$request->idMateria)
                    ->orderBy('alumno_entrega_tareas.created_at', 'desc')
                    ->get();
 

        $entregasReHacer=DB::table('re_hacer_tareas')
                    ->select('re_hacer_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 're_hacer_tareas.idAlumnos AS idAlumnos', 're_hacer_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario' ,'profesor_crea_tareas.idGrupo' ,'profesor_crea_tareas.idProfesor' ,'profesor_crea_tareas.idMateria')
                    ->join('profesor_crea_tareas', 're_hacer_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
                    ->join('usuarios', 're_hacer_tareas.idAlumnos', '=', 'usuarios.username')   
                    ->join('tareas', 're_hacer_tareas.idTareas', '=', 'tareas.id')  
                    ->where('profesor_crea_tareas.idGrupo',$request->idGrupo)
                    ->where('re_hacer_tareas.idTareas',$request->idTareas)
                    ->whereNull('re_hacer_tareas.calificacion')
                    ->where('profesor_crea_tareas.idMateria',$request->idMateria)
                    ->orderBy('re_hacer_tareas.created_at', 'desc')
                    ->get();
        
        $entregasReHacerCorregidas=DB::table('re_hacer_tareas')
                    ->select('re_hacer_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 're_hacer_tareas.idAlumnos AS idAlumnos', 're_hacer_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario' ,'profesor_crea_tareas.idGrupo' ,'profesor_crea_tareas.idProfesor' ,'profesor_crea_tareas.idMateria')
                    ->join('profesor_crea_tareas', 're_hacer_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
                    ->join('usuarios', 're_hacer_tareas.idAlumnos', '=', 'usuarios.username')   
                    ->join('tareas', 're_hacer_tareas.idTareas', '=', 'tareas.id')  
                    ->where('profesor_crea_tareas.idGrupo',$request->idGrupo)
                    ->where('re_hacer_tareas.idTareas',$request->idTareas)
                    ->whereNotNull('re_hacer_tareas.calificacion')
                    ->where('profesor_crea_tareas.idMateria',$request->idMateria)
                    ->orderBy('re_hacer_tareas.created_at', 'desc')
                    ->get();

   

        $entregas_tarea=array();
        $entregas_tarea_corregidas=array();
        $entregas_re_hacer_tarea=array();
        $entregas_re_hacer_tarea_corregidas=array();
        foreach ($entregas as $t) {

                 $datos = [
                    'idTarea'=> $t->idTareas,
                    'idAlumnos'=> $t->idAlumnos,
                    'calificacion'=> $t->calificacion,
                    'usuario'=> $t->nombreUsuario,
                    'idMateria'=> $t->idMateria,
                    'idGrupo'=> $t->idGrupo,
                    'idProfesor'=> $t->idProfesor,
                    'titulo'=> $t->titulo,
                    'descripcion'=> $t->descripcion,
                    
                ];

                array_push($entregas_tarea,$datos);
    
            }

                foreach ($entregasCorregidas as $t) {

                    $existe = AlumnoReHacerTarea::where('idTareas', $t->idTareas)->first();
                    if(!$existe){
                         $datosCorregidos = [
                        'idTarea'=> $t->idTareas,
                        'idAlumnos'=> $t->idAlumnos,
                        'calificacion'=> $t->calificacion,
                        'usuario'=> $t->nombreUsuario,
                        'idMateria'=> $t->idMateria,
                        'idGrupo'=> $t->idGrupo,
                        'idProfesor'=> $t->idProfesor,
                        'titulo'=> $t->titulo,
                        'descripcion'=> $t->descripcion,
                        
                    ];
    
                    array_push($entregas_tarea_corregidas,$datosCorregidos);}

                
    
            }

            foreach ($entregasReHacer as $p) {
                $reHacer = [
                    'idTarea'=> $p->idTareas,
                    'idAlumnos'=> $p->idAlumnos,
                    'calificacion'=> $p->calificacion,
                    'usuario'=> $p->nombreUsuario,
                    'idMateria'=> $p->idMateria,
                    'idGrupo'=> $p->idGrupo,
                    'idProfesor'=> $p->idProfesor,
                    'titulo'=> $p->titulo,
                    'descripcion'=> $p->descripcion,
                ];

                array_push($entregas_re_hacer_tarea,$reHacer);
            }    

            foreach ($entregasReHacerCorregidas as $p) {
                $reHacerCorregidas = [
                    'idTarea'=> $p->idTareas,
                    'idAlumnos'=> $p->idAlumnos,
                    'calificacion'=> $p->calificacion,
                    'usuario'=> $p->nombreUsuario,
                    'idMateria'=> $p->idMateria,
                    'idGrupo'=> $p->idGrupo,
                    'idProfesor'=> $p->idProfesor,
                    'titulo'=> $p->titulo,
                    'descripcion'=> $p->descripcion,
                ];

                array_push($entregas_re_hacer_tarea_corregidas,$reHacerCorregidas);
            }    
            $entregas_totalesNoCorregidas=[
                'entregas_tareas_no_corregidas'=>$entregas_tarea,
                're_hacer_no_corregidas'=>$entregas_re_hacer_tarea,
            ];
            $entregas_totalesCorregidas=[
                'entregas_tareas_corregidas'=>$entregas_tarea_corregidas,
                're_hacer_corregidas'=>$entregas_re_hacer_tarea_corregidas,
            ];
        
            $entregas_totales=[
                'entregas_totalesNoCorregidas'=>$entregas_totalesNoCorregidas,
                'entregas_totalesCorregidas'=>$entregas_totalesCorregidas,
            ];

        return response()->json($entregas_totales);

    }

    public function listarEntregasAlumno(Request $request){

        $entregas=DB::table('alumno_entrega_tareas')
                    ->select('alumno_entrega_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'alumno_entrega_tareas.re_hacer AS re_hacer', 'tareas.descripcion', 'alumno_entrega_tareas.idAlumnos AS idAlumnos', 'alumno_entrega_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario' ,'profesor_crea_tareas.idGrupo' ,'profesor_crea_tareas.idProfesor' ,'profesor_crea_tareas.idMateria')
                    ->join('profesor_crea_tareas', 'alumno_entrega_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
                    ->join('usuarios', 'alumno_entrega_tareas.idAlumnos', '=', 'usuarios.username') 
                    ->join('tareas', 'alumno_entrega_tareas.idTareas', '=', 'tareas.id')   
                    ->where('alumno_entrega_tareas.idAlumnos',$request->idAlumnos)
                    ->orderBy('alumno_entrega_tareas.created_at', 'desc')
                    ->get();

        $entregasReHacer=DB::table('re_hacer_tareas')
                    ->select('re_hacer_tareas.idTareas AS idTareas', 'tareas.titulo AS titulo', 'tareas.descripcion', 're_hacer_tareas.idAlumnos AS idAlumnos', 're_hacer_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario' ,'profesor_crea_tareas.idGrupo' ,'profesor_crea_tareas.idProfesor' ,'profesor_crea_tareas.idMateria')
                    ->join('profesor_crea_tareas', 're_hacer_tareas.idTareas', '=', 'profesor_crea_tareas.idTareas')
                    ->join('usuarios', 're_hacer_tareas.idAlumnos', '=', 'usuarios.username')   
                    ->join('tareas', 're_hacer_tareas.idTareas', '=', 'tareas.id')  
                    ->where('re_hacer_tareas.idAlumnos',$request->idAlumnos)
                    ->orderBy('re_hacer_tareas.created_at', 'desc')
                    ->get();

   

        $entregas_tarea=array();
        $entregas_re_hacer_tarea=array();
        foreach ($entregas as $t) {

                 $datos = [
                    'idTarea'=> $t->idTareas,
                    'idAlumnos'=> $t->idAlumnos,
                    'calificacion'=> $t->calificacion,
                    'usuario'=> $t->nombreUsuario,
                    'idMateria'=> $t->idMateria,
                    'idGrupo'=> $t->idGrupo,
                    'idProfesor'=> $t->idProfesor,
                    'titulo'=> $t->titulo,
                    'descripcion'=> $t->descripcion,
                    'reHacer'=> $t->re_hacer,
                    
                ];

                array_push($entregas_tarea,$datos);
    
            }

            foreach ($entregasReHacer as $p) {
                $reHacer = [
                    'idTarea'=> $p->idTareas,
                    'idAlumnos'=> $p->idAlumnos,
                    'calificacion'=> $p->calificacion,
                    'usuario'=> $p->nombreUsuario,
                    'idMateria'=> $p->idMateria,
                    'idGrupo'=> $p->idGrupo,
                    'idProfesor'=> $p->idProfesor,
                    'titulo'=> $p->titulo,
                    'descripcion'=> $p->descripcion,
                ];

                array_push($entregas_re_hacer_tarea,$reHacer);
            }    
            $entregas_totales=[
                'entregas_tareas'=>$entregas_tarea,
                're_hacer'=>$entregas_re_hacer_tarea,
            ];
        

        return response()->json($entregas_totales);

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
            $arrayImagenes = array();
            $postAuthor = $p->idAlumnos;

            $imgPerfil = DB::table('usuarios')
                ->select('imagen_perfil')
                ->where('username', $postAuthor)
                ->get();

            $img = base64_encode(Storage::disk('ftp')->get($imgPerfil[0]->imagen_perfil));

            foreach ($peticionSQLFiltrada as $p2) {

                $resultado = strpos($p2->archivo, ".pdf");
                if ($resultado) {
                    array_push($arrayDeArchivos, $p2->archivo);
                } else {
                    array_push($arrayImagenes, $p2->archivo);
                }
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

    public function entregaAlumnoReHacer(Request $request)
    {

        $peticionSQL = DB::table('re_hacer_tareas')
            ->select('re_hacer_tareas.idTareas AS idTareas', 're_hacer_tareas.idAlumnos AS idAlumnos', 're_hacer_tareas.created_at AS fecha', 're_hacer_tareas.calificacion AS calificacion', 'usuarios.nombre AS nombreUsuario')
            ->join('usuarios', 're_hacer_tareas.idAlumnos', '=', 'usuarios.username')
            ->where('re_hacer_tareas.idTareas', $request->idTareas)
            ->where('re_hacer_tareas.idAlumnos', $request->idAlumnos)
            ->get();

        $dataResponse = array();

        foreach ($peticionSQL as $p) {

            $peticionSQLFiltrada = DB::table('archivos_re_hacer_tarea')
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
              /*   "mensaje" => $p->mensaje, */
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

    public function verificar_correcion(Request $request){ 
    if ($request->re_hacer){
        return self::corregirEntrega($request);

    }
    else{

        return self::corregirEntregaReHacer($request);

    }
    }

    public function corregirEntrega(Request $request)
    {

        $existe = AlumnoEntrega::where('idTareas', $request->idTareas)->where('idAlumnos', $request->idAlumnos)->first();
        try {
            if ($existe) {

                DB::update('UPDATE alumno_entrega_tareas SET calificacion="' . $request->calificacion . '" , mensaje_profesor="' . $request->mensaje . '" , re_hacer="' . $request->re_hacer . '" WHERE idTareas="' . $request->idTareas . '" AND idAlumnos="' . $request->idAlumnos . '";');
                return response()->json(['status' => 'Success'], 200);
            }
            return response()->json(['status' => 'Bad Request'], 400);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    
    public function corregirEntregaReHacer(Request $request)
    {

        $existe = AlumnoReHacerTarea::where('idTareas', $request->idTareas)->where('idAlumnos', $request->idAlumnos)->first();
        try {
            if ($existe) {

                DB::update('UPDATE re_hacer_tareas SET calificacion="' . $request->calificacion . '" , mensaje_profesor="' . $request->mensaje . '" WHERE idTareas="' . $request->idTareas . '" AND idAlumnos="' . $request->idAlumnos . '";');
                return response()->json(['status' => 'Success'], 200);
            }
            return response()->json(['status' => 'Bad Request'], 400);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }


}
