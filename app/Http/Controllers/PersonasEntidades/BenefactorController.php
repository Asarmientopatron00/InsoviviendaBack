<?php

/**
 * Controlador para comunicación entre la vista y el modelo de la funcionalidad de Benefactores.
 * @author  ASSIS S.A.S
 *          Jose Alejandro Gutierrez B
 * @version 20/05/2022/A
 */

namespace App\Http\Controllers\PersonasEntidades;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Benefactores\Benefactor;

class BenefactorController extends Controller
{
   /**
    * Presenta un listado con la información de la funcionalidad.
    * @param Request $request
    * @return Response
    */
   public function index(Request $request)
   {
      try { $datos = $request->all();

            // valida entrada de parametros a la funcion
            if (!$request->ligera) {
               $retVal = Validator::make($datos, ['limite' => 'integer|between:1,500']);
               if ($retVal->fails())
                  return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);
            }

            // captura lista de registros de repositorio benefactores
            if ($request->ligera)
               $retLista = Benefactor::obtenerColeccionLigera($datos);
            else {
               if (isset($datos['ordenar_por']))
                  $datos['ordenar_por'] = format_order_by_attributes($datos);
               $retLista = Benefactor::obtenerColeccion($datos);
            }

            return response($retLista, Response::HTTP_OK);
         }
      catch(Exception $e)
         {
            return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
         }
   }

   /**
    * Almacena o crea un registro en el repositorio de la funcionalidad.
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function store(Request $request)
   {
      DB::beginTransaction(); // Se abre la transaccion
      try { $datos = $request->all();

            // realiza validaciones generales de datos para el repositorio benefactores
            $retVal = Validator::make( $datos, 
                                       ['benefactoresIdentificacion'    => 'string|required|max:128',
                                        'benefactoresNombres'           => 'string|required|max:128',
                                        'benefactoresPrimerApellido'    => 'string|required|max:128',
                                        'benefactoresSegundoApellido'   => 'string|required|max:128',
                                        'tipo_benefactor_id'            => ['integer', 'required', Rule::exists('tipos_benefactor','id')->where(function ($query) { $query->where('tipBenEstado', 1); }), ],
                                        'benefactoresNombrePerContacto' => 'string|required|max:128',
                                        'benefactor_id'                 => ['integer', 'required', Rule::exists('benefactores','id')->where(function ($query) { $query->where('estado', 1); }), ],
                                        'pais_id'                       => ['integer', 'required', Rule::exists('paises','id')->where(function ($query) { $query->where('paisesEstado', 1); }), ],
                                        'departamento_id'               => ['integer', 'required', Rule::exists('departamentos','id')->where(function ($query) { $query->where('departamentosEstado', 1); }), ],
                                        'ciudad_id'                     => ['integer', 'required', Rule::exists('ciudades','id')->where(function ($query) { $query->where('ciudadesEstado', 1); }), ],
                                        'comuna_id'                     => ['integer', 'required', Rule::exists('comunas','id')->where(function ($query) { $query->where('comunasEstado', 1); }), ],
                                        'barrio_id'                     => ['integer', 'required', Rule::exists('barrios','id')->where(function ($query) { $query->where('barriosEstado', 1); }), ],
                                        'benefactoresDireccion'         => 'string|required|max:128',
                                        'benefactoresTelefonoFijo'      => 'string|required|max:128',
                                        'benefactoresTelefonoCelular'   => 'string|required|max:128',
                                        'benefactoresCorreo'            => 'string|required|max:128',
                                        'benefactoresNotas'             => 'string|required|max:512',
                                        'estado'                        => 'boolean|required',],
                                       $msgErr = [ 'tipo_benefactor_id.exists' => 'El tipo de benefactor no existe o está en estado inactivo',
                                                   'benefactor_id.exists'      => 'El benefactor no existe o está en estado inactivo',
                                                   'pais_id.exists'            => 'El pais seleccionado no existe o está en estado inactivo',
                                                   'departamento_id.exists'    => 'El departamento seleccionado no existe o está en estado inactivo',
                                                   'ciudad_id.exists'          => 'La ciudad seleccionada no existe o está en estado inactivo', 
                                                   'comuna_id.exists'          => 'La comuna seleccionada no existe o está en estado inactivo',
                                                   'barrio_id.exists'          => 'El barrio seleccionado no existe o está en estado inactivo', ] );
            if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

            // inserta registro en repositorio benefactores
            $regCre = Benefactor::modificarOCrear($datos);
            if ($regCre) {
               DB::commit(); // Se cierra la transaccion correctamente
               return response(get_response_body(["Benefactor, ha sido creado.", 2], $regCre), Response::HTTP_CREATED);
            }
            else {
               DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
               return response(get_response_body(["Error al crear Benefactor."]), Response::HTTP_CONFLICT);
            }
         }
      catch (Exception $e)
         {
            DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
         }
   }

   /**
    * Presenta la información de un registro especifico de la funcionalidad.
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function show($id)
   {
      try { $datos['id'] = $id;

            // verifica la existencia del id de registro en el repositorio benefactores
            $retVal = Validator::make($datos, ['id' => 'integer|required|exists:benefactores,id']);
            if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

            // captura y retorna el detalle de registro del repositorio benefactores
            return response(Benefactor::cargar($id), Response::HTTP_OK);
         }
      catch (Exception $e)
         {
           return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
         }
   }

   /**
    * Presenta el formulario para actualizar el registro especifico de la funcionalidad.
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function update(Request $request, $id)
   {
      DB::beginTransaction(); // Se abre la transaccion
      try { $datos = $request->all();
            $datos['id'] = $id;

            // verifica la existencia del id de registro y realiza validaciones a los campos para actualizar el repositorio benefactores
            $retVal = Validator::make( $datos, 
                                       ['id'                            => 'integer|required|exists:benefactores,id',
                                        'benefactoresIdentificacion'    => 'string|required|max:128',
                                        'benefactoresNombres'           => 'string|required|max:128',
                                        'benefactoresPrimerApellido'    => 'string|required|max:128',
                                        'benefactoresSegundoApellido'   => 'string|required|max:128',
                                        'tipo_benefactor_id'            => ['integer', 'required', Rule::exists('tipos_benefactor','id')->where(function ($query) { $query->where('tipBenEstado', 1); }), ],
                                        'benefactoresNombrePerContacto' => 'string|required|max:128',
                                        'benefactor_id'                 => ['integer', 'required', Rule::exists('benefactores','id')->where(function ($query) { $query->where('estado', 1); }), ],
                                        'pais_id'                       => ['integer', 'required', Rule::exists('paises','id')->where(function ($query) { $query->where('paisesEstado', 1); }), ],
                                        'departamento_id'               => ['integer', 'required', Rule::exists('departamentos','id')->where(function ($query) { $query->where('departamentosEstado', 1); }), ],
                                        'ciudad_id'                     => ['integer', 'required', Rule::exists('ciudades','id')->where(function ($query) { $query->where('ciudadesEstado', 1); }), ],
                                        'comuna_id'                     => ['integer', 'required', Rule::exists('comunas','id')->where(function ($query) { $query->where('comunasEstado', 1); }), ],
                                        'barrio_id'                     => ['integer', 'required', Rule::exists('barrios','id')->where(function ($query) { $query->where('barriosEstado', 1); }), ],
                                        'benefactoresDireccion'         => 'string|required|max:128',
                                        'benefactoresTelefonoFijo'      => 'string|required|max:128',
                                        'benefactoresTelefonoCelular'   => 'string|required|max:128',
                                        'benefactoresCorreo'            => 'string|required|max:128',
                                        'benefactoresNotas'             => 'string|required|max:512',
                                        'estado'                        => 'boolean|required' ],
                                       $msgErr = [ 'tipo_benefactor_id.exists' => 'El tipo de benefactor seleccionado no existe o está en estado inactivo',
                                                   'benefactor_id.exists'      => 'El benefactor seleccionado no existe o está en estado inactivo',
                                                   'pais_id.exists'            => 'El pais seleccionado no existe o está en estado inactivo',
                                                   'departamento_id.exists'    => 'El departamento seleccionado no existe o está en estado inactivo',
                                                   'ciudad_id.exists'          => 'La ciudad seleccionada no existe o está en estado inactivo', 
                                                   'comuna_id.exists'          => 'La comuna seleccionada no existe o está en estado inactivo',
                                                   'barrio_id.exists'          => 'El barrio seleccionado no existe o está en estado inactivo', ] );
         if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

            // actualiza/modifica registro en repositorio benefactores
            $regMod = Benefactor::modificarOCrear($datos);
            if ($regMod) {
               DB::commit(); // Se cierra la transaccion correctamente
               return response(get_response_body(["Benefactor, ha sido modificado.", 1], $regMod), Response::HTTP_OK);
            }
            else {
               DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
               return response(get_response_body(["Error al modificar el Benefactor."]), Response::HTTP_CONFLICT);;
            }
         }
      catch (Exception $e)
         {
            DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
            return response(get_response_body([$e->getMessage()]), Response::HTTP_INTERNAL_SERVER_ERROR);
         }
   }

   /**
    * Elimina un registro especifico de la funcionalidad.
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function destroy($id)
   {
      DB::beginTransaction(); // Se abre la transaccion
      try { $datos['id'] = $id;

            // verifica la existencia del id de registro en el repositorio benefactores
            $retVal = Validator::make($datos, ['id' => 'integer|required|exists:benefactores,id']);
            if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

            // elimina registro en repositorio benefactores
            $regEli = Benefactor::eliminar($id);
            if ($regEli){
               DB::commit(); // Se cierra la transaccion correctamente
               return response(get_response_body(["Benefactor, ha sido eliminado.", 3]), Response::HTTP_OK);
            }
            else {
               DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
               return response(get_response_body(["Error al eliminar el Benefactor."]), Response::HTTP_CONFLICT);
            }
         }
      catch (Exception $e)
         {
            DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
         }
   }
}
