<?php

/**
 * Controlador para comunicación entre la vista y el modelo de la funcionalidad de Tipos de gastos.
 * @author  ASSIS S.A.S
 *          Jose Alejandro Gutierrez B
 * @version 06/05/2022/A
 */

namespace App\Http\Controllers\Parametrizacion;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Parametrizacion\TipoGasto;

class TipoGastoController extends Controller
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
               $retVal = Validator::make($datos, ['limite' => 'integer|between:1,500']);
               if ($retVal->fails())
                   return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);
           }

           // captura lista de registros de repositorio tipos_gasto
           if ($request->ligera)
               $retLista = TipoGasto::obtenerColeccionLigera($datos);
           else {
               if (isset($datos['ordenar_por']))
                   $datos['ordenar_por'] = format_order_by_attributes($datos);
               $retLista = TipoGasto::obtenerColeccion($datos);
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
       try {
           // realiza validaciones generales de datos para el repositorio tipos_gasto
           $datos = $request->all();
           $retVal = Validator::make($datos, ['tipGasDescripcion' => 'string|required|max:128',
                                              'tipGasEstado' => 'boolean|required']);
           if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

           // inserta registro en repositorio tipos_gasto
           $regCre = TipoGasto::modificarOCrear($datos);
           if ($regCre) {
               DB::commit(); // Se cierra la transaccion correctamente
               return response(get_response_body(["Tipo de gasto, ha sido creado.", 2], $regCre), Response::HTTP_CREATED);
           }
           else {
               DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
               return response(get_response_body(["Error al crear el Tipo de gasto."]), Response::HTTP_CONFLICT);
           }
       }
       catch (Exception $e)
       {
           DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
           return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
       }
   }

   /**
    * Presenta la informaci�n de un registro especifico de la funcionalidad.
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function show($id)
   {
       try {
           // verifica la existencia del id de registro en el repositorio tipos_gasto
           $datos['id'] = $id;
           $retVal = Validator::make($datos, ['id' => 'integer|required|exists:tipos_gasto,id']);
           if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

           // captura y retorna el detalle de registro del repositorio tipos_gasto
           return response(TipoGasto::cargar($id), Response::HTTP_OK);
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
       try {
           // verifica la existencia del id de registro y realiza validaciones a los campos para actualizar el repositorio tipos_gasto
           $datos = $request->all();
           $datos['id'] = $id;
           $retVal = Validator::make($datos, ['id' => 'integer|required|exists:tipos_gasto,id',
                                              'tipGasDescripcion' => 'string|required|max:128',
                                              'tipGasEstado' => 'boolean|required']);
           if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

           // actualiza/modifica registro en repositorio tipos_gasto
           $regMod = TipoGasto::modificarOCrear($datos);
           if ($regMod) {
               DB::commit(); // Se cierra la transaccion correctamente
               return response(get_response_body(["Tipo de gasto, ha sido modificado.", 1], $regMod), Response::HTTP_OK);
           }
           else {
               DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
               return response(get_response_body(["Error al modificar el Tipo de gasto."]), Response::HTTP_CONFLICT);;
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
       try {
           // verifica la existencia del id de registro en el repositorio tipos_gasto
           $datos['id'] = $id;
           $retVal = Validator::make($datos, ['id' => 'integer|required|exists:tipos_gasto,id']);
           if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

           // elimina registro en repositorio tipos_gasto
           $regEli = TipoGasto::eliminar($id);
           if ($regEli){
               DB::commit(); // Se cierra la transaccion correctamente
               return response(get_response_body(["Tipo de gasto, ha sido eliminado.", 3]), Response::HTTP_OK);
           }
           else {
               DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
               return response(get_response_body(["Error al eliminar el Tipo de gasto."]), Response::HTTP_CONFLICT);
           }
       }
       catch (Exception $e)
       {
           DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
           return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
       }
   }
}
