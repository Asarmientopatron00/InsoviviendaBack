<?php

namespace App\Models\Proyectos;

use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanAmortizacion extends Model
{
    protected $table = 'plan_amortizacion'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'proyecto_id',
        'plaAmoNumeroCuota',
        'plaAmoFechaVencimientoCuota',
        'plaAmoValorSaldoCapital',
        'plaAmoValorCapitalCuota',
        'plaAmoValorInteresCuota',
        'plaAmoValorSeguroCuota',
        'plaAmoValorInteresMora',
        'plaAmoDiasMora',
        'plaAmoFechaUltimoPagoCuota',
        'plaAmoCuotaCancelada',
        'plaAmoEstadoPlanAmortizacion',
        'plaAmoEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function proyecto(){
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('plan_amortizacion')
            ->join('proyectos', 'proyectos.id', 'plan_amortizacion.proyecto_id')
            ->join('personas', 'personas.id', 'proyectos.persona_id')
            ->select(
                'plan_amortizacion.id',
                'proyectos.id as numero_proyecto',
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(personas.personasNombres), ''),
                        IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
                        IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
                        )
                    AS solicitante"
                ),
                'plan_amortizacion.plaAmoNumeroCuota',
                'plan_amortizacion.plaAmoFechaVencimientoCuota',
                'plan_amortizacion.plaAmoValorSaldoCapital',
                'plan_amortizacion.plaAmoValorCapitalCuota',
                'plan_amortizacion.plaAmoValorInteresCuota',
                'plan_amortizacion.plaAmoValorSeguroCuota',
                'plan_amortizacion.plaAmoValorInteresMora',
                'plan_amortizacion.plaAmoDiasMora',
                'plan_amortizacion.plaAmoFechaUltimoPagoCuota',
                'plan_amortizacion.plaAmoCuotaCancelada',
                'plan_amortizacion.plaAmoEstadoPlanAmortizacion',
                'plan_amortizacion.plaAmoEstado',
                'plan_amortizacion.usuario_creacion_id',
                'plan_amortizacion.usuario_creacion_nombre',
                'plan_amortizacion.usuario_modificacion_id',
                'plan_amortizacion.usuario_modificacion_nombre',
                'plan_amortizacion.created_at AS fecha_creacion',
                'plan_amortizacion.updated_at AS fecha_modificacion',
            )
            ->where('proyectos.id', $dto['proyecto_id']);

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'numero_proyecto'){
                    $query->orderBy('proyectos.id', $value);
                }
                if($attribute == 'solicitante'){
                    $query->orderBy('personas.personasNombres', $value);
                }
                if($attribute == 'plaAmoNumeroCuota'){
                    $query->orderBy('plan_amortizacion.plaAmoNumeroCuota', $value);
                }
                if($attribute == 'plaAmoFechaVencimientoCuota'){
                    $query->orderBy('plan_amortizacion.plaAmoFechaVencimientoCuota', $value);
                }
                if($attribute == 'plaAmoValorSaldoCapital'){
                    $query->orderBy('plan_amortizacion.plaAmoValorSaldoCapital', $value);
                }
                if($attribute == 'plaAmoValorCapitalCuota'){
                    $query->orderBy('plan_amortizacion.plaAmoValorCapitalCuota', $value);
                }
                if($attribute == 'plaAmoValorInteresCuota'){
                    $query->orderBy('plan_amortizacion.plaAmoValorInteresCuota', $value);
                }
                if($attribute == 'plaAmoValorSeguroCuota'){
                    $query->orderBy('plan_amortizacion.plaAmoValorSeguroCuota', $value);
                }
                if($attribute == 'plaAmoValorInteresMora'){
                    $query->orderBy('plan_amortizacion.plaAmoValorInteresMora', $value);
                }
                if($attribute == 'plaAmoDiasMora'){
                    $query->orderBy('plan_amortizacion.plaAmoDiasMora', $value);
                }
                if($attribute == 'plaAmoFechaUltimoPagoCuota'){
                    $query->orderBy('plan_amortizacion.plaAmoFechaUltimoPagoCuota', $value);
                }
                if($attribute == 'plaAmoCuotaCancelada'){
                    $query->orderBy('plan_amortizacion.plaAmoCuotaCancelada', $value);
                }
                if($attribute == 'plaAmoEstadoPlanAmortizacion'){
                    $query->orderBy('plan_amortizacion.plaAmoEstadoPlanAmortizacion', $value);
                }
                if($attribute == 'plaAmoEstado'){
                    $query->orderBy('plan_amortizacion.plaAmoEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('plan_amortizacion.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('plan_amortizacion.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('plan_amortizacion.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('plan_amortizacion.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("plan_amortizacion.updated_at", "desc");
        }

        $planAmortizacion = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($planAmortizacion ?? [] as $data){
            array_push($datos, $data);
        }

        $cantidadPlanAmortizacion = count($planAmortizacion);
        $to = isset($planAmortizacion) && $cantidadPlanAmortizacion > 0 ? $planAmortizacion->currentPage() * $planAmortizacion->perPage() : null;
        $to = isset($to) && isset($planAmortizacion) && $to > $planAmortizacion->total() && $cantidadPlanAmortizacion > 0 ? $planAmortizacion->total() : $to;
        $from = isset($to) && isset($planAmortizacion) && $cantidadPlanAmortizacion > 0 ?
            ( $planAmortizacion->perPage() > $to ? 1 : ($to - $cantidadPlanAmortizacion) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($planAmortizacion) && $cantidadPlanAmortizacion > 0 ? +$planAmortizacion->perPage() : 0,
            'pagina_actual' => isset($planAmortizacion) && $cantidadPlanAmortizacion > 0 ? $planAmortizacion->currentPage() : 1,
            'ultima_pagina' => isset($planAmortizacion) && $cantidadPlanAmortizacion > 0 ? $planAmortizacion->lastPage() : 0,
            'total' => isset($planAmortizacion) && $cantidadPlanAmortizacion > 0 ? $planAmortizacion->total() : 0
        ];
    }

    public static function calcularPlan($params){
        $numeroProyecto = $params['numero_proyecto'];
        $tipoPlan = $params['tipo_plan'];
        $planDef = $params['plan_def'];
        $transaccion = 'CalcularPlanAmortizacion';
        $usuario = $params['usuario_nombre'];
        $usuarioId = $params['usuario_id'];
        $procedure = DB::select(
            'CALL SP_PlanAmortizacionGenerar(?,?,?,?,?,?)', 
            array(
                $numeroProyecto,
                $tipoPlan,
                $planDef,
                $transaccion,
                $usuarioId,
                $usuario
            ));
    }

    use HasFactory;
}
