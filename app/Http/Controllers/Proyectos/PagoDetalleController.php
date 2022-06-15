<?php

namespace App\Http\Controllers\Proyectos;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Proyectos\PagoDetalle;
use Illuminate\Support\Facades\Validator;

class PagoDetalleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $pago_id)
    {
        try{
            $datos = $request->all();
            $datos['pago_id'] = $pago_id;
            if(!$request->ligera){
                $validator = Validator::make($datos, [
                    'limite' => 'integer|between:1,500',
                    'pago_id' => 'integer|required|exists:pagos,id'
                ]);

                if($validator->fails()) {
                    return response(
                        get_response_body(format_messages_validator($validator))
                        , Response::HTTP_BAD_REQUEST
                    );
                }
            }

            if($request->ligera){
                $pago = PagoDetalle::obtenerColeccionLigera($datos);
            }else{
                if(isset($datos['ordenar_por'])){
                    $datos['ordenar_por'] = format_order_by_attributes($datos);
                }
                $pago = PagoDetalle::obtenerColeccion($datos);
            }
            return response($pago, Response::HTTP_OK);
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
     * @param  \App\Models\Proyectos\PagoDetalle  $pagoDetalle
     * @return \Illuminate\Http\Response
     */
    public function show(PagoDetalle $pagoDetalle)
    {
        //
    }
}
