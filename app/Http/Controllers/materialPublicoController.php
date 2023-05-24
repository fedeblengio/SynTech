<?php

namespace App\Http\Controllers;

use App\Models\usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\MaterialPublico;
use App\Models\archivos_material_publico;
use App\Http\Controllers\RegistrosController;
use Carbon\Carbon;

class materialPublicoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->idUsuario) {
            $peticionSQL = $this->getMaterialPublicoForUsuario($request);
        } else {
            $peticionSQL = $this->getMaterialPublico($request);
        }

        $dataResponse = array();


        foreach ($peticionSQL as $p) {
            $peticionSQLFiltrada = $this->getArchivosMaterialPublico($p);
            if (!App::environment(['testing'])) {
                $p->imgEncabezado = base64_encode(Storage::disk('ftp')->get($p->imgEncabezado));
            }
            $arrayArchivos = array();


            foreach ($peticionSQLFiltrada as $p2) {
                array_push($arrayArchivos, $p2->archivo);
            }


            $datos = [
                "id" => $p->id,
                "imagenEncabezado" => $p->imgEncabezado,
                "mensaje" => $p->mensaje,
                "titulo" => $p->titulo,
                "idUsuario" => $p->idUsuario,
                "nombreAutor" => $p->nombreAutor,
                "fecha" => $p->fecha
            ];

            $p = [
                "data" => $datos,
                "archivos" => $arrayArchivos,
            ];

            array_push($dataResponse, $p);
        }
        return response()->json($dataResponse);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'string|required',
            'mensaje' => 'string',
            'idUsuario' => 'required'
        ]);
        $usuario = usuarios::findOrFail($request->idUsuario);
        if ($usuario->ou != "Profesor") {
            return response()->json(['status' => 'Unauthorized'], 401);
        }

        $idDatos = $this->agregarMaterialPublico($request);


        if ($request->archivos) {

            for ($i = 0; $i < count($request->nombresArchivo); $i++) {
                $this->subirArchivoMaterialPublico($request, $i, $idDatos);
            }
        }

        RegistrosController::store("PUBLICACION PUBLICA", $request->header('token'), "CREATE", $request->idUsuario);
        return response()->json(['status' => 'Success'], 200);
    }

    public function destroy($id, Request $request)
    {

        $materialPublico = MaterialPublico::findOrFail($id);
        $arhivosMaterialPublico = archivos_material_publico::where('idMaterialPublico', $materialPublico->id)->get();
        try {
            foreach ($arhivosMaterialPublico as $p) {
                $this->deleteArchivosMaterialPublico($p);
            }
            $materialPublico->delete();
            RegistrosController::store("PUBLICACION PUBLICA", $request->header('token'), "DELETE", $request->idUsuario);
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }


    public function getMaterialPublicoForUsuario(Request $request)
    {
        $peticionSQL = DB::table('material_publicos')
            ->select('material_publicos.id', 'material_publicos.imgEncabezado', 'material_publicos.titulo AS titulo', 'material_publicos.mensaje AS mensaje', 'material_publicos.idUsuario', 'material_publicos.imgEncabezado', 'material_publicos.created_at AS fecha', 'usuarios.nombre AS nombreAutor')
            ->join('usuarios', 'usuarios.id', '=', 'material_publicos.idUsuario')
            ->where('material_publicos.idUsuario', $request->idUsuario)
            ->orderBy('id', 'desc')
            ->take($request->limit)
            ->get();
        return $peticionSQL;
    }

    public function getMaterialPublico(Request $request)
    {
        $peticionSQL = DB::table('material_publicos')
            ->select('material_publicos.id', 'material_publicos.imgEncabezado', 'material_publicos.titulo AS titulo', 'material_publicos.mensaje AS mensaje', 'material_publicos.idUsuario', 'material_publicos.imgEncabezado', 'material_publicos.created_at AS fecha', 'usuarios.nombre AS nombreAutor')
            ->join('usuarios', 'usuarios.id', '=', 'material_publicos.idUsuario')
            ->orderBy('id', 'desc')
            ->take($request->limit)
            ->get();
        return $peticionSQL;
    }


    public function getArchivosMaterialPublico($p)
    {
        $peticionSQLFiltrada = DB::table('archivos_material_publico')
            ->select('nombreArchivo AS archivo')
            ->where('idMaterialPublico', $p->id)
            ->distinct()
            ->get();
        return $peticionSQLFiltrada;
    }


    public function agregarMaterialPublico(Request $request)
    {
        $materialPublico = new MaterialPublico;
        $materialPublico->idUsuario = $request->idUsuario;
        $materialPublico->titulo = $request->titulo;
        $materialPublico->mensaje = $request->mensaje;
        $materialPublico->imgEncabezado = "encabezadoPredeterminado.jpg";
        $materialPublico->save();

        return $materialPublico;
    }


    public function subirArchivoMaterialPublico(Request $request, int $i, $idDatos)
    {
        $nombreArchivo = random_int(0, 1000000) . "_" . $request->nombresArchivo[$i];
        if (!App::environment(['testing'])) {
            Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivos[$i], 'r+'));
        }
        $archivosForo = new archivos_material_publico;
        $archivosForo->idMaterialPublico = $idDatos->id;
        $archivosForo->nombreArchivo = $nombreArchivo;
        $archivosForo->save();
    }

    public function deleteArchivosMaterialPublico($p)
    {
        if (!App::environment(['testing'])) {
            Storage::disk('ftp')->delete($p->nombreArchivo);
        }
        $p->delete();
    }
}