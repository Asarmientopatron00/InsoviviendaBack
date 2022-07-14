<?php

namespace App\Http\Controllers\PersonasEntidades;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Exports\PersonasEntidades\PersonaInformacion;
use App\Http\Controllers\Controller;
use App\Models\PersonasEntidades\Persona;
use Illuminate\Support\Facades\Validator;

class PersonaController extends Controller
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
                $personas = Persona::obtenerColeccionLigera($datos);
            }else{
                if(isset($datos['ordenar_por'])){
                    $datos['ordenar_por'] = format_order_by_attributes($datos);
                }
                $personas = Persona::obtenerColeccion($datos);
            }
            return response($personas, Response::HTTP_OK);
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
                'personasIdentificacion' => 'string|required|unique:personas,personasIdentificacion',
                'tipo_identificacion_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_identificacion','id')->where(function ($query) {
                        $query->where('tipIdeEstado', 1);
                    }),
                ],
                'personasCategoriaAportes' => 'string|required',
                'personasNombres' => 'string|required',
                'personasPrimerApellido' => 'string|required',
                'personasSegundoApellido' => 'string|nullable',
                'personasFechaNacimiento' => 'date|required',
                'pais_nacimiento_id' => [
                    'integer',
                    'required',
                    Rule::exists('paises','id')->where(function ($query) {
                        $query->where('paisesEstado', 1);
                    }),
                ],
                'departamento_nacimiento_id' => [
                    'integer',
                    'required',
                    Rule::exists('departamentos','id')->where(function ($query) {
                        $query->where('departamentosEstado', 1);
                    }),
                ],
                'ciudad_nacimiento_id' => [
                    'integer',
                    'required',
                    Rule::exists('ciudades','id')->where(function ($query) {
                        $query->where('ciudadesEstado', 1);
                    }),
                ],
                'personasGenero' => 'string|required',
                'estado_civil_id' => [
                    'integer',
                    'required',
                    Rule::exists('estados_civil','id')->where(function ($query) {
                        $query->where('estCivEstado', 1);
                    }),
                ],
                'tipo_parentesco_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_parentesco','id')->where(function ($query) {
                        $query->where('tipParEstado', 1);
                    }),
                ],
                'tipo_poblacion_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('tipos_poblacion','id')->where(function ($query) {
                        $query->where('tipPobEstado', 1);
                    }),
                ],
                'tipo_discapacidad_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('tipos_discapacidad','id')->where(function ($query) {
                        $query->where('tipDisEstado', 1);
                    }),
                ],
                'personasSeguridadSocial' => 'string|required',
                'eps_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('eps','id')->where(function ($query) {
                        $query->where('epsEstado', 1);
                    }),
                ],
                'grado_escolaridad_id' => [
                    'integer',
                    'required',
                    Rule::exists('grados_escolaridad','id')->where(function ($query) {
                        $query->where('graEscEstado', 1);
                    }),
                ],
                'personasVehiculo' => 'string|nullable',
                'personasCorreo' => 'string|nullable',
                'personasFechaVinculacion' => 'date|required',
                'departamento_id' => [
                    'integer',
                    'required',
                    Rule::exists('departamentos','id')->where(function ($query) {
                        $query->where('departamentosEstado', 1);
                    }),
                ],
                'ciudad_id' => [
                    'integer',
                    'required',
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
                'personasDireccion' => 'string|required',
                'personasZona' => 'string|required',
                'personasEstrato' => 'string|required',
                'personasTelefonoCasa' => 'string|nullable',
                'personasTelefonoCelular' => 'string|nullable',
                'tipo_vivienda_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_vivienda','id')->where(function ($query) {
                        $query->where('tipVivEstado', 1);
                    }),
                ],
                'personasTipoPropiedad' => 'string|required',
                'personasNumeroEscritura' => 'string|nullable',
                'personasNotariaEscritura' => 'string|nullable',
                'personasFechaEscritura' => 'date|nullable',
                'personasIndicativoPC' => 'string|nullable',
                'personasNumeroHabitaciones' => 'integer|required',
                'personasNumeroBanos' => 'integer|required',
                'tipo_techo_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_techo','id')->where(function ($query) {
                        $query->where('tipTecEstado', 1);
                    }),
                ],
                'tipo_piso_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_piso','id')->where(function ($query) {
                        $query->where('tipPisEstado', 1);
                    }),
                ],
                'tipo_division_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_division','id')->where(function ($query) {
                        $query->where('tipDivEstado', 1);
                    }),
                ],
                'personasSala' => 'string|required',
                'personasComedor' => 'string|required',
                'personasCocina' => 'string|required',
                'personasPatio' => 'string|required',
                'personasTerraza' => 'string|required',
                'ocupacion_id' => [
                    'integer',
                    'required',
                    Rule::exists('ocupaciones','id')->where(function ($query) {
                        $query->where('ocupacionesEstado', 1);
                    }),
                ],
                'personasTipoTrabajo' => 'string|required',
                'personasTipoContrato' => 'string|required',
                'personasNombreEmpresa' => 'string|nullable',
                'personasTelefonoEmpresa' => 'string|nullable',
                'personasPuntajeProcredito' => 'integer|required',
                'personasPuntajeDatacredito' => 'integer|required',
                'departamento_correspondencia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('departamentos','id')->where(function ($query) {
                        $query->where('departamentosEstado', 1);
                    }),
                ],
                'ciudad_correspondencia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('ciudades','id')->where(function ($query) {
                        $query->where('ciudadesEstado', 1);
                    }),
                ],
                'comuna_correspondencia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('comunas','id')->where(function ($query) {
                        $query->where('comunasEstado', 1);
                    }),
                ],
                'barrio_correspondencia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('barrios','id')->where(function ($query) {
                        $query->where('barriosEstado', 1);
                    }),
                ],
                'personasCorDireccion' => 'string|nullable',
                'personasCorTelefono' => 'string|nullable',
                'personasIngresosFormales' => 'numeric|required',
                'personasIngresosInformales' => 'numeric|required',
                'personasIngresosArriendo' => 'numeric|nullable',
                'personasIngresosSubsidios' => 'numeric|nullable',
                'personasIngresosPaternidad' => 'numeric|nullable',
                'personasIngresosTerceros' => 'numeric|nullable',
                'personasIngresosOtros' => 'numeric|nullable',
                'personasAportesFormales' => 'numeric|required',
                'personasAportesInformales' => 'numeric|required',
                'personasAportesArriendo' => 'numeric|nullable',
                'personasAportesSubsidios' => 'numeric|nullable',
                'personasAportesPaternidad' => 'numeric|nullable',
                'personasAportesTerceros' => 'numeric|nullable',
                'personasAportesOtros' => 'numeric|nullable',
                'personasRefNombre1' => 'string|nullable',
                'personasRefTelefono1' => 'string|nullable',
                'personasRefNombre2' => 'string|nullable',
                'personasRefTelefono2' => 'string|nullable',
                'personasObservaciones' => 'string|nullable',
                'personasEstadoTramite' => 'string|required',
                'personasEstadoRegistro' => 'string|required',
                'familia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('familias','id')->where(function ($query) {
                        $query->where('familiasEstado', 1);
                    }),
                ],
            ],
                $messages = [
                    'tipo_identificacion_id.exists'=>'El tipo de identificacion seleccionado no existe o está en estado inactivo',
                    'pais_nacimiento_id.exists'=>'El pais seleccionado no existe o está en estado inactivo',
                    'departamento_nacimiento_id.exists'=>'El departamento seleccionado no existe o está en estado inactivo',
                    'ciudad_nacimiento_id.exists'=>'La ciudad seleccionada no existe o está en estado inactivo',
                    'estado_civil_id.exists'=>'El estado civil seleccionado no existe o está en estado inactivo',
                    'tipo_parentesco_id.exists'=>'El tipo de parentesco seleccionado no existe o está en estado inactivo',
                    'tipo_poblacion_id.exists'=>'El tipo de poblacion seleccionado no existe o está en estado inactivo',
                    'tipo_discapacidad_id.exists'=>'El tipo de discapacidad seleccionado no existe o está en estado inactivo',
                    'eps_id.exists'=>'La EPS seleccionada no existe o está en estado inactivo',
                    'grado_escolaridad_id.exists'=>'El grado de escolaridad seleccionado no existe o está en estado inactivo',
                    'departamento_id.exists'=>'El departamento seleccionado no existe o está en estado inactivo',
                    'ciudad_id.exists'=>'La ciudad seleccionada no existe o está en estado inactivo',
                    'comuna_id.exists'=>'La comuna seleccionada no existe o está en estado inactivo',
                    'barrio_id.exists'=>'El barrio seleccionado no existe o está en estado inactivo',
                    'departamento_correspondencia_id.exists'=>'El departamento seleccionado no existe o está en estado inactivo',
                    'ciudad_correspondencia_id.exists'=>'La ciudad seleccionada no existe o está en estado inactivo',
                    'comuna_correspondencia_id.exists'=>'La comuna seleccionada no existe o está en estado inactivo',
                    'barrio_correspondencia_id.exists'=>'El barrio seleccionado no existe o está en estado inactivo',
                    'tipo_vivienda_id.exists'=>'El tipo de vivienda seleccionado no existe o está en estado inactivo',
                    'tipo_techo_id.exists'=>'El tipo de techo seleccionado no existe o está en estado inactivo',
                    'tipo_piso_id.exists'=>'El tipo de piso seleccionado no existe o está en estado inactivo',
                    'tipo_division_id.exists'=>'El tipo de disivión seleccionado no existe o está en estado inactivo',
                    'ocupacion_id.exists'=>'La ocupación seleccionada no existe o está en estado inactivo',
                    'familia_id.exists'=>'La familia seleccionada no existe o está en estado inactivo',
                ]
            );

            if ($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $persona = Persona::modificarOCrear($datos);
            
            if ($persona) {
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["La persona ha sido creada.", 2], $persona),
                    Response::HTTP_CREATED
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar crear la persona."]), Response::HTTP_CONFLICT);
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
                'id' => 'integer|required|exists:personas,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            return response(Persona::cargar($id), Response::HTTP_OK);
        }catch (Exception $e){
            return response($e, Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'id' => 'integer|required|exists:personas,id',
                'personasIdentificacion' => 'string|required',
                'tipo_identificacion_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_identificacion','id')->where(function ($query) {
                        $query->where('tipIdeEstado', 1);
                    }),
                ],
                'personasCategoriaAportes' => 'string|required',
                'personasNombres' => 'string|required',
                'personasPrimerApellido' => 'string|required',
                'personasSegundoApellido' => 'string|nullable',
                'personasFechaNacimiento' => 'date|required',
                'pais_nacimiento_id' => [
                    'integer',
                    'required',
                    Rule::exists('paises','id')->where(function ($query) {
                        $query->where('paisesEstado', 1);
                    }),
                ],
                'departamento_nacimiento_id' => [
                    'integer',
                    'required',
                    Rule::exists('departamentos','id')->where(function ($query) {
                        $query->where('departamentosEstado', 1);
                    }),
                ],
                'ciudad_nacimiento_id' => [
                    'integer',
                    'required',
                    Rule::exists('ciudades','id')->where(function ($query) {
                        $query->where('ciudadesEstado', 1);
                    }),
                ],
                'personasGenero' => 'string|required',
                'estado_civil_id' => [
                    'integer',
                    'required',
                    Rule::exists('estados_civil','id')->where(function ($query) {
                        $query->where('estCivEstado', 1);
                    }),
                ],
                'tipo_parentesco_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_parentesco','id')->where(function ($query) {
                        $query->where('tipParEstado', 1);
                    }),
                ],
                'tipo_poblacion_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('tipos_poblacion','id')->where(function ($query) {
                        $query->where('tipPobEstado', 1);
                    }),
                ],
                'tipo_discapacidad_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('tipos_discapacidad','id')->where(function ($query) {
                        $query->where('tipDisEstado', 1);
                    }),
                ],
                'personasSeguridadSocial' => 'string|required',
                'eps_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('eps','id')->where(function ($query) {
                        $query->where('epsEstado', 1);
                    }),
                ],
                'grado_escolaridad_id' => [
                    'integer',
                    'required',
                    Rule::exists('grados_escolaridad','id')->where(function ($query) {
                        $query->where('graEscEstado', 1);
                    }),
                ],
                'personasVehiculo' => 'string|nullable',
                'personasCorreo' => 'string|nullable',
                'personasFechaVinculacion' => 'date|required',
                'departamento_id' => [
                    'integer',
                    'required',
                    Rule::exists('departamentos','id')->where(function ($query) {
                        $query->where('departamentosEstado', 1);
                    }),
                ],
                'ciudad_id' => [
                    'integer',
                    'required',
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
                'personasDireccion' => 'string|required',
                'personasZona' => 'string|required',
                'personasEstrato' => 'string|required',
                'personasTelefonoCasa' => 'string|nullable',
                'personasTelefonoCelular' => 'string|nullable',
                'tipo_vivienda_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_vivienda','id')->where(function ($query) {
                        $query->where('tipVivEstado', 1);
                    }),
                ],
                'personasTipoPropiedad' => 'string|required',
                'personasNumeroEscritura' => 'string|nullable',
                'personasNotariaEscritura' => 'string|nullable',
                'personasFechaEscritura' => 'date|nullable',
                'personasIndicativoPC' => 'string|nullable',
                'personasNumeroHabitaciones' => 'integer|required',
                'personasNumeroBanos' => 'integer|required',
                'tipo_techo_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_techo','id')->where(function ($query) {
                        $query->where('tipTecEstado', 1);
                    }),
                ],
                'tipo_piso_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_piso','id')->where(function ($query) {
                        $query->where('tipPisEstado', 1);
                    }),
                ],
                'tipo_division_id' => [
                    'integer',
                    'required',
                    Rule::exists('tipos_division','id')->where(function ($query) {
                        $query->where('tipDivEstado', 1);
                    }),
                ],
                'personasSala' => 'string|required',
                'personasComedor' => 'string|required',
                'personasCocina' => 'string|required',
                'personasPatio' => 'string|required',
                'personasTerraza' => 'string|required',
                'ocupacion_id' => [
                    'integer',
                    'required',
                    Rule::exists('ocupaciones','id')->where(function ($query) {
                        $query->where('ocupacionesEstado', 1);
                    }),
                ],
                'personasTipoTrabajo' => 'string|required',
                'personasTipoContrato' => 'string|required',
                'personasNombreEmpresa' => 'string|nullable',
                'personasTelefonoEmpresa' => 'string|nullable',
                'personasPuntajeProcredito' => 'integer|required',
                'personasPuntajeDatacredito' => 'integer|required',
                'departamento_correspondencia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('departamentos','id')->where(function ($query) {
                        $query->where('departamentosEstado', 1);
                    }),
                ],
                'ciudad_correspondencia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('ciudades','id')->where(function ($query) {
                        $query->where('ciudadesEstado', 1);
                    }),
                ],
                'comuna_correspondencia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('comunas','id')->where(function ($query) {
                        $query->where('comunasEstado', 1);
                    }),
                ],
                'barrio_correspondencia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('barrios','id')->where(function ($query) {
                        $query->where('barriosEstado', 1);
                    }),
                ],
                'personasCorDireccion' => 'string|nullable',
                'personasCorTelefono' => 'string|nullable',
                'personasIngresosFormales' => 'numeric|required',
                'personasIngresosInformales' => 'numeric|required',
                'personasIngresosArriendo' => 'numeric|nullable',
                'personasIngresosSubsidios' => 'numeric|nullable',
                'personasIngresosPaternidad' => 'numeric|nullable',
                'personasIngresosTerceros' => 'numeric|nullable',
                'personasIngresosOtros' => 'numeric|nullable',
                'personasAportesFormales' => 'numeric|required',
                'personasAportesInformales' => 'numeric|required',
                'personasAportesArriendo' => 'numeric|nullable',
                'personasAportesSubsidios' => 'numeric|nullable',
                'personasAportesPaternidad' => 'numeric|nullable',
                'personasAportesTerceros' => 'numeric|nullable',
                'personasAportesOtros' => 'numeric|nullable',
                'personasRefNombre1' => 'string|nullable',
                'personasRefTelefono1' => 'string|nullable',
                'personasRefNombre2' => 'string|nullable',
                'personasRefTelefono2' => 'string|nullable',
                'personasObservaciones' => 'string|nullable',
                'personasEstadoTramite' => 'string|required',
                'personasEstadoRegistro' => 'string|required',
                'familia_id' => [
                    'integer',
                    'nullable',
                    Rule::exists('familias','id')->where(function ($query) {
                        $query->where('familiasEstado', 1);
                    }),
                ],
            ],
                $messages = [
                    'tipo_identificacion_id.exists'=>'El tipo de identificacion seleccionado no existe o está en estado inactivo',
                    'pais_nacimiento_id.exists'=>'El pais seleccionado no existe o está en estado inactivo',
                    'departamento_nacimiento_id.exists'=>'El departamento seleccionado no existe o está en estado inactivo',
                    'ciudad_nacimiento_id.exists'=>'La ciudad seleccionada no existe o está en estado inactivo',
                    'estado_civil_id.exists'=>'El estado civil seleccionado no existe o está en estado inactivo',
                    'tipo_parentesco_id.exists'=>'El tipo de parentesco seleccionado no existe o está en estado inactivo',
                    'tipo_poblacion_id.exists'=>'El tipo de poblacion seleccionado no existe o está en estado inactivo',
                    'tipo_discapacidad_id.exists'=>'El tipo de discapacidad seleccionado no existe o está en estado inactivo',
                    'eps_id.exists'=>'La EPS seleccionada no existe o está en estado inactivo',
                    'grado_escolaridad_id.exists'=>'El grado de escolaridad seleccionado no existe o está en estado inactivo',
                    'departamento_id.exists'=>'El departamento seleccionado no existe o está en estado inactivo',
                    'ciudad_id.exists'=>'La ciudad seleccionada no existe o está en estado inactivo',
                    'comuna_id.exists'=>'La comuna seleccionada no existe o está en estado inactivo',
                    'barrio_id.exists'=>'El barrio seleccionado no existe o está en estado inactivo',
                    'departamento_correspondencia_id.exists'=>'El departamento seleccionado no existe o está en estado inactivo',
                    'ciudad_correspondencia_id.exists'=>'La ciudad seleccionada no existe o está en estado inactivo',
                    'comuna_correspondencia_id.exists'=>'La comuna seleccionada no existe o está en estado inactivo',
                    'barrio_correspondencia_id.exists'=>'El barrio seleccionado no existe o está en estado inactivo',
                    'tipo_vivienda_id.exists'=>'El tipo de vivienda seleccionado no existe o está en estado inactivo',
                    'tipo_techo_id.exists'=>'El tipo de techo seleccionado no existe o está en estado inactivo',
                    'tipo_piso_id.exists'=>'El tipo de piso seleccionado no existe o está en estado inactivo',
                    'tipo_division_id.exists'=>'El tipo de disivión seleccionado no existe o está en estado inactivo',
                    'ocupacion_id.exists'=>'La ocupación seleccionada no existe o está en estado inactivo',
                    'familia_id.exists'=>'La familia seleccionada no existe o está en estado inactivo',
                ]
            );

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $persona = Persona::modificarOCrear($datos);
            if($persona){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["La persona ha sido modificada.", 1], $persona),
                    Response::HTTP_OK
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar modificar la persona."]), Response::HTTP_CONFLICT);;
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
                'id' => 'integer|required|exists:personas,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $eliminado = Persona::eliminar($id);
            if($eliminado){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["La persona ha sido eliminada.", 3]),
                    Response::HTTP_OK
                );
            }else{
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar eliminar la persona."]), Response::HTTP_CONFLICT);
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function informePersonas(Request $request)
    {
        $nombreArchivo = 'personas-' . time() . '.xlsx';
        return (new PersonaInformacion($request->all()))->download($nombreArchivo);
        // return Excel::download(new ParticipanteInformacion($request->all()), $nombreArchivo);
    }
}
