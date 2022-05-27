<?php

namespace App\Http\Controllers\Proyectos;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Proyectos\PlanAmortizacion;

class PlanAmortizacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $proyecto_id)
    {
        try{
            $datos = $request->all();
            $datos['proyecto_id'] = $proyecto_id;
            if(!$request->ligera){
                $validator = Validator::make($datos, [
                    'limite' => 'integer|between:1,500',
                    'proyecto_id' => 'integer|required|exists:proyectos,id'
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
            $planAmortizacion = PlanAmortizacion::obtenerColeccion($datos);
            return response($planAmortizacion, Response::HTTP_OK);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Proyectos\PlanAmortizacion  $planAmortizacion
     * @return \Illuminate\Http\Response
     */
    public function show(PlanAmortizacion $planAmortizacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proyectos\PlanAmortizacion  $planAmortizacion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PlanAmortizacion $planAmortizacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Proyectos\PlanAmortizacion  $planAmortizacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(PlanAmortizacion $planAmortizacion)
    {
        //
    }
}
