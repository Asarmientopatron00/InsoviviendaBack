<?php

/**
 * Controlador para comunicación entre la vista y el modelo de la funcionalidad de Persona asesoria.
 * @author  ASSIS S.A.S
 *          Jose Alejandro Gutierrez B
 * @version 7/08/2022/A
 */

namespace App\Http\Controllers\PersonasEntidades;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\PersonasEntidades\PersonaAsesoria;

class PersonaAsesoriaController extends Controller
{
   /**
    * Presenta un listado con la información de la funcionalidad.
    * @param Request $request
    * @return Response
    */
   public function index(Request $request)
   {
      try { 
         $datos = $request->all();
   
         // valida entrada de parametros a la funcion
         if (!$request->ligera) {
            $retVal = Validator::make(
               $datos, 
               [  'limite' => 
                  'integer|between:1,500'
               ]
            );
            if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);
         }
   
         // captura lista de registros de repositorio personas_asesorias
         if ($request->ligera)
            $retLista = PersonaAsesoria::obtenerColeccionLigera($datos);
         else {
            if (isset($datos['ordenar_por']))
               $datos['ordenar_por'] = format_order_by_attributes($datos);
            $retLista = PersonaAsesoria::obtenerColeccion($datos);
         }
   
         return response($retLista, Response::HTTP_OK);
      }
      catch(Exception $e) {
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
      try { 
         $datos = $request->all();
 
         // realiza validaciones generales de datos para el repositorio personas_asesorias
         $retVal = Validator::make( 
            $datos, 
            [  
               'tipo_identificacion_id' => 
                  [  'integer', 
                     'required', 
                     Rule::exists('tipos_identificacion','id') ->
                        where(function ($query) { 
                           $query -> where('tipIdeEstado', 1); 
                        }), 
                  ],
   
               'numero_documento' => 'string|required|max:128',
 
               'nombre' => 'string|required',
   
               'telefono' => 'string|nullable|max:128',
   
               'celular' => 'string|nullable|max:128',
   
               'direccion' => 'string|nullable|max:128',
   
               'departamento_id' => 
                  [  'integer', 
                     'nullable', 
                     Rule::exists('departamentos','id') ->
                        where(function ($query) { 
                           $query -> where('departamentosEstado', 1); 
                        }), 
                  ],
   
               'ciudad_id' => 
                  [  'integer', 
                     'nullable', 
                     Rule::exists('ciudades','id') ->
                        where(function ($query) { 
                           $query -> where('ciudadesEstado', 1); 
                        }), 
                  ],
   
               'observaciones' => 'string|nullable|max:128',
   
               'estado' => 'boolean|required',
            ],
            $msgErr = [ 
               'tipo_identificacion_id.exists' => 'El tipo de indentificacion seleccionado no existe o está en estado inactivo',
               'departamento_id.exists' => 'El departamento seleccionado no existe o está en estado inactivo',
               'ciudad_id.exists' => 'La ciudad seleccionada no existe o está en estado inactivo', 
            ] 
         );
         if ($retVal->fails())
            return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);
 
         // inserta registro en repositorio personas_asesorias
         $regCre = PersonaAsesoria::modificarOCrear($datos);
         if ($regCre) {
            DB::commit(); // Se cierra la transaccion correctamente
            return response(get_response_body(["Asesoria a persona, ha sido creado.", 2], $regCre), Response::HTTP_CREATED);
         }
         else {
            DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
            return response(get_response_body(["Error al crear Asesoria a persona."]), Response::HTTP_CONFLICT);
         }
      }
      catch (Exception $e) {
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
      try { 
         $datos['id'] = $id;
   
         // verifica la existencia del id de registro en el repositorio personas_asesorias
         $retVal = Validator::make(
            $datos, 
            [  'id' => 
               'integer|required|exists:personas_asesorias,id'
            ]
         );
         if ($retVal->fails())
            return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);
   
         // captura y retorna el detalle de registro del repositorio personas_asesorias
         return response(PersonaAsesoria::cargar($id), Response::HTTP_OK);
      }
      catch (Exception $e) {
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
      try { 
         $datos = $request->all();
         $datos['id'] = $id;
 
         // verifica la existencia del id de registro y realiza validaciones a los campos para actualizar el repositorio personas_asesorias
         $retVal = Validator::make( 
            $datos, 
            [  
               'id' => 'integer|required|exists:personas_asesorias,id',
 
               'tipo_identificacion_id' => 
                  [  'integer', 
                     'required', 
                     Rule::exists('tipos_identificacion','id') ->
                        where(function ($query) { 
                           $query -> where('tipIdeEstado', 1); 
                        }), 
                  ],
   
               'numero_documento' => 'string|required|max:128',
 
               'nombre' => 'string|required|max:128',
 
               'telefono' => 'string|nullable|max:128',
 
               'celular' => 'string|nullable|max:128',
 
               'direccion' => 'string|nullable|max:128',
 
               'departamento_id' => 
                  [  'integer', 
                     'nullable', 
                     Rule::exists('departamentos','id') ->
                        where(function ($query) { 
                           $query -> where('departamentosEstado', 1); 
                        }), 
                  ],
 
               'ciudad_id' => 
                  [  'integer', 
                     'nullable', 
                     Rule::exists('ciudades','id') ->
                        where(function ($query) { 
                           $query -> where('ciudadesEstado', 1); 
                        }), 
                  ],
  
               'observaciones' => 'string|nullable|max:128',
 
               'estado' => 'boolean|required',
            ],
            $msgErr = [ 
               'tipo_identificacion_id.exists' => 'El tipo de indentificación seleccionado no existe o está en estado inactivo',
               'departamento_id.exists' => 'El departamento seleccionado no existe o está en estado inactivo',
               'ciudad_id.exists' => 'La ciudad seleccionada no existe o está en estado inactivo', 
            ] 
         );
         if ($retVal->fails())
            return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);
 
         // actualiza/modifica registro en repositorio personas_asesorias
         $regMod = PersonaAsesoria::modificarOCrear($datos);
         if ($regMod) {
            DB::commit(); // Se cierra la transaccion correctamente
            return response(get_response_body(["PersonaAsesoria, ha sido modificado.", 1], $regMod), Response::HTTP_OK);
         }
         else {
            DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
            return response(get_response_body(["Error al modificar la Asesoria a persona."]), Response::HTTP_CONFLICT);;
         }
      }
      catch (Exception $e) {
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
      try { 
         $datos['id'] = $id;
 
         // verifica la existencia del id de registro en el repositorio personas_asesorias
         $retVal = Validator::make(
            $datos, 
            [  'id' => 
               'integer|required|exists:personas_asesorias,id'
            ] );
         if ($retVal->fails())
            return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);
 
         // elimina registro en repositorio personas_asesorias
         $regEli = PersonaAsesoria::eliminar($id);
         if ($regEli) {
            DB::commit(); // Se cierra la transaccion correctamente
            return response(get_response_body(["Asesoria a persona, ha sido eliminado.", 3]), Response::HTTP_OK);
         }
         else {
            DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
            return response(get_response_body(["Error al eliminar el Asesoria a persona."]), Response::HTTP_CONFLICT);
         }
      }
      catch (Exception $e) {
         DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
         return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   }
}
