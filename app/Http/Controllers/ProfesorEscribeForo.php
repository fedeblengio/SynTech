<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use App\Models\datosForo;
use App\Models\Foro;
use Illuminate\Http\Request;
use App\Models\ProfesorForoGrupo;

class ProfesorEscribeForo extends Controller
{
    public function index(Request $request)
    {
        return response()->json(ProfesorForoGrupo::where('idMateria', $request->idMateria)->where('idGrupo', $request->idGrupo)->first());
    }

    public function show(Request $request)
    {
        $mostrarMensajes=datosForo::all()->where('idForo', $request->idForo);
        return response()->json($mostrarMensajes);
    }

    public function store(Request $request)
    {
        /* try { */
                $datosForo = new datosForo;
                $datosForo->idForo = $request->idForo;
                $datosForo->idUsuario = $request->idUsuario;
                $datosForo->titulo = $request->titulo;
                $datosForo->mensaje = $request->mensaje;
                

                if($request->hasFile("archivo")){
                    $file=$request->archivo;
                    
                    $nombre = time()."_".$file->getClientOriginalName();

                    /* $carpeta = Storage::makeDirectory(time()."/pdf");
                    $carpeta2 = Storage::makeDirectory(time()."/docx"); */
        
                   /*  $ruta = public_path($carpeta,$nombre);
                    $ruta2 = public_path($carpeta2,$nombre); */
                     
        
                    if($file->guessExtension()=="pdf"){
                        /* copy($file, $ruta); */
                        Storage::disk('pdf')->put($nombre, fopen($request->archivo, 'r+'));
                       /*  $request->archivo->store(time());
                        $datosForo->datos = time();
                        $datosForo->save(); */
                        return response()->json(['status' => 'Success'], 200);
                        if($file->guessExtension()=="docx"){
                          
                           /*  copy($file, $ruta2); */
                           $request->archivo->store(time());
                            $datosForo->datos = time();
                            $datosForo->save();
                        return response()->json(['status' => 'Success'], 200);
                        }
                    }else{
                        return response()->json(['status' => 'Error'], 406);
                    } 
                //* * } */

                /* $datosForo->datos = $request->archivo; */
                
               /*  return response()->json(['status' => 'Success'], 200); */
      /*   } catch (\Throwable $th) {
                return response()->json($th);
             }    */

    }
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
        $eliminarDatosForo = datosForo::where('idDatos', $request->idDatos)->first();
        try {
            $eliminarDatosForo->delete();
            return response()->json(['status' => 'Success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'Bad Request'], 400);
        }
    }







}
