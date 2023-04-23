<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\datosForo;
use App\Models\Foro;
use App\Models\archivosForo;
use App\Models\alumnoGrupo;
use App\Http\Controllers\ProfesorGrupo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\RegistrosController;
use App\Models\ProfesorForoGrupo;

class ProfesorEscribeForo extends Controller
{
    public function index(Request $request)
    {
        return response()->json(ProfesorForoGrupo::where('idMateria', $request->idMateria)
            ->where('idGrupo', $request->idGrupo)->first());
    }

    public function traerGrupos(Request $request){
        if($request->ou == 'Profesor'){
            $request["idProfesor"] = $request->idUsuario;
            return ProfesorGrupo::listarProfesorGrupo($request);
        }else if ($request->ou == 'Alumno'){
            return self::traerGruposAlumnos($request);
        }
    }

 
    public function traerGruposAlumnos($request){
        $gruposAlumno=alumnoGrupo::select('idGrupo')->where('idAlumnos', $request->idUsuario)->get();
        return response()->json($gruposAlumno);
    }


    public function traerArchivo($archivo)
    {
        if(empty($archivo)){
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }
        try{
            return Storage::disk('ftp')->get($archivo);
        }catch(\Exception $e){
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }
    }

    public function show(Request $request)
    {
        if ($request->idMateria) {
            if ($request->ou == 'Profesor') {
                return  self::traerPublicacionesProfesorMateria($request);
            } else if ($request->ou == 'Alumno') {
                return self::traerPublicacionesAlumnoMateria($request);
            }
        } else {
            if ($request->ou == 'Profesor') {
                return  self::traerPublicacionesProfesor($request);
            } else if ($request->ou == 'Alumno') {
                return self::traerPublicacionesAlumno($request);
            }
        }
    }




    public function traerPublicacionesProfesor($request)
    {
        $peticionSQL = $this->getPublicacionesForoForProfesor($request);


        $dataResponse = array();


        foreach ($peticionSQL as $p) {
            $peticionSQLFiltrada = $this->getArchivosForo($p);

            $arrayArchivos = array();
            $arrayImagenes = array();
            $postAuthor = $p->postAuthor;
            $imgPerfil = $this->getImagenPefil($postAuthor);

            $img = base64_encode(Storage::disk('ftp')->get($imgPerfil[0]->imagen_perfil));


            foreach ($peticionSQLFiltrada as $p2) {

                $resultado = strpos($p2->archivo, ".pdf");
                if ($resultado) {
                    array_push($arrayArchivos, $p2->archivo);
                } else {
                    array_push($arrayImagenes, base64_encode(Storage::disk('ftp')->get($p2->archivo)));
                }
            }

            $datos = [
                "id" => $p->id,
                "profile_picture" => $img,
                "idForo" => $p->idForo,
                "mensaje" => $p->mensaje,
                "idUsuario" => $p->idUsuario,
                "nombreAutor" => $p->nombreAutor,
                "idGrupo" => $p->idGrupo,
                "materia" => $p->materia,
                "fecha" => $p->fecha
            ];

            $p = [
                "data" => $datos,
                "archivos" => $arrayArchivos,
                "imagenes" => $arrayImagenes,
            ];

            array_push($dataResponse, $p);
        }
        return response()->json($dataResponse);
    }

    public function traerPublicacionesProfesorMateria($request)
    {
        $peticionSQL = $this->getPublicacionesForoMateriaForProfesor($request);

        $dataResponse = array();


        foreach ($peticionSQL as $p) {
            $peticionSQLFiltrada = $this->getArchivosForo($p);

            $arrayArchivos = array();
            $arrayImagenes = array();
            $postAuthor = $p->postAuthor;
            $imgPerfil = $this->getImagenPefil($postAuthor);

            $img = base64_encode(Storage::disk('ftp')->get($imgPerfil[0]->imagen_perfil));


            foreach ($peticionSQLFiltrada as $p2) {

                $resultado = strpos($p2->archivo, ".pdf");
                if ($resultado) {
                    array_push($arrayArchivos, $p2->archivo);
                } else {
                    array_push($arrayImagenes, base64_encode(Storage::disk('ftp')->get($p2->archivo)));
                }
            }

            $datos = [
                "id" => $p->id,
                "profile_picture" => $img,
                "idForo" => $p->idForo,
                "mensaje" => $p->mensaje,
                "idUsuario" => $p->idUsuario,
                "nombreAutor" => $p->nombreAutor,
                "idGrupo" => $p->idGrupo,
                "materia" => $p->materia,
                "fecha" => $p->fecha
            ];

            $p = [
                "data" => $datos,
                "archivos" => $arrayArchivos,
                "imagenes" => $arrayImagenes,
            ];

            array_push($dataResponse, $p);
        }
        return response()->json($dataResponse);
    }



    public function traerPublicacionesAlumno($request)
    {

        $peticionSQL = $this->getPublicacionesForoForGrupo($request);

        $dataResponse = array();

        foreach ($peticionSQL as $p) {

            $peticionSQLFiltrada = $this->getArchivosForo($p);

            $arrayDeArchivos = array();
            $arrayImagenes = array();
            $postAuthor = $p->postAuthor;

            $imgPerfil = $this->getImagenPefil($postAuthor);

            $img = base64_encode(Storage::disk('ftp')->get($imgPerfil[0]->imagen_perfil));

            foreach ($peticionSQLFiltrada as $p2) {

                $resultado = strpos($p2->archivo, ".pdf");
                if ($resultado) {
                    array_push($arrayDeArchivos, $p2->archivo);
                } else {
                    array_push($arrayImagenes, base64_encode(Storage::disk('ftp')->get($p2->archivo)));
                }
            }

            $datos = [
                "id" => $p->id,
                "profile_picture" => $img,
                "idForo" => $p->idForo,
                "mensaje" => $p->mensaje,
                "idUsuario" => $p->idUsuario,
                "nombreAutor" => $p->nombreAutor,
                "idGrupo" => $p->idGrupo,
                "materia" => $p->materia,
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

    public function traerPublicacionesAlumnoMateria($request)
    {

        $peticionSQL = $this->getPublicacionesForoMateriaForGrupo($request);

        $dataResponse = array();

        foreach ($peticionSQL as $p) {

            $peticionSQLFiltrada = $this->getArchivosForo($p);

            $arrayDeArchivos = array();
            $arrayImagenes = array();
            $postAuthor = $p->postAuthor;

            $imgPerfil = $this->getImagenPefil($postAuthor);

            $img = base64_encode(Storage::disk('ftp')->get($imgPerfil[0]->imagen_perfil));

            foreach ($peticionSQLFiltrada as $p2) {

                $resultado = strpos($p2->archivo, ".pdf");
                if ($resultado) {
                    array_push($arrayDeArchivos, $p2->archivo);
                } else {
                    array_push($arrayImagenes, base64_encode(Storage::disk('ftp')->get($p2->archivo)));
                }
            }

            $datos = [
                "id" => $p->id,
                "profile_picture" => $img,
                "idForo" => $p->idForo,
                "mensaje" => $p->mensaje,
                "idUsuario" => $p->idUsuario,
                "nombreAutor" => $p->nombreAutor,
                "idGrupo" => $p->idGrupo,
                "materia" => $p->materia,
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

    public function store (Request $request){

        $this->agregarDatosForo($request);

        $idDatos = DB::table('datosForo')->orderBy('created_at', 'desc')->limit(1)->get('id');

        if ($request->archivos){
            for ($i=0; $i < count($request->nombresArchivo); $i++){
                $this->subirArchivoForo($request, $i, $idDatos[0]);
            }
    }

        RegistrosController::store("PUBLICACION FORO",$request->header('token'),"CREATE","");
        return response()->json(['status' => 'Success'], 200);
    }


    public function destroy(Request $request)
    {

        $postForo = datosForo::where('id', $request->id)->first();
        $arhivosForo = archivosForo::where('idDato', $request->id)->get();
        foreach ($arhivosForo as $p) {
            Storage::disk('ftp')->delete($p->nombreArchivo);
            $arhivosId = archivosForo::where('id', $p->id)->first();
            $arhivosId->delete();
        }
        try {
            $postForo->delete();
            RegistrosController::store("PUBLICACION FORO",$request->header('token'),"DELETE","");
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    /**
     * @param $request
     * @return \Illuminate\Support\Collection
     */
    public function getPublicacionesForoForProfesor($request): \Illuminate\Support\Collection
    {
        $peticionSQL = DB::table('profesor_estan_grupo_foro')
            ->select('datosForo.id AS id', 'datosForo.idForo AS idForo', 'profesor_estan_grupo_foro.idGrupo', 'materias.nombre AS materia', 'datosForo.idUsuario AS idUsuario', 'usuarios.nombre AS nombreAutor', 'datosForo.mensaje AS mensaje', 'datosForo.created_at AS fecha', 'datosForo.idUsuario as postAuthor')
            ->join('datosForo', 'datosForo.idForo', '=', 'profesor_estan_grupo_foro.idForo')
            ->join('grupos_tienen_profesor AS A', 'A.idMateria', '=', 'profesor_estan_grupo_foro.idMateria')
            ->join('grupos_tienen_profesor', 'grupos_tienen_profesor.idGrupo', '=', 'profesor_estan_grupo_foro.idGrupo')
            ->join('usuarios', 'usuarios.id', '=', 'datosForo.idUsuario')
            ->join('materias', 'materias.id', '=', 'profesor_estan_grupo_foro.idMateria')
            ->where('profesor_estan_grupo_foro.idProfesor', $request->idUsuario)
            ->where('profesor_estan_grupo_foro.idGrupo', $request->idGrupo)
            
            ->orderBy('id', 'desc')
            ->take($request->limit)
            ->distinct()
            ->get();
        return $peticionSQL;
    }

    /**
     * @param $p
     * @return \Illuminate\Support\Collection
     */
    public function getArchivosForo($p): \Illuminate\Support\Collection
    {
        $peticionSQLFiltrada = DB::table('archivos_foro')
            ->select('nombreArchivo AS archivo')
            ->where('idDato', $p->id)
            ->distinct()
            ->get();
        return $peticionSQLFiltrada;
    }

    /**
     * @param $postAuthor
     * @return \Illuminate\Support\Collection
     */
    public function getImagenPefil($postAuthor): \Illuminate\Support\Collection
    {
        $imgPerfil = DB::table('usuarios')
            ->select('imagen_perfil')
            ->where('id', $postAuthor)
            ->get();
        return $imgPerfil;
    }

    /**
     * @param $request
     * @return \Illuminate\Support\Collection
     */
    public function getPublicacionesForoMateriaForProfesor($request): \Illuminate\Support\Collection
    {
        $peticionSQL = DB::table('profesor_estan_grupo_foro')
            ->select('datosForo.id AS id', 'datosForo.idForo AS idForo', 'profesor_estan_grupo_foro.idGrupo', 'materias.nombre as materia', 'datosForo.idUsuario AS idUsuario', 'usuarios.nombre AS nombreAutor', 'datosForo.mensaje AS mensaje', 'datosForo.created_at AS fecha', 'datosForo.idUsuario as postAuthor')
            ->join('datosForo', 'datosForo.idForo', '=', 'profesor_estan_grupo_foro.idForo')
            ->join('usuarios', 'usuarios.id', '=', 'datosForo.idUsuario')
            ->join('materias', 'materias.id', '=', 'profesor_estan_grupo_foro.idMateria')
            ->where('profesor_estan_grupo_foro.idProfesor', $request->idUsuario)
            ->where('profesor_estan_grupo_foro.idMateria', $request->idMateria)
            ->where('profesor_estan_grupo_foro.idGrupo', $request->idGrupo)
            ->orderBy('id', 'desc')
            ->take($request->limit)
            ->get();
        return $peticionSQL;
    }

    /**
     * @param $request
     * @return \Illuminate\Support\Collection
     */
    public function getIdGrupoAlumno($request): \Illuminate\Support\Collection
    {
        $idGrupo = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
            ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
            ->get();
        return $idGrupo;
    }

    /**
     * @param $idGrupo
     * @param $request
     * @return \Illuminate\Support\Collection
     */
    public function getPublicacionesForoForGrupo($request): \Illuminate\Support\Collection
    {
        $peticionSQL = DB::table('profesor_estan_grupo_foro')
            ->select('datosForo.id AS id', 'datosForo.idForo AS idForo', 'profesor_estan_grupo_foro.idGrupo', 'materias.nombre as materia', 'datosForo.idUsuario AS idUsuario', 'usuarios.nombre AS nombreAutor', 'datosForo.mensaje AS mensaje', 'datosForo.created_at AS fecha', 'datosForo.idUsuario as postAuthor')
            ->join('materias', 'materias.id', '=', 'profesor_estan_grupo_foro.idMateria')
            ->join('datosForo', 'datosForo.idForo', '=', 'profesor_estan_grupo_foro.idForo')
            ->join('usuarios', 'usuarios.id', '=', 'datosForo.idUsuario')
            ->where('profesor_estan_grupo_foro.idGrupo', $request->idGrupo)
            ->orderBy('id', 'desc')
            ->take($request->limit)
            ->distinct()
            ->get();
        return $peticionSQL;
    }

    /**
     * @param $idGrupo
     * @param $request
     * @return \Illuminate\Support\Collection
     */
    public function getPublicacionesForoMateriaForGrupo($request): \Illuminate\Support\Collection
    {
        $peticionSQL = DB::table('profesor_estan_grupo_foro')
            ->select('datosForo.id AS id', 'datosForo.idForo AS idForo', 'profesor_estan_grupo_foro.idGrupo', 'materias.nombre as materia', 'datosForo.idUsuario AS idUsuario', 'usuarios.nombre AS nombreAutor', 'datosForo.mensaje AS mensaje', 'datosForo.created_at AS fecha', 'datosForo.idUsuario as postAuthor')
            ->join('materias', 'materias.id', '=', 'profesor_estan_grupo_foro.idMateria')
            ->join('datosForo', 'datosForo.idForo', '=', 'profesor_estan_grupo_foro.idForo')
            ->join('usuarios', 'usuarios.id', '=', 'datosForo.idUsuario')
            ->where('profesor_estan_grupo_foro.idGrupo', $request->idGrupo)
            ->where('profesor_estan_grupo_foro.idMateria', $request->idMateria)
            ->orderBy('id', 'desc')
            ->take($request->limit)
            ->get();
        return $peticionSQL;
    }

    /**
     * @param Request $request
     * @return void
     */
    public function agregarDatosForo(Request $request): void
    {
        $datosForo = new datosForo;
        $datosForo->idForo = $request->idForo;
        $datosForo->idUsuario = $request->idUsuario;
        $datosForo->mensaje = $request->mensaje;
        $datosForo->save();
    }

    /**
     * @param Request $request
     * @param int $i
     * @param $idDatos
     * @return void
     * @throws \Exception
     */
    public function subirArchivoForo(Request $request, int $i, $idDatos): void
    {
        $nombreArchivo = random_int(0, 1000000) . "_" . $request->nombresArchivo[$i];
        Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivos[$i], 'r+'));
        $archivosForo = new archivosForo;
        $archivosForo->idDato = $idDatos->id;
        $archivosForo->idForo = $request->idForo;
        $archivosForo->nombreArchivo = $nombreArchivo;
        $archivosForo->save();
    }
}
