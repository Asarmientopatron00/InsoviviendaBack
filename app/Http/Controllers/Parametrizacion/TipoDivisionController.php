<?php

namespace App\Http\Controllers\Parametrizacion;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\Parametrizacion\TipoDivision;
use Illuminate\Support\Facades\Validator;

class TipoDivisionController extends Controller
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
               $tipoDivision = TipoDivision::obtenerColeccionLigera($datos);
           }else{
               if(isset($datos['ordenar_por'])){
                   $datos['ordenar_por'] = format_order_by_attributes($datos);
               }
               $tipoDivision = TipoDivision::obtenerColeccion($datos);
           }
           return response($tipoDivision, Response::HTTP_OK);
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
               'tipDivDescripcion' => 'string|required|max:128',
               'tipDivEstado' => 'boolean|required'
           ]);

           if ($validator->fails()) {
               return response(
                   get_response_body(format_messages_validator($validator))
                   , Response::HTTP_BAD_REQUEST
               );
           }

           $tipoDivision = TipoDivision::modificarOCrear($datos);
           
           if ($tipoDivision) {
               DB::commit(); // Se cierra la transacción correctamente
               return response(
                   get_response_body(["El tipo de division ha sido creado.", 2], $tipoDivision),
                   Response::HTTP_CREATED
               );
           } else {
               DB::rollback(); // Se devuelven los cambios, por que la transacción falla
               return response(get_response_body(["Ocurrió un error al intentar crear el tipo de division."]), Response::HTTP_CONFLICT);
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
               'id' => 'integer|required|exists:tipos_division,id'
           ]);

           if($validator->fails()) {
               return response(
                   get_response_body(format_messages_validator($validator))
                   , Response::HTTP_BAD_REQUEST
               );
           }

           return response(TipoDivision::cargar($id), Response::HTTP_OK);
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
               'id' => 'integer|required|exists:tipos_division,id',
               'tipDivDescripcion' => 'string|required|max:128',
               'tipDivEstado' => 'boolean|required'
           ]);

           if($validator->fails()) {
               return response(
                   get_response_body(format_messages_validator($validator))
                   , Response::HTTP_BAD_REQUEST
               );
           }

           $tipoDivision = TipoDivision::modificarOCrear($datos);
           if($tipoDivision){
               DB::commit(); // Se cierra la transacción correctamente
               return response(
                   get_response_body(["El tipo de division ha sido modificado.", 1], $tipoDivision),
                   Response::HTTP_OK
               );
           } else {
               DB::rollback(); // Se devuelven los cambios, por que la transacción falla
               return response(get_response_body(["Ocurrió un error al intentar modificar el tipo de division."]), Response::HTTP_CONFLICT);;
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
               'id' => 'integer|required|exists:tipos_division,id'
           ]);

           if($validator->fails()) {
               return response(
                   get_response_body(format_messages_validator($validator))
                   , Response::HTTP_BAD_REQUEST
               );
           }

           $eliminado = TipoDivision::eliminar($id);
           if($eliminado){
               DB::commit(); // Se cierra la transacción correctamente
               return response(
                   get_response_body(["El tipo de division ha sido elimado.", 3]),
                   Response::HTTP_OK
               );
           }else{
               DB::rollback(); // Se devuelven los cambios, por que la transacción falla
               return response(get_response_body(["Ocurrió un error al intentar eliminar el tipo de division."]), Response::HTTP_CONFLICT);
           }
       }catch (Exception $e){
           DB::rollback(); // Se devuelven los cambios, por que la transacción falla
           return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
       }
   }    
}
