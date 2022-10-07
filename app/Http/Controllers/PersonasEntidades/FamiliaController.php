<?php

namespace App\Http\Controllers\PersonasEntidades;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PersonasEntidades\Familia;
use Illuminate\Support\Facades\Validator;
use App\Exports\PersonasEntidades\FamiliasExport;

class FamiliaController extends Controller
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
                $familia = Familia::obtenerColeccionLigera($datos);
            }else{
                if(isset($datos['ordenar_por'])){
                    $datos['ordenar_por'] = format_order_by_attributes($datos);
                }
                $familia = Familia::obtenerColeccion($datos);
            }
            return response($familia, Response::HTTP_OK);
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
                'identificacion_persona' => [
                    'string',
                    'required',
                    Rule::exists('personas','personasIdentificacion')->where(function ($query) {
                        $query->where('personasEstadoRegistro', 'AC');
                    }),
                ],
                'tipo_familia_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_familia','id')->where(function ($query) {
                        $query->where('tipFamEstado', 1);
                    }),
                ],
                'condicion_familia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('condiciones_familia','id')->where(function ($query) {
                        $query->where('conFamEstado', 1);
                    }),
                ],
                'familiasFechaVisitaDomici' => 'date|nullable',
                'familiasAportesFormales' => 'numeric|required',
                'familiasAportesInformales' => 'numeric|required',
                'familiasAportesArriendo' => 'numeric|nullable',
                'familiasAportesSubsidios' => 'numeric|nullable',
                'familiasAportesPaternidad' => 'numeric|nullable',
                'familiasAportesTerceros' => 'numeric|nullable',
                'familiasAportesOtros' => 'numeric|nullable',
                'familiasEgresosDeudas' => 'numeric|required',
                'familiasEgresosEducacion' => 'numeric|required',
                'familiasEgresosSalud' => 'numeric|required',
                'familiasEgresosTransporte' => 'numeric|required',
                'familiasEgresosSerPublicos' => 'numeric|required',
                'familiasEgresosAlimentacion' => 'numeric|required',
                'familiasEgresosVivienda' => 'numeric|required',
                'familiasEgresosOtros' => 'numeric|required',
                'familiasEstado' => 'boolean|required',
                'familiasObservaciones' => 'string|nullable',
            ],
                $messages = [
                    'identificacion_persona.exists'=>'La identificación seleccionado no coincide con ninguna registrada o pertenece a alguien inactivo',
                    'tipo_familias_id.exists'=>'El tipo de familia seleccionado no existe o está en estado inactivo',
                    'condicion_familia_id.exists'=>'La condición de familia seleccionada no existe o está en estado inactivo',
                ]
            );

            if ($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $familia = Familia::modificarOCrear($datos);
            
            if ($familia) {
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["La familia ha sido creada.", 2], $familia),
                    Response::HTTP_CREATED
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar crear la familia."]), Response::HTTP_CONFLICT);
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
                'id' => 'integer|required|exists:familias,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            return response(Familia::cargar($id), Response::HTTP_OK);
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
                'id' => 'integer|required|exists:familias,id',
                'identificacion_persona' => [
                    'string',
                    'required',
                    Rule::exists('personas','personasIdentificacion')->where(function ($query) {
                        $query->where('personasEstadoRegistro', 'AC');
                    }),
                ],
                'tipo_familia_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_familia','id')->where(function ($query) {
                        $query->where('tipFamEstado', 1);
                    }),
                ],
                'condicion_familia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('condiciones_familia','id')->where(function ($query) {
                        $query->where('conFamEstado', 1);
                    }),
                ],
                'familiasFechaVisitaDomici' => 'date|nullable',
                'familiasAportesFormales' => 'numeric|required',
                'familiasAportesInformales' => 'numeric|required',
                'familiasAportesArriendo' => 'numeric|nullable',
                'familiasAportesSubsidios' => 'numeric|nullable',
                'familiasAportesPaternidad' => 'numeric|nullable',
                'familiasAportesTerceros' => 'numeric|nullable',
                'familiasAportesOtros' => 'numeric|nullable',
                'familiasEgresosDeudas' => 'numeric|required',
                'familiasEgresosEducacion' => 'numeric|required',
                'familiasEgresosSalud' => 'numeric|required',
                'familiasEgresosTransporte' => 'numeric|required',
                'familiasEgresosSerPublicos' => 'numeric|required',
                'familiasEgresosAlimentacion' => 'numeric|required',
                'familiasEgresosVivienda' => 'numeric|required',
                'familiasEgresosOtros' => 'numeric|required',
                'familiasEstado' => 'boolean|required',
                'familiasObservaciones' => 'string|nullable',
            ],
                $messages = [
                    'identificacion_persona.exists'=>'La identificación seleccionado no coincide con ninguna registrada o pertenece a alguien inactivo',
                    'tipos_familia_id.exists'=>'El tipo de familia seleccionado no existe o está en estado inactivo',
                    'condicion_familia_id.exists'=>'La condición de familia seleccionada no existe o está en estado inactivo',
                ]
            );

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $familia = Familia::modificarOCrear($datos);
            if($familia){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["La familia ha sido modificada.", 1], $familia),
                    Response::HTTP_OK
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar modificar la familia."]), Response::HTTP_CONFLICT);;
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
                'id' => 'integer|required|exists:familias,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $eliminado = Familia::eliminar($id);
            if($eliminado){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["La familia ha sido elimada.", 3]),
                    Response::HTTP_OK
                );
            }else{
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar eliminar la familia."]), Response::HTTP_CONFLICT);
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function familiaExport(Request $request)
    {
        $nombreArchivo = 'familias-' . time() . '.xlsx';
        return (new FamiliasExport($request->all()))->download($nombreArchivo);
    }
}
