<?php

namespace App\Http\Controllers\Procesos;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Procesos\ProcesoMora;
use App\Models\Parametrizacion\ParametroConstante;

class ProcesoMoraController extends Controller
{
    /**
     * executing sp.
     *
     * @return \Illuminate\Http\Response
     */
    public function calculoProcesoMora(Request $request)
    {
        DB::beginTransaction(); // Se abre la transacción
        try{
            $datos = $request->all();
            $ultimaEjecucion = ParametroConstante::where('codigo_parametro', 'FECHA_ULTIMA_EJECUCION_CALCULO_MORA')->first();
            $boolean = $ultimaEjecucion->valor_parametro == Carbon::now()->format('Y-m-d');
            if(!$boolean){
                $proceso = ProcesoMora::calcularMora($datos);
                DB::commit(); // Se cierra la transacción correctamente
                return response(
                    get_response_body(["El proceso del cálculo de mora ha sido ejecutado correctamente.", 1], $proceso),
                    Response::HTTP_OK
                );
            } else {
                DB::rollback(); // Se devuelven los cambios, por que la transacción falla
                return response(get_response_body(["Ya se ejecutó el cálculo de mora el día de hoy"]), Response::HTTP_CONFLICT);;
            }
        }catch (Exception $e){
            DB::rollback(); // Se devuelven los cambios, por que la transacción falla
            return response(get_response_body([$e->getMessage()]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
