<?php

/**
 * Controlador para comunicación entre la vista y el modelo de la funcionalidad de Donaciones.
 * @author  ASSIS S.A.S
 *          Jose Alejandro Gutierrez B
 * @version 20/05/2022/A
 */

namespace App\Http\Controllers\Proyectos;

use PDF;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Proyectos\Pago;
use Illuminate\Validation\Rule;
use App\Models\Proyectos\Donacion;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Exports\Proyectos\DonacionesExport;
use App\Models\Parametrizacion\ParametroConstante;

class DonacionController extends Controller
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
               [  'limite'  
                     =>'integer|between:1,500'
               ]
            );
            if ($retVal->fails())
               return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);
         }

         // captura lista de registros de repositorio donaciones
         if ($request->ligera)
            $retLista = Donacion::obtenerColeccionLigera($datos);
         else {
            if (isset($datos['ordenar_por']))
               $datos['ordenar_por'] = format_order_by_attributes($datos);
            $retLista = Donacion::obtenerColeccion($datos);
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

         // realiza validaciones generales de datos para el repositorio donaciones
         $retVal = Validator::make($datos, [
            'persona_id' => [
               'integer', 
               'nullable', 
               Rule::exists('personas','id')-> 
                  where(function ($query) { 
                     $query->where('personasEstadoRegistro', 'AC'); 
               }), 
            ],
            'donacionesFechaDonacion' => 'date|nullable',
            'tipo_donacion_id' => [
               'integer', 
               'required', 
               Rule::exists('tipos_donacion','id')->
                  where(function ($query) { 
                     $query->where('tipDonEstado', 1); 
               }), 
            ],
            'donacionesValorDonacion' => 'numeric|required',
            'donacionesEstadoDonacion' => 'string|required|max:2',
            'forma_pago_id' => [
               'integer', 
               'required', 
               Rule::exists('formas_pago','id') ->
                  where(function ($query) { 
                     $query->where('forPagEstado', 1); 
               }), 
            ],
            'donacionesNumeroCheque' => 'string|nullable|max:128',
            'banco_id' => [
               'integer', 
               'nullable', 
               Rule::exists('bancos','id') ->
                  where(function ($query) { 
                     $query->where('bancosEstado', 1); 
               }), 
            ],
            // 'donacionesNumeroRecibo' => 'string|required|max:128',
            'donacionesNumeroDocumentoTercero' => 'string|required|max:128',
            'donacionesNombreTercero' => 'string|required|max:128',
            'donacionesFechaRecibo' => 'date|required',
            'donacionesNotas' => 'string|required|max:512',
            'estado' => 'boolean|required',
         ], $msgErr = [ 
            'persona_id.exists' => 'La persona no existe o está en estado inactivo',
            'tipo_donacion_id.exists' => 'El tipo de donacion seleccionado no existe o está en estado inactivo',
            'forma_pago_id.exists' => 'La forma d pago seleccionada no existe o está en estado inactivo', 
            'banco_id.exists' => 'El banco seleccionado no existe o está en estado inactivo', 
         ]);

         if ($retVal->fails())
            return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

         $tipoDonacionOtros = ParametroConstante::where('CODIGO_PARAMETRO', 'ID_TIPO_DONACION_OTROS_INGRESOS')->first();
         if(!$tipoDonacionOtros){
            return response('Faltan parámetros por definir', Response::HTTP_BAD_REQUEST);
         }

         // inserta registro en repositorio donaciones
         $regCre = Donacion::modificarOCrear($datos);
         if ($regCre) {
            DB::commit(); // Se cierra la transaccion correctamente
            return response(get_response_body(["Donación, ha sido creado.", 2], $regCre), Response::HTTP_CREATED);
         }
         else {
            DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
            return response(get_response_body(["Error al crear Donación."]), Response::HTTP_CONFLICT);
         }
      }
      catch (Exception $e) {
         DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
         return response($e, Response::HTTP_INTERNAL_SERVER_ERROR);
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

         // verifica la existencia del id de registro en el repositorio donaciones
         $retVal = Validator::make(
            $datos, 
            [  'id'  
                  =>'integer|required|exists:donaciones,id'
            ]
         );
         if ($retVal->fails())
            return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

         // captura y retorna el detalle de registro del repositorio donaciones
         return response(Donacion::cargar($id), Response::HTTP_OK);
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

         // verifica la existencia del id de registro y realiza validaciones a los campos para actualizar el repositorio donaciones
         $retVal = Validator::make($datos, [
            'id' => 'integer|required|exists:donaciones,id',
            'persona_id' => [
               'integer', 
               'nullable', 
               Rule::exists('personas','id')-> 
                  where(function ($query) { 
                     $query->where('personasEstadoRegistro', 'AC'); 
               }), 
            ],
            'donacionesFechaDonacion' => 'date|nullable',
            'tipo_donacion_id' => [
               'integer', 
               'required', 
               Rule::exists('tipos_donacion','id')->
                  where(function ($query) { 
                     $query->where('tipDonEstado', 1); 
               }), 
            ],
            'donacionesValorDonacion' => 'numeric|required',
            'donacionesEstadoDonacion' => 'string|required|max:2',
            'forma_pago_id' => [
               'integer', 
               'required', 
               Rule::exists('formas_pago','id') ->
                  where(function ($query) { 
                     $query->where('forPagEstado', 1); 
               }), 
            ],
            'donacionesNumeroCheque' => 'string|nullable|max:128',
            'banco_id' => [
               'integer', 
               'nullable', 
               Rule::exists('bancos','id') ->
                  where(function ($query) { 
                     $query->where('bancosEstado', 1); 
               }), 
            ],
            // 'donacionesNumeroRecibo' => 'string|required|max:128',
            'donacionesNumeroDocumentoTercero' => 'string|required|max:128',
            'donacionesNombreTercero' => 'string|required|max:128',
            'donacionesFechaRecibo' => 'date|required',
            'donacionesNotas' => 'string|required|max:512',
            'estado' => 'boolean|required',
         ], $msgErr = [ 
            'persona_id.exists' => 'La persona no existe o está en estado inactivo',
            'tipo_donacion_id.exists' => 'El tipo de donacion seleccionado no existe o está en estado inactivo',
            'forma_pago_id.exists' => 'La forma d pago seleccionada no existe o está en estado inactivo', 
            'banco_id.exists' => 'El banco seleccionado no existe o está en estado inactivo', 
         ]);

         if ($retVal->fails())
            return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

         $tipoDonacionOtros = ParametroConstante::where('CODIGO_PARAMETRO', 'ID_TIPO_DONACION_OTROS_INGRESOS')->first();
         if(!$tipoDonacionOtros){
            return response('Faltan parámetros por definir', Response::HTTP_BAD_REQUEST);
         }

         // actualiza/modifica registro en repositorio donaciones
         $regMod = Donacion::modificarOCrear($datos);
         if ($regMod) {
            DB::commit(); // Se cierra la transaccion correctamente
            return response(get_response_body(["Donación, ha sido modificado.", 1], $regMod), Response::HTTP_OK);
         }
         else {
            DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
            return response(get_response_body(["Error al modificar el Donación."]), Response::HTTP_CONFLICT);;
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

         // verifica la existencia del id de registro en el repositorio donaciones
         $retVal = Validator::make(
            $datos, 
            [  'id'  
                  =>'integer|required|exists:donaciones,id'
            ]
         );
         if ($retVal->fails())
            return response(get_response_body(format_messages_validator($retVal)), Response::HTTP_BAD_REQUEST);

         // elimina registro en repositorio donaciones
         $regEli = Donacion::eliminar($id);
         if ($regEli){
            DB::commit(); // Se cierra la transaccion correctamente
            return response(get_response_body(["Donación, ha sido eliminado.", 3]), Response::HTTP_OK);
         }
         else {
            DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
            return response(get_response_body(["Error al eliminar el Donación."]), Response::HTTP_CONFLICT);
         }
      }
      catch (Exception $e) {
         DB::rollback(); // Se devuelven los cambios, por que la transaccion falla
         return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   }

   public function listaDonaciones(Request $request){
      $nombreArchivo = 'donaciones-otros-ingresos-' . time() . '.xlsx';
      return (new DonacionesExport($request->all()))->download($nombreArchivo);
   }

   public function recibo(Request $request, $id){
      $donacion = Donacion::find($id);
      if(!$donacion){
          return;
      }
      $numberToWord = Pago::numberToWord($donacion->donacionesValorDonacion);
      $registro = (object)[];
      $registro->consecutivo = $donacion->donacionesNumeroRecibo;
      $registro->valor = $donacion->donacionesValorDonacion;
      $registro->persona = $donacion->donacionesNombreTercero;
      $registro->identificacion = $donacion->donacionesNumeroDocumentoTercero;
      $registro->concepto = $donacion->donacionesNotas;
      $registro->banco = $donacion->banco->bancosDescripcion;
      $registro->fechaC = $donacion->donacionesFechaDonacion;
      $registro->fechaE = $donacion->donacionesFechaRecibo;
      $registro->elaboradoPor = $donacion->usuario_creacion_nombre;
      $registro->estado = $donacion->estado;
      $registro->capital = '';
      $registro->interesCuota = '';
      $registro->interesMora = '';
      $registro->interesTotal = '';
      $registro->seguro = '';
      $registro->cartera = '';
      $registro->numberToWord = $numberToWord;
      $registro->fecha = Carbon::now();
      $pdf = PDF::loadView('factura', compact(['registro']));
      return $pdf->download('recibo-de-caja-'.$registro->consecutivo.'-'.time().'.pdf');
   }
}
