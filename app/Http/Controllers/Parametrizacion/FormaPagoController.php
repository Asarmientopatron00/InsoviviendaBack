<?php

/**
 * Controlador para comunicación entre la vista y el modelo de la funcionalidad de Forma de pago.
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
use App\Models\Parametrizacion\FormaPago;

class FormaPagoController extends Controller
{
   /**
    * Presenta un listado con la informaci�n de la funcionalidad.
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

           // captura lista de registros de repositorio formas_pago
           if ($request->ligera)
               $retLista = FormaPago::obtenerColeccionLigera($datos);
           else {
               if (isset($datos['ordenar_por']))
                   $datos['ordenar_por'] = format_order_by_attributes($datos);
               $retLista = FormaPago::obtenerColeccion($datos);
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
       DB::beginTransaction(); // Se abre la transacción
       try {
           // realiza validaciones generales de datos para el repositorio formas_pago
           $datos = $request->all();
           $retVal = Validator::make($datos, ['forPagDescripcion' => 'string|required|max:128',
                                              'forPagEstado' => 'boolean|required']);
           if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

           // inserta registro en repositorio formas_pago
           $regCre = FormaPago::modificarOCrear($datos);
           if ($regCre) {
               DB::commit(); // Se cierra la transacción correctamente
               return response(get_response_body(["Forma de pago, ha sido creada.", 2], $regCre), Response::HTTP_CREATED);
            }           
            else {          
               DB::rollback(); // Se devuelven los cambios, por que la transacción falla
               return response(get_response_body(["Error al crear Forma de pago."]), Response::HTTP_CONFLICT);
            }
        }
       catch (Exception $e)
       {
           DB::rollback(); // Se devuelven los cambios, por que la transacción falla
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
           // verifica la existencia del id de registro en el repositorio formas_pago
           $datos['id'] = $id;
           $retVal = Validator::make($datos, ['id' => 'integer|required|exists:formas_pago,id']);
           if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

           // captura y retorna el detalle de registro del repositorio formas_pago
           return response(FormaPago::cargar($id), Response::HTTP_OK);
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
       DB::beginTransaction(); // Se abre la transacción
       try {
           // verifica la existencia del id de registro y realiza validaciones a los campos para actualizar el repositorio formas_pago
           $datos = $request->all();
           $datos['id'] = $id;
           $retVal = Validator::make($datos, ['id' => 'integer|required|exists:formas_pago,id',
                                              'forPagDescripcion' => 'string|required|max:128',
                                              'forPagEstado' => 'boolean|required']);
           if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

           // actualiza/modifica registro en repositorio formas_pago
           $regMod = FormaPago::modificarOCrear($datos);
           if ($regMod) {
               DB::commit(); // Se cierra la transacción correctamente
               return response(get_response_body(["Forma de pago, ha sido modificada.", 1], $regMod), Response::HTTP_OK);
           }           
           else {
               DB::rollback(); // Se devuelven los cambios, por que la transacción falla
               return response(get_response_body(["Error al modificar la Forma de pago."]), Response::HTTP_CONFLICT);;
            }
       }
       catch (Exception $e)
       {
           DB::rollback(); // Se devuelven los cambios, por que la transacción falla
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
       DB::beginTransaction(); // Se abre la transacción
       try {
           // verifica la existencia del id de registro en el repositorio formas_pago
           $datos['id'] = $id;
           $retVal = Validator::make($datos, ['id' => 'integer|required|exists:formas_pago,id']);
           if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

           // elimina registro en repositorio formas_pago
           $regEli = FormaPago::eliminar($id);
           if ($regEli){
               DB::commit(); // Se cierra la transacción correctamente
               return response(get_response_body(["Forma de pago, ha sido eliminada.", 3]), Response::HTTP_OK);
           }
           else {
               DB::rollback(); // Se devuelven los cambios, por que la transacción falla
               return response(get_response_body(["Error al eliminar la Forma de pago."]), Response::HTTP_CONFLICT);
            }
       }
       catch (Exception $e)
       {
           DB::rollback(); // Se devuelven los cambios, por que la transacción falla
           return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
       }
   }   
}
