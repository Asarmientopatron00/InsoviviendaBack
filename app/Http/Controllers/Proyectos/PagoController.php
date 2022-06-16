<?php

namespace App\Http\Controllers\Proyectos;

use PDF;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Proyectos\Pago;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PagoController extends Controller
{
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
                $pago = Pago::obtenerColeccionLigera($datos);
            }else{
                if(isset($datos['ordenar_por'])){
                    $datos['ordenar_por'] = format_order_by_attributes($datos);
                }
                $pago = Pago::obtenerColeccion($datos);
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
        DB::beginTransaction(); // Se abre la transacción
        try {
            $datos = $request->all();
            $validator = Validator::make($datos, [
                'proyecto_id' => [
                    'integer',
                    'required',
                    Rule::exists('proyectos','id')->where(function ($query) {
                        $query->whereNot('proyectosEstadoProyecto', 'CAN')
                            ->whereNot('proyectosEstadoProyecto', 'CON')
                            ->whereNot('proyectosEstadoProyecto', 'REC');
                    }),
                ],
                'pagosFechaPago' => 'date|required',
                'pagosValorTotalPago' => 'numeric|required',
                'pagosDescripcionPago' => 'string|required',
                'pagosEstado' => 'boolean|required',
            ], $messages = [
                'proyecto_id.exists'=>'El proyecto seleccionado no existe, fue cancelado, rechazado o está congelado',
            ]);

            if ($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $pago = Pago::modificarOCrear($datos);
            
            if ($pago) {
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El pago ha sido creado.", 2], $pago),
                    Response::HTTP_CREATED
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar crear el pago."]), Response::HTTP_CONFLICT);
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response($e, Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'id' => 'integer|required|exists:pagos,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            return response(Pago::cargar($id), Response::HTTP_OK);
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
                'id' => 'integer|required|exists:pagos,id',
                'proyecto_id' => [
                    'integer',
                    'required',
                    Rule::exists('proyectos','id')->where(function ($query) {
                        $query->whereNot('proyectosEstadoProyecto', 'CAN')
                            ->whereNot('proyectosEstadoProyecto', 'CON')
                            ->whereNot('proyectosEstadoProyecto', 'REC');
                    }),
                ],
                'pagosFechaPago' => 'date|required',
                'pagosValorTotalPago' => 'numeric|required',
                'pagosDescripcionPago' => 'string|required',
                'pagosEstado' => 'boolean|required',
            ], $messages = [
                'proyecto_id.exists'=>'El proyecto seleccionado no existe, fue cancelado, rechazado o está congelado',
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $pago = Pago::modificarOCrear($datos);
            if($pago){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El pago ha sido modificado.", 1], $pago),
                    Response::HTTP_OK
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar modificar el pago."]), Response::HTTP_CONFLICT);;
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(get_response_body([$e->getMessage()]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reversar(Request $request, $id)
    {
        DB::beginTransaction(); // Se abre la transacción
        try{
            $datos = $request->all();
            $datos['id'] = $id;
            $validator = Validator::make($datos, [
                'id' => [
                    'integer',
                    'required',
                    Rule::exists('pagos','id')->where(function ($query) {
                        $query->where('pagosEstado', 1);
                    }),
                ],
            ], $messages = [
                'id.exists'=>'El pago seleccionado no existe o ya ha sido reversado',
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body([format_messages_validator($validator), 4])
                    , Response::HTTP_BAD_REQUEST
                );
            }
            $datos['reversar'] = true;
            $pago = Pago::pagosReversar($datos);
            if($pago){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El pago ha sido reversado.", 3], $pago),
                    Response::HTTP_OK
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar reversar el pago."]), Response::HTTP_CONFLICT);;
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
                'id' => 'integer|required|exists:pagos,id'
            ]);

            if($validator->fails()) {
                return response(
                    get_response_body(format_messages_validator($validator))
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $eliminado = Pago::eliminar($id);
            if($eliminado){
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El pago ha sido elimado.", 3]),
                    Response::HTTP_OK
                );
            }else{
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ocurrió un error al intentar eliminar el pago."]), Response::HTTP_CONFLICT);
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function factura(Request $request, $id){
        $pago = Pago::find($id);
        if(!$pago || $pago->pagosEstado === 0){
            return;
        }
        $numberToWord = Pago::numberToWord($pago->pagosValorTotalPago);
        $pagosDetalle = $pago->pagosDetalle;
        $totales = (object)[];
        $totales->capital = 0;
        $totales->interesCuota = 0;
        $totales->interesMora = 0;
        $totales->seguro = 0;
        $totales->fecha = Carbon::now();
        $totales->cartera = $pago->pagosSaldoDespPago;
        foreach($pagosDetalle as $pagoDetalle){
            $totales->capital = $totales->capital + $pagoDetalle->pagDetValorCapitalCuotaPagado + $pagoDetalle->pagDetValorSaldoCuotaPagado; 
            $totales->interesCuota = $totales->interesCuota + $pagoDetalle->pagDetValorInteresCuotaPagado; 
            $totales->interesMora = $totales->interesMora + $pagoDetalle->pagDetValorInteresMoraPagado; 
            $totales->seguro = $totales->seguro + $pagoDetalle->pagDetValorSeguroCuotaPagado; 
        }
        $pdf = PDF::loadView('factura', compact(['pago', 'numberToWord', 'totales']));
        return $pdf->download('recibo-de-caja-'.$pago->pagosConsecutivo.'-'.time().'.pdf');
        // return $pdf->stream('recibo-de-caja-'.$pago->id.'-'.time().'.pdf');
    }
}
