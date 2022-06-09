<?php

namespace App\Http\Controllers\Proyectos;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exports\Proyectos\PlanAmortizacionDefinitivoExport;
use App\Http\Controllers\Controller;
use App\Models\Proyectos\PlanAmortizacionDefinitivo;
use Illuminate\Support\Facades\Validator;

class PlanAmortizacionDefinitivoController extends Controller
{
   /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
   public function index(Request $request, $proyecto_id)
   {
      try {
         $datos = $request->all();
         $datos['proyecto_id'] = $proyecto_id;
         if (!$request->ligera) {
            $validator = Validator::make($datos, [
               'limite' => 'integer|between:1,500',
               'proyecto_id' => 'integer|required|exists:proyectos,id'
            ]);

            if ($validator->fails()) 
               return response(
                  get_response_body(format_messages_validator($validator))
                     , Response::HTTP_BAD_REQUEST
               );            
         }
         if (isset($datos['ordenar_por']))
            $datos['ordenar_por'] = format_order_by_attributes($datos);
         
         if ($request->headerInfo)
            $planAmortizacionDefinitivo = PlanAmortizacionDefinitivo::getHeaders($proyecto_id);
         else
            $planAmortizacionDefinitivo = PlanAmortizacionDefinitivo::obtenerColeccion($datos);

         return response($planAmortizacionDefinitivo, Response::HTTP_OK);
      }
      catch(Exception $e) {
         return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
   }

   public function descargaPlanAmortizacionDefinitivo(Request $request)
   {
       $nombreArchivo = 'PlanAmortizacionDefinitivo-' . time() . '.xlsx';
       return (new PlanAmortizacionDefinitivoExport($request->all()))->download($nombreArchivo);
   }
}
