<?php

namespace App\Http\Controllers\Proyectos;

use Exception;
use App\Rules\MinNormDate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Rules\HasDisbursement;
use App\Rules\UpToDateProject;
use App\Rules\DesembolsoMaximo;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Proyectos\Desembolso;
use Illuminate\Support\Facades\Validator;
use App\Exports\Proyectos\DesembolsoExport;

class DesembolsoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
            if(isset($datos['ordenar_por'])){
                $datos['ordenar_por'] = format_order_by_attributes($datos);
            }
            $desembolsos = Desembolso::obtenerColeccion($datos);
            return response($desembolsos, Response::HTTP_OK);
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
                'proyecto_id' => [
                    'integer',
                    'required',
                    Rule::exists('proyectos','id')->where(function ($query) {
                        $query->whereNot('proyectosEstadoProyecto', 'CAN')
                            ->whereNot('proyectosEstadoProyecto', 'CON')
                            ->whereNot('proyectosEstadoProyecto', 'REC');
                    }),
                ],
                'desembolsosFechaDesembolso' => 'date|required',
                'desembolsosValorDesembolso' => [
                    'numeric',
                    'required', 
                    new DesembolsoMaximo(
                        $datos['proyecto_id'], 
                        0,
                        $datos['desembolsosValorDesembolso']
                )],
                'desembolsosFechaNormalizacionP' => 'date|required',
                'desembolsosDescripcionDes' => 'string|required',
                'banco_id' => [
                    'integer',
                    'required',
                    Rule::exists('bancos','id')->where(function ($query) {
                        $query->where('bancosEstado', 1);
                    }),
                ],
                'desembolsosTipoCuentaDes' => 'string|required|max:3',
                'desembolsosNumeroCuentaDes' => 'numeric|required',
                'desembolsosNumeroComEgreso' => 'numeric|required',
                'desembolsosPlanDefinitivo' => 'boolean|required',
                'desembolsosEstado' => 'boolean|required',
            ], $messages = [
                'proyecto_id.exists'=>'El proyecto seleccionado no existe, fue cancelado, rechazado o está congelado',
                'banco_id.exists'=>'El banco seleccionado no existe o está en estado inactivo',
            ]);

            if ($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $desembolso = Desembolso::modificarOCrear($datos);
            
            if ($desembolso) {
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El desembolso ha sido creado.", 2], $desembolso),
                    Response::HTTP_CREATED
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar crear el desembolso."]), Response::HTTP_CONFLICT);
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response($e, Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'id' => 'integer|required|exists:desembolsos,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            return response(Desembolso::cargar($id), Response::HTTP_OK);
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
                'id' => 'integer|required|exists:desembolsos,id',
                'proyecto_id' => [
                    'integer',
                    'required',
                    Rule::exists('proyectos','id')->where(function ($query) {
                        $query->whereNot('proyectosEstadoProyecto', 'CAN')
                            ->whereNot('proyectosEstadoProyecto', 'CON')
                            ->whereNot('proyectosEstadoProyecto', 'REC');
                    }),
                ],
                'desembolsosFechaDesembolso' => 'date|required',
                'desembolsosValorDesembolso' => [
                    'numeric',
                    'required', 
                    new DesembolsoMaximo(
                        $datos['proyecto_id'], 
                        $datos['id'],
                        $datos['desembolsosValorDesembolso']
                )],
                'desembolsosFechaNormalizacionP' => 'date|required',
                'desembolsosDescripcionDes' => 'string|required',
                'banco_id' => [
                    'integer',
                    'required',
                    Rule::exists('bancos','id')->where(function ($query) {
                        $query->where('bancosEstado', 1);
                    }),
                ],
                'desembolsosTipoCuentaDes' => 'string|required|max:3',
                'desembolsosNumeroCuentaDes' => 'numeric|required',
                'desembolsosNumeroComEgreso' => 'numeric|required',
                'desembolsosPlanDefinitivo' => 'boolean|required',
                'desembolsosEstado' => 'boolean|required',
            ], $messages = [
                'proyecto_id.exists'=>'El proyecto seleccionado no existe, fue cancelado, rechazado o está congelado',
                'banco_id.exists'=>'El banco seleccionado no existe o está en estado inactivo',
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $desembolso = Desembolso::modificarOCrear($datos);
            if($desembolso){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El desembolso ha sido modificado.", 1], $desembolso),
                    Response::HTTP_OK
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar modificar el desembolso."]), Response::HTTP_CONFLICT);;
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
                'id' => 'integer|required|exists:desembolsos,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $eliminado = Desembolso::eliminar($id);
            if($eliminado){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El desembolso ha sido elimado.", 3]),
                    Response::HTTP_OK
                );
            }else{
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar eliminar el desembolso."]), Response::HTTP_CONFLICT);
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function descargaDesembolso(Request $request)
    {
        $nombreArchivo = 'Desembolso-' . time() . '.xlsx';
        return (new DesembolsoExport($request->all()))->download($nombreArchivo);
    }

    public function reajustarFechaPago(Request $request)
    {
        DB::beginTransaction(); // Se abre la transacción
        try {
            $datos = $request->all();
            $validator = Validator::make($datos, [
                'proyecto_id' => [
                    'integer',
                    'required',
                    Rule::exists('proyectos','id')->where(function ($query) {
                        $query->where('proyectosEstadoProyecto', 'DES');
                    }),
                    new HasDisbursement(),
                    new UpToDateProject()
                ],
                'desembolsosFechaNormalizacionP' => [
                    'date',
                    'required',
                    new MinNormDate($datos['proyecto_id'])
                ],
            ], $messages = [
                'proyecto_id.exists'=>'El proyecto seleccionado no existe o está en un estado no permitido para esta opción',
            ]);

            if ($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $desembolso = Desembolso::reajustarFechaPago($datos);
            
            if ($desembolso) {
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["Se ha ajustado la fecha de pagos exitosamene.", 2], $desembolso),
                    Response::HTTP_CREATED
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar cambiar fecha de pagos."]), Response::HTTP_CONFLICT);
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response($e, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
