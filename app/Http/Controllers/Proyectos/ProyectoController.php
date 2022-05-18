<?php

namespace App\Http\Controllers\Proyectos;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProyectoController extends Controller
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
            if(isset($datos['ordenar_por'])){
                $datos['ordenar_por'] = format_order_by_attributes($datos);
            }
            $proyectos = Proyecto::obtenerColeccion($datos);
            return response($proyectos, Response::HTTP_OK);
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
                'persona_id' => [
                    'integer',
                    'required',
                    Rule::exists('personas','id')->where(function ($query) {
                        $query->where('personasEstadoRegistro', 'AC');
                    }),
                ],
                'proyectosEstadoProyecto' => 'string|required',
                'proyectosFechaSolicitud' => 'date|required',
                'proyectosTipoProyecto' => 'string|required',
                'tipo_programa_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_programa','id')->where(function ($query) {
                        $query->where('tipProEstado', '1');
                    }),
                ],
                'proyectosRemitido' => 'string|required',
                'remitido_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('personas','id')->where(function ($query) {
                        $query->where('personasEstadoRegistro', 'AC');
                    }),
                ],
                'pais_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('paises','id')->where(function ($query) {
                        $query->where('paisesEstado', 1);
                    }),
                ],
                'departamento_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('departamentos','id')->where(function ($query) {
                        $query->where('departamentosEstado', 1);
                    }),
                ],
                'ciudad_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('ciudades','id')->where(function ($query) {
                        $query->where('ciudadesEstado', 1);
                    }),
                ],
                'comuna_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('comunas','id')->where(function ($query) {
                        $query->where('comunasEstado', 1);
                    }),
                ],
                'barrio_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('barrios','id')->where(function ($query) {
                        $query->where('barriosEstado', 1);
                    }),
                ],
                'proyectosZona' => 'string|nullable',
                'proyectosDireccion' => 'string|nullable',
                'proyectosVisitaDomiciliaria' => 'string|nullable',
                'proyectosFechaVisitaDom' => 'date|nullable',
                'proyectosPagoEstudioCre' => 'string|nullable',
                'proyectosReqLicenciaCon' => 'string|nullable',
                'proyectosFechaInicioEstudio' => 'date|nullable',
                'proyectosFechaAproRec' => 'date|nullable',
                'proyectosFechaEstInicioObr' => 'date|nullable',
                'proyectosValorProyecto' => 'numeric|nullable',
                'proyectosValorSolicitud' => 'numeric|nullable',
                'proyectosValorRecursosSol' => 'numeric|nullable',
                'proyectosValorSubsidios' => 'numeric|nullable',
                'proyectosValorDonaciones' => 'numeric|nullable',
                'proyectosValorCuotaAprobada' => 'numeric|nullable',
                'proyectosValorCapPagoMen' => 'numeric|nullable',
                'proyectosValorAprobado' => 'numeric|nullable',
                'proyectosValorSeguroVida' => 'numeric|nullable',
                'proyectosTasaInteresNMV' => 'numeric|nullable',
                'proyectosTasaInteresEA' => 'numeric|nullable',
                'proyectosNumeroCuotas' => 'numeric|nullable',
                'banco_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('bancos','id')->where(function ($query) {
                        $query->where('bancosEstado', 1);
                    }),
                ],
                'proyectosTipoCuentaRecaudo' => 'string|nullable',
                'proyectosNumCuentaRecaudo' => 'string|nullable',
                'proyectosEstadoFormalizacion' => 'string|nullable',
                'proyectosFechaAutNotaria' => 'date|nullable',
                'proyectosFechaFirEscrituras' => 'date|nullable',
                'proyectosFechaIngresoReg' => 'date|nullable',
                'proyectosFechaSalidaReg' => 'date|nullable',
                'proyectosAutorizacionDes' => 'string|nullable',
                'proyectosFechaAutDes' => 'date|nullable',
                'proyectosFechaCancelacion' => 'date|nullable',
                'orientador_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('orientadores','id')->where(function ($query) {
                        $query->where('orientadoresEstado', 1);
                    }),
                ],
                'proyectosObservaciones' => 'string|nullable',
            ],
                $messages = [
                    'persona_id.exists'=>'La persona seleccionada no existe o está en estado inactivo',
                    'tipo_programa_id.exists'=>'El tipo de programa seleccionado no existe o está en estado inactivo',
                    'remitido_id.exists'=>'La persona seleccionada no existe o está en estado inactivo',
                    'pais_id.exists'=>'El pais seleccionado no existe o está en estado inactivo',
                    'departamento_id.exists'=>'El departamento seleccionado no existe o está en estado inactivo',
                    'ciudad_id.exists'=>'La ciudad seleccionada no existe o está en estado inactivo',
                    'comuna_id.exists'=>'La comuna seleccionada no existe o está en estado inactivo',
                    'barrio_id.exists'=>'El barrio seleccionado no existe o está en estado inactivo',
                    'banco_id.exists'=>'El banco seleccionado no existe o está en estado inactivo',
                    'orientador_id.exists'=>'El asesor seleccionado no existe o está en estado inactivo',
                ]
            );

            if ($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $proyecto = Proyecto::modificarOCrear($datos);
            
            if ($proyecto) {
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El proyecto ha sido creado.", 2], $proyecto),
                    Response::HTTP_CREATED
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar crear el proyecto."]), Response::HTTP_CONFLICT);
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
                'id' => 'integer|required|exists:proyectos,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            return response(Proyecto::cargar($id), Response::HTTP_OK);
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
                'id' => 'integer|required|exists:proyectos,id',
                'persona_id' => [
                    'integer',
                    'required',
                    Rule::exists('personas','id')->where(function ($query) {
                        $query->where('personasEstadoRegistro', 'AC');
                    }),
                ],
                'proyectosEstadoProyecto' => 'string|required',
                'proyectosFechaSolicitud' => 'date|required',
                'proyectosTipoProyecto' => 'string|required',
                'tipo_programa_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_programa','id')->where(function ($query) {
                        $query->where('tipProEstado', '1');
                    }),
                ],
                'proyectosRemitido' => 'string|required',
                'remitido_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('personas','id')->where(function ($query) {
                        $query->where('personasEstadoRegistro', 'AC');
                    }),
                ],
                'pais_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('paises','id')->where(function ($query) {
                        $query->where('paisesEstado', 1);
                    }),
                ],
                'departamento_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('departamentos','id')->where(function ($query) {
                        $query->where('departamentosEstado', 1);
                    }),
                ],
                'ciudad_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('ciudades','id')->where(function ($query) {
                        $query->where('ciudadesEstado', 1);
                    }),
                ],
                'comuna_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('comunas','id')->where(function ($query) {
                        $query->where('comunasEstado', 1);
                    }),
                ],
                'barrio_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('barrios','id')->where(function ($query) {
                        $query->where('barriosEstado', 1);
                    }),
                ],
                'proyectosZona' => 'string|nullable',
                'proyectosDireccion' => 'string|nullable',
                'proyectosVisitaDomiciliaria' => 'string|nullable',
                'proyectosFechaVisitaDom' => 'date|nullable',
                'proyectosPagoEstudioCre' => 'string|nullable',
                'proyectosReqLicenciaCon' => 'string|nullable',
                'proyectosFechaInicioEstudio' => 'date|nullable',
                'proyectosFechaAproRec' => 'date|nullable',
                'proyectosFechaEstInicioObr' => 'date|nullable',
                'proyectosValorProyecto' => 'numeric|nullable',
                'proyectosValorSolicitud' => 'numeric|nullable',
                'proyectosValorRecursosSol' => 'numeric|nullable',
                'proyectosValorSubsidios' => 'numeric|nullable',
                'proyectosValorDonaciones' => 'numeric|nullable',
                'proyectosValorCuotaAprobada' => 'numeric|nullable',
                'proyectosValorCapPagoMen' => 'numeric|nullable',
                'proyectosValorAprobado' => 'numeric|nullable',
                'proyectosValorSeguroVida' => 'numeric|nullable',
                'proyectosTasaInteresNMV' => 'numeric|nullable',
                'proyectosTasaInteresEA' => 'numeric|nullable',
                'proyectosNumeroCuotas' => 'numeric|nullable',
                'banco_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('bancos','id')->where(function ($query) {
                        $query->where('bancosEstado', 1);
                    }),
                ],
                'proyectosTipoCuentaRecaudo' => 'string|nullable',
                'proyectosNumCuentaRecaudo' => 'string|nullable',
                'proyectosEstadoFormalizacion' => 'string|nullable',
                'proyectosFechaAutNotaria' => 'date|nullable',
                'proyectosFechaFirEscrituras' => 'date|nullable',
                'proyectosFechaIngresoReg' => 'date|nullable',
                'proyectosFechaSalidaReg' => 'date|nullable',
                'proyectosAutorizacionDes' => 'string|nullable',
                'proyectosFechaAutDes' => 'date|nullable',
                'proyectosFechaCancelacion' => 'date|nullable',
                'orientador_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('orientadores','id')->where(function ($query) {
                        $query->where('orientadoresEstado', 1);
                    }),
                ],
                'proyectosObservaciones' => 'string|nullable',
            ],
                $messages = [
                    'persona_id.exists'=>'La persona seleccionada no existe o está en estado inactivo',
                    'tipo_programa_id.exists'=>'El tipo de programa seleccionado no existe o está en estado inactivo',
                    'remitido_id.exists'=>'La persona seleccionada no existe o está en estado inactivo',
                    'pais_id.exists'=>'El pais seleccionado no existe o está en estado inactivo',
                    'departamento_id.exists'=>'El departamento seleccionado no existe o está en estado inactivo',
                    'ciudad_id.exists'=>'La ciudad seleccionada no existe o está en estado inactivo',
                    'comuna_id.exists'=>'La comuna seleccionada no existe o está en estado inactivo',
                    'barrio_id.exists'=>'El barrio seleccionado no existe o está en estado inactivo',
                    'banco_id.exists'=>'El banco seleccionado no existe o está en estado inactivo',
                    'orientador_id.exists'=>'El asesor seleccionado no existe o está en estado inactivo',
                ]
            );

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $proyecto = Proyecto::modificarOCrear($datos);
            if($proyecto){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El proyecto ha sido modificado.", 1], $proyecto),
                    Response::HTTP_OK
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar modificar el proyecto."]), Response::HTTP_CONFLICT);;
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
                'id' => 'integer|required|exists:proyectos,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $eliminado = Proyecto::eliminar($id);
            if($eliminado){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El proyecto ha sido eliminado.", 3]),
                    Response::HTTP_OK
                );
            }else{
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar eliminar el proyecto."]), Response::HTTP_CONFLICT);
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
