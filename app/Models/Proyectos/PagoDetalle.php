<?php

namespace App\Models\Proyectos;

use Carbon\Carbon;
use App\Models\Proyectos\Pago;
use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PagoDetalle extends Model
{
    protected $table = 'pagos_detalle'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'proyecto_id',
        'pago_id',
        'pagDetFechaPago',
        'pagDetNumeroCuota',
        'pagDetFechaVencimientoCuota',
        'pagDetValorCapitalCuotaPagado',
        'pagDetValorSaldoCuotaPagado',
        'pagDetValorInteresCuotaPagado',
        'pagDetValorSeguroCuotaPagado',
        'pagDetValorInteresMoraPagado',
        'pagDetDiasMora',
        'pagDetValorInteresMoraCondonado',
        'pagDetValorSeguroCuotaCondonado',
        'pagDetValorInteresCuotaCondonado',
        'pagDetValorCapitalCuotaCondonado',
        'pagDetEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function proyecto(){
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function pago(){
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('pagos_detalle')
            ->join('proyectos', 'pagos.proyecto_id', 'proyectos.id')
            ->select(
                'pagos_detalle.id',
                'proyectos.id AS proyecto_id',
                'proyectos.proyectosFechaSolicitud AS fecha_solicitud',
                'pagos_detalle.pagDetNumeroCuota',
                'pagos_detalle.pagDetFechaPago',
                'pagos_detalle.pagDetEstado AS estado',
            );
        $query->orderBy('pagos_detalle.id', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('pagos_detalle')
            ->join('proyectos', 'pagos_detalle.proyecto_id', 'proyectos.id')
            ->join('personas', 'proyectos.persona_id', 'personas.id')
            ->join('pagos', 'pagos_detalle.pago_id', 'pagos.id')
            ->select(
                'pagos_detalle.id',
                'pagos.id AS pago_id',
                'pagos.pagosFechaPago AS fechaPago',
                'proyectos.id AS proyecto_id',
                'proyectos.proyectosFechaSolicitud AS fechaSolicitud',
                'proyectos.proyectosEstadoProyecto AS estado',
                'personas.personasIdentificacion AS identificacion',
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(personas.personasNombres), ''),
                        IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
                        IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
                        )
                    AS solicitante"
                ),
                'pagos_detalle.pagDetFechaPago',
                'pagos_detalle.pagDetNumeroCuota',
                'pagos_detalle.pagDetFechaVencimientoCuota',
                'pagos_detalle.pagDetValorCapitalCuotaPagado',
                'pagos_detalle.pagDetValorSaldoCuotaPagado',
                'pagos_detalle.pagDetValorInteresCuotaPagado',
                'pagos_detalle.pagDetValorSeguroCuotaPagado',
                'pagos_detalle.pagDetValorInteresMoraPagado',
                'pagos_detalle.pagDetValorInteresMoraCondonado',
                'pagos_detalle.pagDetValorSeguroCuotaCondonado',
                'pagos_detalle.pagDetValorInteresCuotaCondonado',
                'pagos_detalle.pagDetValorCapitalCuotaCondonado',
                'pagos_detalle.pagDetDiasMora',
                'pagos_detalle.pagDetEstado',
                'pagos_detalle.usuario_creacion_id',
                'pagos_detalle.usuario_creacion_nombre',
                'pagos_detalle.usuario_modificacion_id',
                'pagos_detalle.usuario_modificacion_nombre',
                'pagos_detalle.created_at AS fecha_creacion',
                'pagos_detalle.updated_at AS fecha_modificacion',
            )
            ->where('pagos.id', $dto['pago_id']);

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'proyecto_id'){
                    $query->orderBy('pagos_detalle.proyecto_id', $value);
                }
                if($attribute == 'pagDetFechaPago'){
                    $query->orderBy('pagos_detalle.pagDetFechaPago', $value);
                }
                if($attribute == 'pagDetNumeroCuota'){
                    $query->orderBy('pagos_detalle.pagDetNumeroCuota', $value);
                }
                if($attribute == 'pagDetFechaVencimientoCuota'){
                    $query->orderBy('pagos_detalle.pagDetFechaVencimientoCuota', $value);
                }
                if($attribute == 'pagDetValorCapitalCuotaPagado'){
                    $query->orderBy('pagos_detalle.pagDetValorCapitalCuotaPagado', $value);
                }
                if($attribute == 'pagDetValorSaldoCuotaPagado'){
                    $query->orderBy('pagos_detalle.pagDetValorSaldoCuotaPagado', $value);
                }
                if($attribute == 'pagDetValorInteresCuotaPagado'){
                    $query->orderBy('pagos_detalle.pagDetValorInteresCuotaPagado', $value);
                }
                if($attribute == 'pagDetValorInteresMoraCondonado'){
                    $query->orderBy('pagos_detalle.pagDetValorInteresMoraCondonado', $value);
                }
                if($attribute == 'pagDetValorSeguroCuotaCondonado'){
                    $query->orderBy('pagos_detalle.pagDetValorSeguroCuotaCondonado', $value);
                }
                if($attribute == 'pagDetValorInteresCuotaCondonado'){
                    $query->orderBy('pagos_detalle.pagDetValorInteresCuotaCondonado', $value);
                }
                if($attribute == 'pagDetValorCapitalCuotaCondonado'){
                    $query->orderBy('pagos_detalle.pagDetValorCapitalCuotaCondonado', $value);
                }
                if($attribute == 'pagDetValorSeguroCuotaPagado'){
                    $query->orderBy('pagos_detalle.pagDetValorSeguroCuotaPagado', $value);
                }
                if($attribute == 'pagDetValorInteresMoraPagado'){
                    $query->orderBy('pagos_detalle.pagDetValorInteresMoraPagado', $value);
                }
                if($attribute == 'pagDetDiasMora'){
                    $query->orderBy('pagos_detalle.pagDetDiasMora', $value);
                }
                if($attribute == 'pagDetEstado'){
                    $query->orderBy('pagos_detalle.pagDetEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('pagos_detalle.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('pagos_detalle.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('pagos_detalle.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('pagos_detalle.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("pagos_detalle.updated_at", "desc");
        }

        $pagosDetalle = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($pagosDetalle ?? [] as $pagoDetalle){
            array_push($datos, $pagoDetalle);
        }

        $cantidadPagosDetalle = count($pagosDetalle);
        $to = isset($pagosDetalle) && $cantidadPagosDetalle > 0 ? $pagosDetalle->currentPage() * $pagosDetalle->perPage() : null;
        $to = isset($to) && isset($pagosDetalle) && $to > $pagosDetalle->total() && $cantidadPagosDetalle > 0 ? $pagosDetalle->total() : $to;
        $from = isset($to) && isset($pagosDetalle) && $cantidadPagosDetalle > 0 ?
            ( $pagosDetalle->perPage() > $to ? 1 : ($to - $cantidadPagosDetalle) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($pagosDetalle) && $cantidadPagosDetalle > 0 ? +$pagosDetalle->perPage() : 0,
            'pagina_actual' => isset($pagosDetalle) && $cantidadPagosDetalle > 0 ? $pagosDetalle->currentPage() : 1,
            'ultima_pagina' => isset($pagosDetalle) && $cantidadPagosDetalle > 0 ? $pagosDetalle->lastPage() : 0,
            'total' => isset($pagosDetalle) && $cantidadPagosDetalle > 0 ? $pagosDetalle->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $pagoDetalle = PagoDetalle::find($id);
        $proyecto = $pagoDetalle->proyecto;
        return [
            'id' => $pagoDetalle->id,
            'proyecto_id' => $pagoDetalle->proyecto_id,
            'pagDetFechaPago' => $pagoDetalle->pagDetFechaPago,
            'pagDetNumeroCuota' => $pagoDetalle->pagDetNumeroCuota,
            'pagDetFechaVencimientoCuota' => $pagoDetalle->pagDetFechaVencimientoCuota,
            'pagDetValorCapitalCuotaPagado' => $pagoDetalle->pagDetValorCapitalCuotaPagado,
            'pagDetValorSaldoCuotaPagado' => $pagoDetalle->pagDetValorSaldoCuotaPagado,
            'pagDetValorInteresCuotaPagado' => $pagoDetalle->pagDetValorInteresCuotaPagado,
            'pagDetValorSeguroCuotaPagado' => $pagoDetalle->pagDetValorSeguroCuotaPagado,
            'pagDetValorInteresMoraPagado' => $pagoDetalle->pagDetValorInteresMoraPagado,
            'pagDetDiasMora' => $pagoDetalle->pagDetDiasMora,
            'pagDetEstado' => $pagoDetalle->pagDetEstado,
            'usuario_creacion_id' => $pagoDetalle->usuario_creacion_id,
            'usuario_creacion_nombre' => $pagoDetalle->usuario_creacion_nombre,
            'usuario_modificacion_id' => $pagoDetalle->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $pagoDetalle->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($pagoDetalle->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($pagoDetalle->updated_at))->format("Y-m-d H:i:s"),
            'proyecto' => isset($proyecto) ? [
                'id' => $proyecto->id,
            ] : null,
        ];
    }

    use HasFactory;
}
