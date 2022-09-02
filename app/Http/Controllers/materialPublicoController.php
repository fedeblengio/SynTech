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
        $peticionSQL = DB::table('material_publicos')
            ->select('material_publicos.id', 'material_publicos.imgEncabezado', 'material_publicos.titulo AS titulo', 'material_publicos.mensaje AS mensaje', 'material_publicos.idUsuario', 'material_publicos.imgEncabezado', 'material_publicos.created_at AS fecha', 'usuarios.nombre AS nombreAutor')
            ->join('usuarios', 'usuarios.id', '=', 'material_publicos.idUsuario')
            ->orderBy('id', 'desc')
            ->take($request->limit)
            ->get();

        $dataResponse = array();


        foreach ($peticionSQL as $p) {
            $peticionSQLFiltrada = DB::table('archivos_material_publico')
                ->select('nombreArchivo AS archivo')
                ->where('idMaterialPublico', $p->id)
                ->distinct()
                ->get();

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
        
        $materialPublico = new material_publico;
        $materialPublico->idUsuario = $request->idUsuario;
        $materialPublico->titulo = $request->titulo;
        $materialPublico->mensaje = $request->mensaje;
        $materialPublico->imgEncabezado = "encabezadoPredeterminado.jpg";
        $materialPublico->save();

        $idDatos = DB::table('material_publicos')->orderBy('created_at', 'desc')->limit(1)->get('id');

        if ($request->archivos) {

            for ($i = 0; $i < count($request->nombresArchivo); $i++) {
                $nombreArchivo = random_int(0, 1000000) . "_" . $request->nombresArchivo[$i];
                Storage::disk('ftp')->put($nombreArchivo, fopen($request->archivos[$i], 'r+'));
                $archivosForo = new archivos_material_publico;
                $archivosForo->idMaterialPublico = $idDatos[0]->id;
                $archivosForo->nombreArchivo = $nombreArchivo;
                $archivosForo->save();
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
            Storage::disk('ftp')->delete($p->nombreArchivo);
            $arhivosId = archivos_material_publico::where('id', $p->id)->first();
            $arhivosId->delete();
        }
        try {
            $materialPublico->delete();
            RegistrosController::store("PUBLICACION PUBLICA", $request->header('token'), "DELETE", $request->idUsuario);
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }
}
