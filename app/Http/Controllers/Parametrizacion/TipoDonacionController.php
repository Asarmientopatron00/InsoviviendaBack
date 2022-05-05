<?php

namespace App\Http\Controllers\Parametrizacion;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Parametrizacion\TipoDonacion;

class TipoDonacionController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @param Request $request
    * @return Response
    */
   public function index(Request $request)
   {
       try {
           $datos = $request->all();

           // Valida entrada de parametros a la funcion
           if (!$request->ligera) {
               $validator = Validator::make($datos, ['limite' => 'integer|between:1,500']);
               if ($validator->fails())
                   return response(get_response_body(format_messages_validator($validator)), Response::HTTP_BAD_REQUEST);
           }

           if ($request->ligera)
               $tipDonDescr = TipoDonacion::obtenerColeccionLigera($datos);
           else {
               if (isset($datos['ordenar_por']))
                   $datos['ordenar_por'] = format_order_by_attributes($datos);
               $tipDonDescr = TipoDonacion::obtenerColeccion($datos);
           }

           return response($tipDonDescr, Response::HTTP_OK);
       }
       catch(Exception $e)
       {
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
       DB::beginTransaction(); // Se abre la transaccion
       try {
           $datos = $request->all();
           $validator = Validator::make($datos, ['tipDonDescripcion' => 'string|required|max:128',
                                                 'tipDonEstado' => 'boolean|required']);

           if ($validator->fails())
               return response(get_response_body(format_messages_validator($validator)), Response::HTTP_BAD_REQUEST);

           $tipDonDescr = TipoDonacion::modificarOCrear($datos);

           if ($tipDonDescr) {
               DB::commit(); // Se cierra la transaccion correctamente
               return response(get_response_body(["Tipo de donación, ha sido creado.", 2], $tipDonDescr), Response::HTTP_CREATED);
           }
           else {
               DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
               return response(get_response_body(["Error al crear Tipo de donación."]), Response::HTTP_CONFLICT);
           }
       }
       catch (Exception $e)
       {
           DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
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
       try {
           $datos['id'] = $id;
           $validator = Validator::make($datos, ['id' => 'integer|required|exists:tipos_donacion,id']);

           if ($validator->fails())
               return response(get_response_body(format_messages_validator($validator)), Response::HTTP_BAD_REQUEST);

           return response(TipoDonacion::cargar($id), Response::HTTP_OK);
       }
       catch (Exception $e)
       {
           return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
       }
   }

   /**
    * Show the form for editing the specified resource.
    *
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
           $validator = Validator::make($datos, ['id' => 'integer|required|exists:tipos_donacion,id',
                                                 'tipDonDescripcion' => 'string|required|max:128',
                                                 'tipDonEstado' => 'boolean|required']);

           if ($validator->fails())
               return response(get_response_body(format_messages_validator($validator)), Response::HTTP_BAD_REQUEST);

           $tipDonDescr = TipoDonacion::modificarOCrear($datos);
           if ($tipDonDescr) {
               DB::commit(); // Se cierra la transaccion correctamente
               return response(get_response_body(["Tipo de donación, ha sido modificado.", 1], $tipDonDescr), Response::HTTP_OK);
           }
           else {
               DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
               return response(get_response_body(["Error al modificar el Tipo de donación."]), Response::HTTP_CONFLICT);;
           }
       }
       catch (Exception $e)
       {
           DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
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
       DB::beginTransaction(); // Se abre la transaccion
       try {
           $datos['id'] = $id;
           $validator = Validator::make($datos, ['id' => 'integer|required|exists:tipos_donacion,id']);

           if ($validator->fails())
               return response(get_response_body(format_messages_validator($validator)), Response::HTTP_BAD_REQUEST);

           $eliminado = TipoDonacion::eliminar($id);
           if ($eliminado){
               DB::commit(); // Se cierra la transaccion correctamente
               return response(get_response_body(["Tipo de donación, ha sido eliminado.", 3]), Response::HTTP_OK);
           }
           else {
               DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
               return response(get_response_body(["Error al eliminar el Tipo de donación."]), Response::HTTP_CONFLICT);
           }
       }
       catch (Exception $e)
       {
           DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
           return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
       }
   }
}
