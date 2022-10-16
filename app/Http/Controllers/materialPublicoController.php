<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\material_publico;
use App\Models\archivos_material_publico;
use App\Http\Controllers\RegistrosController;
use Carbon\Carbon;

class materialPublicoController extends Controller
{
    public function index(Request $request)
    {
        if($request->idUsuario){
            $peticionSQL = $this->getMaterialPublicoForUsuario($request);
        }else{
            $peticionSQL = $this->getMaterialPublico($request);
        }

        $dataResponse = array();


        foreach ($peticionSQL as $p) {
            $peticionSQLFiltrada = $this->getArchivosMaterialPublico($p);

            $p->imgEncabezado = base64_encode(Storage::disk('ftp')->get($p->imgEncabezado));

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

        $this->agregarMaterialPublico($request);

        $idDatos = DB::table('material_publicos')->orderBy('created_at', 'desc')->limit(1)->get('id');

        if ($request->archivos) {

            for ($i = 0; $i < count($request->nombresArchivo); $i++) {
                $this->subirArchivoMaterialPublico($request, $i, $idDatos[0]);
            }
        }

        RegistrosController::store("PUBLICACION PUBLICA", $request->header('token'), "CREATE", $request->idUsuario);
        return response()->json(['status' => 'Success'], 200);
    }

    public function destroy(Request $request)
    {

        $materialPublico = material_publico::where('id', $request->id)->first();
        $arhivosMaterialPublico = archivos_material_publico::where('idMaterialPublico', $request->id)->get();
        foreach ($arhivosMaterialPublico as $p) {
            $this->deleteArchivosMaterialPublico($p);
        }
        try {
            $materialPublico->delete();
            RegistrosController::store("PUBLICACION PUBLICA", $request->header('token'), "DELETE", $request->idUsuario);
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getMaterialPublicoForUsuario(Request $request): \Illuminate\Support\Collection
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

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getMaterialPublico(Request $request): \Illuminate\Support\Collection
    {
        $peticionSQL = DB::table('material_publicos')
            ->select('material_publicos.id', 'material_publicos.imgEncabezado', 'material_publicos.titulo AS titulo', 'material_publicos.mensaje AS mensaje', 'material_publicos.idUsuario', 'material_publicos.imgEncabezado', 'material_publicos.created_at AS fecha', 'usuarios.nombre AS nombreAutor')
            ->join('usuarios', 'usuarios.id', '=', 'material_publicos.idUsuario')
            ->orderBy('id', 'desc')
            ->take($request->limit)
            ->get();
        return $peticionSQL;
    }

    /**
     * @param $p
     * @return \Illuminate\Support\Collection
     */
    public function getArchivosMaterialPublico($p): \Illuminate\Support\Collection
    {
        $peticionSQLFiltrada = DB::table('archivos_material_publico')
            ->select('nombreArchivo AS archivo')
            ->where('idMaterialPublico', $p->id)
            ->distinct()
            ->get();
        return $peticionSQLFiltrada;
    }

    /**
     * @param Request $request
     * @return void
     */
    public function agregarMaterialPublico(Request $request): void
    {
        $materialPublico = new material_publico;
        $materialPublico->idUsuario = $request->idUsuario;
        $materialPublico->titulo = $request->titulo;
        $materialPublico->mensaje = $request->mensaje;
        $materialPublico->imgEncabezado = "encabezadoPredeterminado.jpg";
        $materialPublico->save();
    }

    /**
     * @param Request $request
     * @param int $i
     * @param $idDatos
     * @return void
     * @throws \Exception
     */
    public function subirArchivoMaterialPublico(Request $request, int $i, $idDatos): void
    {
        $nombreArchivo = random_int(0, 1000000) . "_" . $request->nombresArchivo[$i];
        Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivos[$i], 'r+'));
        $archivosForo = new archivos_material_publico;
        $archivosForo->idMaterialPublico = $idDatos->id;
        $archivosForo->nombreArchivo = $nombreArchivo;
        $archivosForo->save();
    }

    /**
     * @param $p
     * @return void
     */
    public function deleteArchivosMaterialPublico($p): void
    {
        Storage::disk('ftp')->delete($p->nombreArchivo);
        $arhivosId = archivos_material_publico::where('id', $p->id)->first();
        $arhivosId->delete();
    }
}
