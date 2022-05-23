<?php

namespace App\Http\Controllers\Proyectos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Proyectos\Orientacion;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrientacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        try{
            $datos = $request->all();
            if(!$request->ligera){
                $validator = Validator::make($datos, [
                    'limite' => 'integer|between:1,500'
                ]);

                if($validator->fails()) {
                    return response(
                        get_response_body(format_messages_validator($validator))
                        , Response::HTTP_BAD_REQUEST
                    );
                }
            }

            if($request->ligera){
                $orientacion = Orientacion::obtenerColeccionLigera($datos);
            }else{
                if(isset($datos['ordenar_por'])){
                    $datos['ordenar_por'] = format_order_by_attributes($datos);
                }
                $orientacion = Orientacion::obtenerColeccion($datos);
            }
            return response($orientacion, Response::HTTP_OK);
        }catch(Exception $e){
            return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction(); // Se abre la transacción
        try {
            $datos = $request->all();
            $validator = Validator::make($datos, [
                'tipo_orientacion_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_orientacion','id')->where(function ($query) {
                        $query->where('tipOriEstado', 1);
                    }),
                ],
                'orientador_id' => [
                    'integer',
                    'required',
                    Rule::exists('orientadores','id')->where(function ($query) {
                        $query->where('orientadoresEstado', 1);
                    }),
                ],
                'orientacionesFechaOrientacion' => 'date|required',
                'persona_id' => [
                    'integer',
                    'required',
                    Rule::exists('personas','id')->where(function ($query) {
                        $query->where('personasEstadoRegistro', 'AC');
                    }),
                ],
                'orientacionesSolicitud' => 'string|required|max:512',
                'orientacionesNota' => 'string|required|max:512',
                'orientacionesRespuesta' => 'string|required|max:512',
                'estado' => 'boolean|required'
            ],
            $messages = [
                'tipo_orientacion_id.exists'=>'El tipo de orientación seleccionado no existe o está en estado inactivo',
                'orientador_id.exists'=>'El orientador seleccionado no existe o está en estado inactivo',
                'persona_id.exists'=>'La persona seleccionada no existe o está en estado inactivo',
            ]);

            if ($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $orientacion = Orientacion::modificarOCrear($datos);
            
            if ($orientacion) {
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["La orientación ha sido creado.", 2], $orientacion),
                    Response::HTTP_CREATED
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar crear la orientación."]), Response::HTTP_CONFLICT);
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $datos['id'] = $id;
            $validator = Validator::make($datos, [
                'id' => 'integer|required|exists:orientaciones,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            return response(Orientacion::cargar($id), Response::HTTP_OK);
        }catch (Exception $e){
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Se abre la transacción
        try{
            $datos = $request->all();
            $datos['id'] = $id;
            $validator = Validator::make($datos, [
                'id' => 'integer|required|exists:orientaciones,id',
                'tipo_orientacion_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_orientacion','id')->where(function ($query) {
                        $query->where('tipOriEstado', 1);
                    }),
                ],
                'orientador_id' => [
                    'integer',
                    'required',
                    Rule::exists('orientadores','id')->where(function ($query) {
                        $query->where('orientadoresEstado', 1);
                    }),
                ],
                'orientacionesFechaOrientacion' => 'date|required',
                'persona_id' => [
                    'integer',
                    'required',
                    Rule::exists('personas','id')->where(function ($query) {
                        $query->where('personasEstadoRegistro', 'AC');
                    }),
                ],
                'orientacionesSolicitud' => 'string|required|max:512',
                'orientacionesNota' => 'string|required|max:512',
                'orientacionesRespuesta' => 'string|required|max:512',
                'estado' => 'boolean|required'
            ],
            $messages = [
                'tipo_orientacion_id.exists'=>'El tipo de orientación seleccionado no existe o está en estado inactivo',
                'orientador_id.exists'=>'El orientador seleccionado no existe o está en estado inactivo',
                'persona_id.exists'=>'La persona seleccionada no existe o está en estado inactivo',
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $orientacion = Orientacion::modificarOCrear($datos);
            if($orientacion){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["La orientación ha sido modificado.", 1], $orientacion),
                    Response::HTTP_OK
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar modificar la orientación."]), Response::HTTP_CONFLICT);;
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(get_response_body([$e->getMessage()]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction(); // Se abre la transacción
        try{
            $datos['id'] = $id;
            $validator = Validator::make($datos, [
                'id' => 'integer|required|exists:orientaciones,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $eliminado = Orientacion::eliminar($id);
            if($eliminado){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["La orientación ha sido eliminado.", 3]),
                    Response::HTTP_OK
                );
            }else{
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar eliminar la orientación."]), Response::HTTP_CONFLICT);
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
