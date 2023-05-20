<?php

namespace App\Http\Controllers;

use App\Models\usuarios;
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
    public function getForoId($idGrupo, $idMateria)
    {
        return response()->json(ProfesorForoGrupo::where('idMateria', $idMateria)
            ->where('idGrupo', $idGrupo)->first());
    }

    public function traerGrupos(Request $request, $id)
    {
        $usuario = usuarios::findOrFail($id);
        if ($usuario->ou == 'Profesor') {
            $request["idProfesor"] = $usuario->id;
            return ProfesorGrupo::listarProfesorGrupo($request);
        } else if ($usuario->ou == 'Alumno') {
            return self::traerGruposAlumnos($usuario->id);
        }
    }


    public function traerGruposAlumnos($id)
    {
        $gruposAlumno = alumnoGrupo::select('idGrupo')->where('idAlumnos', $id)->get();
        return response()->json($gruposAlumno);
    }


    public function traerArchivo($archivo)
    {
        if (empty($archivo)) {
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }
        try {
            return Storage::disk('ftp')->get($archivo);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }
    }

    public function getAllPublicaciones($idGrupo, $idUsuario,$limit)
    {
        $usuario = usuarios::findOrFail($idUsuario);
        $data = [
            'idUsuario' => $idUsuario,
            'idGrupo' => $idGrupo,
            'limit' => $limit
        ];

        if ($usuario->ou == 'Profesor') {
            return self::traerPublicacionesProfesor($data);
        } else if ($usuario->ou == 'Alumno') {
            return self::traerPublicacionesAlumno($data);
        }
    }

    public function getAllPublicacionesMateria($idGrupo, $idUsuario, $idMateria,$limit)
    {
        $usuario = usuarios::findOrFail($idUsuario);
        $data = [
            'idUsuario' => $idUsuario,
            'idGrupo' => $idGrupo,
            'idMateria' => $idMateria,
            'limit' => $limit,
        ];
        if ($usuario->ou == 'Profesor') {
            return self::traerPublicacionesProfesorMateria($data);
        } else if ($usuario->ou == 'Alumno') {
            return self::traerPublicacionesAlumnoMateria($data);
        }

        return response()->json([]);
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

    public function store(Request $request)
    {
        $request->validate(
            [
                'idGrupo' => 'required',
                'idMateria' => 'required',
                'idUsuario' => 'required',
                'mensaje' => 'string',
                'archivos' => 'array | nullable',
                'nombresArchivo' => 'array | nullable'
            ]
        );


        $datoForo = $this->agregarDatosForo($request);
        if ($request->archivos) {
            for ($i = 0; $i < count($request->nombresArchivo); $i++) {
                $this->subirArchivoForo($request, $i, $datoForo->id);
            }
        }

        RegistrosController::store("PUBLICACION FORO", $request->header('token'), "CREATE", "");
        return response()->json(['status' => 'Success'], 200);
    }

    public function agregarDatosForo(Request $request)
    {
        $foro = ProfesorForoGrupo::where('idGrupo', $request->idGrupo)->where('idMateria', $request->idMateria)->first();
        $datosForo = new datosForo;
        $datosForo->idForo = $foro->idForo;
        $datosForo->idUsuario = $request->idUsuario;
        $datosForo->mensaje = $request->mensaje;
        $datosForo->save();

        return $datosForo;
    }


    public function destroy(Request $request, $id)
    {
        $postForo = datosForo::findOrFail($id);
        $arhivosForo = archivosForo::where('idDato', $id)->get();
        foreach ($arhivosForo as $p) {
            Storage::disk('ftp')->delete($p->nombreArchivo);
            $arhivosId = archivosForo::where('id', $p->id)->first();
            $arhivosId->delete();
        }
        try {
            $postForo->delete();
            RegistrosController::store("PUBLICACION FORO", $request->header('token'), "DELETE", "");
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }


    public function getPublicacionesForoForProfesor($request)
    {
        $peticionSQL = DB::table('profesor_estan_grupo_foro')
            ->select('datosForo.id AS id', 'datosForo.idForo AS idForo', 'profesor_estan_grupo_foro.idGrupo', 'materias.nombre AS materia', 'datosForo.idUsuario AS idUsuario', 'usuarios.nombre AS nombreAutor', 'datosForo.mensaje AS mensaje', 'datosForo.created_at AS fecha', 'datosForo.idUsuario as postAuthor')
            ->join('datosForo', 'datosForo.idForo', '=', 'profesor_estan_grupo_foro.idForo')
            ->join('grupos_tienen_profesor AS A', 'A.idMateria', '=', 'profesor_estan_grupo_foro.idMateria')
            ->join('grupos_tienen_profesor', 'grupos_tienen_profesor.idGrupo', '=', 'profesor_estan_grupo_foro.idGrupo')
            ->join('usuarios', 'usuarios.id', '=', 'datosForo.idUsuario')
            ->join('materias', 'materias.id', '=', 'profesor_estan_grupo_foro.idMateria')
            ->where('profesor_estan_grupo_foro.idProfesor', $request['idUsuario'])
            ->where('profesor_estan_grupo_foro.idGrupo', $request['idGrupo'])

            ->orderBy('id', 'desc')
            ->take($request['limit'])
            ->distinct()
            ->get();
        return $peticionSQL;
    }


    public function getArchivosForo($p)
    {
        $peticionSQLFiltrada = DB::table('archivos_foro')
            ->select('nombreArchivo AS archivo')
            ->where('idDato', $p->id)
            ->distinct()
            ->get();
        return $peticionSQLFiltrada;
    }


    public function getImagenPefil($postAuthor)
    {
        $imgPerfil = DB::table('usuarios')
            ->select('imagen_perfil')
            ->where('id', $postAuthor)
            ->get();
        return $imgPerfil;
    }


    public function getPublicacionesForoMateriaForProfesor($request)
    {
        $peticionSQL = DB::table('profesor_estan_grupo_foro')
            ->select('datosForo.id AS id', 'datosForo.idForo AS idForo', 'profesor_estan_grupo_foro.idGrupo', 'materias.nombre as materia', 'datosForo.idUsuario AS idUsuario', 'usuarios.nombre AS nombreAutor', 'datosForo.mensaje AS mensaje', 'datosForo.created_at AS fecha', 'datosForo.idUsuario as postAuthor')
            ->join('datosForo', 'datosForo.idForo', '=', 'profesor_estan_grupo_foro.idForo')
            ->join('usuarios', 'usuarios.id', '=', 'datosForo.idUsuario')
            ->join('materias', 'materias.id', '=', 'profesor_estan_grupo_foro.idMateria')
            ->where('profesor_estan_grupo_foro.idProfesor', $request['idUsuario'])
            ->where('profesor_estan_grupo_foro.idMateria', $request['idMateria'])
            ->where('profesor_estan_grupo_foro.idGrupo', $request['idGrupo'])
            ->orderBy('id', 'desc')
            ->take($request['limit'])
            ->get();
        return $peticionSQL;
    }


    public function getIdGrupoAlumno($request)
    {
        $idGrupo = DB::table('alumnos_pertenecen_grupos')
            ->select('alumnos_pertenecen_grupos.idGrupo AS idGrupo')
            ->where('alumnos_pertenecen_grupos.idAlumnos', $request->idUsuario)
            ->get();
        return $idGrupo;
    }


    public function getPublicacionesForoForGrupo($request)
    {
        $peticionSQL = DB::table('profesor_estan_grupo_foro')
            ->select('datosForo.id AS id', 'datosForo.idForo AS idForo', 'profesor_estan_grupo_foro.idGrupo', 'materias.nombre as materia', 'datosForo.idUsuario AS idUsuario', 'usuarios.nombre AS nombreAutor', 'datosForo.mensaje AS mensaje', 'datosForo.created_at AS fecha', 'datosForo.idUsuario as postAuthor')
            ->join('materias', 'materias.id', '=', 'profesor_estan_grupo_foro.idMateria')
            ->join('datosForo', 'datosForo.idForo', '=', 'profesor_estan_grupo_foro.idForo')
            ->join('usuarios', 'usuarios.id', '=', 'datosForo.idUsuario')
            ->where('profesor_estan_grupo_foro.idGrupo', $request['idGrupo'])
            ->orderBy('id', 'desc')
            ->take($request['limit'])
            ->distinct()
            ->get();
        return $peticionSQL;
    }


    public function getPublicacionesForoMateriaForGrupo($request)
    {
        $peticionSQL = DB::table('profesor_estan_grupo_foro')
            ->select('datosForo.id AS id', 'datosForo.idForo AS idForo', 'profesor_estan_grupo_foro.idGrupo', 'materias.nombre as materia', 'datosForo.idUsuario AS idUsuario', 'usuarios.nombre AS nombreAutor', 'datosForo.mensaje AS mensaje', 'datosForo.created_at AS fecha', 'datosForo.idUsuario as postAuthor')
            ->join('materias', 'materias.id', '=', 'profesor_estan_grupo_foro.idMateria')
            ->join('datosForo', 'datosForo.idForo', '=', 'profesor_estan_grupo_foro.idForo')
            ->join('usuarios', 'usuarios.id', '=', 'datosForo.idUsuario')
            ->where('profesor_estan_grupo_foro.idGrupo', $request['idGrupo'])
            ->where('profesor_estan_grupo_foro.idMateria', $request['idMateria'])
            ->orderBy('id', 'desc')
            ->take($request['limit'])
            ->get();
        return $peticionSQL;
    }





    public function subirArchivoForo(Request $request, int $i, $idDatos)
    {
        $foro = ProfesorForoGrupo::where('idGrupo', $request->idGrupo)->where('idMateria', $request->idMateria)->first();
        $nombreArchivo = random_int(0, 1000000) . "_" . $request->nombresArchivo[$i];
        Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivos[$i], 'r+'));
        $archivosForo = new archivosForo;
        $archivosForo->idDato = $idDatos;
        $archivosForo->idForo = $foro->idForo;
        $archivosForo->nombreArchivo = $nombreArchivo;
        $archivosForo->save();
    }
}