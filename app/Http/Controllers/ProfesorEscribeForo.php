<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use App\Models\datosForo;
use App\Models\Foro;
use App\Models\archivosForo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ProfesorForoGrupo;

class ProfesorEscribeForo extends Controller
{
    public function index(Request $request)
    {
        return response()->json(ProfesorForoGrupo::where('idMateria', $request->idMateria)->where('idGrupo', $request->idGrupo)->first());
    }

/* 
    public function show(Request $request)
    {
        $mostrarMensajes=datosForo::all()->where('idForo', $request->idForo);
        return response()->json($mostrarMensajes);
    } */

    public function store(Request $request)
    
    {

        try {
            $nombre="";
                if($request->hasFile("archivo")){
                    $file=$request->archivo;
                   
                    /* if($file->guessExtension()=="pdf" || $file->guessExtension()=="jpg" ){ */
                        $nombreArchivo = $request->nombre;      
                        Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivo, 'r+'));              
                    /* } */
                }
               
                return response()->json(['status' => 'Success'], 200);            
                
             }catch (\Throwable $th) {
                    return response()->json(['status' => 'Error'], 406);
                     }
  }


        
    public function show(Request $request){
            if ($request->ou == 'Profesor'){
                $peticionSQL=DB::table('profesor_estan_grupo_foro')
                ->select('datosForo.id AS id','datosForo.idForo AS idForo', 'datosForo.mensaje AS mensaje', 'datosForo.titulo AS titulo')
                ->join('datosForo', 'datosForo.idForo', '=', 'profesor_estan_grupo_foro.idForo')
                ->where('profesor_estan_grupo_foro.idProfesor', $request->idUsuario)
                ->distinct()
                ->get();
                $dataResponse=array();
                
                /*foreach($peticionSQLFiltrada as $p2){
                        if ($p2 == *.jpg){
                            $base64imagen = base64_encode(Storage::disk('ftp')->get($p2->archivo));
                            array_push($arrayDeImagenes,$base64imagen);
                        }
                        array_push($arrayDeArchivos,$p2->archivo);
                    }*/
                foreach ($peticionSQL as $p){
                    $peticionSQLFiltrada= DB::table('archivos_foro')
                    ->select('nombreArchivo AS archivo')
                    ->where('idDato', $p->id)
                    ->distinct()
                    ->get();
                    $arrayDeArchivos=array();
                    foreach($peticionSQLFiltrada as $p2){
                        array_push($arrayDeArchivos,$p2->archivo);
                    }

                    $datos = [
                        "id" => $p->id,
                        "idForo" => $p->idForo,
                        "mensaje" => $p->mensaje,
                        "titulo"=> $p->titulo,
                    ];
                    
                    $p = [
                        "data"=> $datos,
                        "archivos"=> $arrayDeArchivos,
                    ];
                    
                    array_push($dataResponse, $p);
                }
                
                return response()->json($dataResponse); 
               
                
            
             
            }else if($request->ou == 'Alumno'){
                $idGrupo=DB::table('alumnos_pertenecen_grupos')
                ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
                ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
                ->get();
                $p=DB::table('profesor_estan_grupo_foro')
                ->select('datosForo.id AS id','datosForo.idForo AS idForo', 'datosForo.mensaje AS mensaje', 'datosForo.titulo AS titulo')
                ->join('datosForo', 'datosForo.idForo', '=', 'profesor_estan_grupo_foro.idForo')
                ->where('profesor_estan_grupo_foro.idGrupo', $idGrupo[0]->idGrupo)
                ->get();

                $a=array();
                
                foreach ($p as $peti){
                    $otraPeti= DB::table('archivos_foro')
                    ->select('nombreArchivo AS archivo')
                    ->where('idDato', $peti->id)
                    ->distinct()
                    ->get();

                    $datos = [
                        "data"=> $peti,
                        "archivos" => $otraPeti,
                    ];
                    array_push($a, $datos);
                }
                
                return response()->json($a); 
                
               
            }

          
    }

    public function cargarArchivos(Request $request){
        $archivos=archivosForo::all()->where('idDato', $request->idDato);
       /*  $files=array();
        foreach($archivos as $a){
            $files+= Storage::disk('ftp')->get($a->nombreArchivo);
        } */
        
        return response()->json($archivos);
        /* $file->guessExtension()=="pdf" || $file->guessExtension()=="jpg" */
    }
        



    public function subirBD(Request $request){
        $datosForo = new datosForo;
        $datosForo->idForo = $request->idForo;
        $datosForo->idUsuario = $request->idUsuario;
        $datosForo->titulo = $request->titulo;
        $datosForo->mensaje = $request->mensaje;
        $datosForo->save();

        $idDatos = DB::table('datosForo')->orderBy('created_at', 'desc')->limit(1)->get('id');
        /* $nombreArchivosArray= []; */
        $nombreArchivosArray = explode(',', $request->nombre_archivos);

         if($request->nombre_archivos){
                foreach ($nombreArchivosArray as $nombres){   

                $archivosForo = new archivosForo;
                $archivosForo->idDato = $idDatos[0]->id;
                $archivosForo->idForo = $request->idForo;
                $archivosForo->nombreArchivo = $nombres;
                $archivosForo->save();
                }
        }
        return response()->json(['status' => 'Success'], 200);
    }

    public function traerArchivo(Request $request)
    {
        return Storage::disk('ftp')->get($request->archivo);
    }


    public function update(Request $request)
    {
        $modificarDatosForo = datosForo::where('id', $request->idDatos)->first();
       
        try{
            $modificarDatosForo->titulo = $request->titulo;
            $modificarDatosForo->mensaje = $request->mensaje;
            $modificarDatosForo->save();
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    public function destroy(Request $request)
    {
        $eliminarDatosForo = datosForo::where('id', $request->idDatos)->first();
        try {
            $eliminarDatosForo->delete();
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }
}
