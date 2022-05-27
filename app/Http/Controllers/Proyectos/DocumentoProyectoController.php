<?php

namespace App\Http\Controllers\Proyectos;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Proyectos\DocumentoProyecto;

class DocumentoProyectoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $proyecto_id)
    {
        try{
            $datos = $request->all();
            $datos['proyecto_id'] = $proyecto_id;
            if(!$request->ligera){
                $validator = Validator::make($datos, [
                    'limite' => 'integer|between:1,500',
                    'proyecto_id' => 'integer|required|exists:proyectos,id'
                ]);

                if($validator->fails()) {
                    return response(
                        get_response_body(format_messages_validator($validator))
                        , Response::HTTP_BAD_REQUEST
                    );
                }
            }
            if(isset($datos['ordenar_por'])){
                $datos['ordenar_por'] = format_order_by_attributes($datos);
            }
            $documentoProyecto = DocumentoProyecto::obtenerColeccion($datos);
            return response($documentoProyecto, Response::HTTP_OK);
        }catch(Exception $e){
            return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proyectos\DocumentoProyecto  $documentoProyecto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Se abre la transacción
        try{
            $datos = $request->all();
            $datos['id'] = $id;
            $validator = Validator::make($datos, [
                'id' => 'integer|required|exists:documentos_proyecto,id',
                'proyecto_id' => 'integer|required|exists:proyectos,id',
                'tipo_documento_proyecto_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_documentos_proyecto','id')->where(function ($query) {
                        $query->where('tiDoPrEstado', '1');
                    }),
                ],
                'docProAplica' => 'boolean|required',
                'docProEntregado' => 'boolean|required'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $documentoProyecto = DocumentoProyecto::modificar($datos);
            if($documentoProyecto){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El documento del proyecto ha sido modificado.", 1], $documentoProyecto),
                    Response::HTTP_OK
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar modificar el documento del proyecto."]), Response::HTTP_CONFLICT);;
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(get_response_body([$e->getMessage()]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
