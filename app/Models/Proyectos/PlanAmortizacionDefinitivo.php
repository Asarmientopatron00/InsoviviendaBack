<?php

namespace App\Models\Proyectos;

use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanAmortizacionDefinitivo extends Model
{
   protected $table = 'plan_amortizacion_def'; // nombre de la tabla en la base de datos

   protected $fillable = [ // nombres de los campos
      'proyecto_id',
      'plAmDeNumeroCuota',
      'plAmDeFechaVencimientoCuota',
      'plAmDeValorSaldoCapital',
      'plAmDeValorCapitalCuota',
      'plAmDeValorInteresCuota',
      'plAmDeValorSeguroCuota',
      'plAmDeValorInteresMora',
      'plAmDeDiasMora',
      'plAmDeFechaUltimoPagoCuota',
      'plAmDeCuotaCancelada',
      'plAmDeEstadoPlanAmortizacion',
      'plAmDeEstado',
      'usuario_creacion_id',
      'usuario_creacion_nombre',
      'usuario_modificacion_id',
      'usuario_modificacion_nombre',
   ];

   public function proyecto() 
   { 
      return $this->belongsTo(Proyecto::class, 'proyecto_id');
   }

   public static function obtenerColeccion($dto) 
   { 
      $query = DB::table('plan_amortizacion_def')
         ->join('proyectos', 'proyectos.id', 'plan_amortizacion_def.proyecto_id')
         ->join('personas', 'personas.id', 'proyectos.persona_id')
         ->select(
            'plan_amortizacion_def.id',
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
            'plan_amortizacion_def.plAmDeNumeroCuota',
            'plan_amortizacion_def.plAmDeFechaVencimientoCuota',
            'plan_amortizacion_def.plAmDeValorSaldoCapital',
            'plan_amortizacion_def.plAmDeValorCapitalCuota',
            'plan_amortizacion_def.plAmDeValorInteresCuota',
            'plan_amortizacion_def.plAmDeValorSeguroCuota',
            'plan_amortizacion_def.plAmDeValorInteresMora',
            'plan_amortizacion_def.plAmDeDiasMora',
            'plan_amortizacion_def.plAmDeFechaUltimoPagoCuota',
            'plan_amortizacion_def.plAmDeCuotaCancelada',
            'plan_amortizacion_def.plAmDeEstadoPlanAmortizacion',
            'plan_amortizacion_def.plAmDeEstado',
            'plan_amortizacion_def.usuario_creacion_id',
            'plan_amortizacion_def.usuario_creacion_nombre',
            'plan_amortizacion_def.usuario_modificacion_id',
            'plan_amortizacion_def.usuario_modificacion_nombre',
            'plan_amortizacion_def.created_at AS fecha_creacion',
            'plan_amortizacion_def.updated_at AS fecha_modificacion',
         )
         ->where('proyectos.id', $dto['proyecto_id']);

      if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0)  
         foreach ($dto['ordenar_por'] as $attribute => $value) {

            if ($attribute == 'numero_proyecto')  
               $query->orderBy('proyectos.id', $value);

            if ($attribute == 'solicitante')  
               $query->orderBy('personas.personasNombres', $value);

            if ($attribute == 'plAmDeNumeroCuota')  
               $query->orderBy('plan_amortizacion_def.plAmDeNumeroCuota', $value);

            if ($attribute == 'plAmDeFechaVencimientoCuota')  
               $query->orderBy('plan_amortizacion_def.plAmDeFechaVencimientoCuota', $value);

            if ($attribute == 'plAmDeValorSaldoCapital')  
               $query->orderBy('plan_amortizacion_def.plAmDeValorSaldoCapital', $value);

            if ($attribute == 'plAmDeValorCapitalCuota')  
               $query->orderBy('plan_amortizacion_def.plAmDeValorCapitalCuota', $value);

            if ($attribute == 'plAmDeValorInteresCuota')  
               $query->orderBy('plan_amortizacion_def.plAmDeValorInteresCuota', $value);

            if ($attribute == 'plAmDeValorSeguroCuota')  
               $query->orderBy('plan_amortizacion_def.plAmDeValorSeguroCuota', $value);

            if ($attribute == 'plAmDeValorInteresMora')  
               $query->orderBy('plan_amortizacion_def.plAmDeValorInteresMora', $value);

            if ($attribute == 'plAmDeDiasMora')  
               $query->orderBy('plan_amortizacion_def.plAmDeDiasMora', $value);

            if ($attribute == 'plAmDeFechaUltimoPagoCuota')  
               $query->orderBy('plan_amortizacion_def.plAmDeFechaUltimoPagoCuota', $value);

            if ($attribute == 'plAmDeCuotaCancelada')  
               $query->orderBy('plan_amortizacion_def.plAmDeCuotaCancelada', $value);

            if ($attribute == 'plAmDeEstadoPlanAmortizacion')  
               $query->orderBy('plan_amortizacion_def.plAmDeEstadoPlanAmortizacion', $value);

            if ($attribute == 'plAmDeEstado')  
               $query->orderBy('plan_amortizacion_def.plAmDeEstado', $value);

            if ($attribute == 'usuario_creacion_nombre')  
               $query->orderBy('plan_amortizacion_def.usuario_creacion_nombre', $value);

            if ($attribute == 'usuario_modificacion_nombre')  
               $query->orderBy('plan_amortizacion_def.usuario_modificacion_nombre', $value);

            if ($attribute == 'fecha_creacion')  
               $query->orderBy('plan_amortizacion_def.created_at', $value);

            if ($attribute == 'fecha_modificacion')  
               $query->orderBy('plan_amortizacion_def.updated_at', $value);
         }
      else 
         $query->orderBy("plan_amortizacion_def.updated_at", "desc");

      $planAmortizacionDefinitivo = $query->paginate($dto['limite'] ?? 100);
      $datos = [];

      foreach ($planAmortizacionDefinitivo ?? [] as $data)  
         array_push($datos, $data);

      $cantidadPlanAmortizacionDef = count($planAmortizacionDefinitivo);
      $to = isset($planAmortizacionDefinitivo) && $cantidadPlanAmortizacionDef > 0 ? $planAmortizacionDefinitivo->currentPage() * $planAmortizacionDefinitivo->perPage() : null;
      $to = isset($to) && isset($planAmortizacionDefinitivo) && $to > $planAmortizacionDefinitivo->total() && $cantidadPlanAmortizacionDef > 0 ? $planAmortizacionDefinitivo->total() : $to;
      $from = isset($to) && isset($planAmortizacionDefinitivo) && $cantidadPlanAmortizacionDef > 0 ?
         ( $planAmortizacionDefinitivo->perPage() > $to ? 1 : ($to - $cantidadPlanAmortizacionDef) + 1 )
         : null;

      return [
         'datos' => $datos,
         'desde' => $from,
         'hasta' => $to,
         'por_pagina' => isset($planAmortizacionDefinitivo) && $cantidadPlanAmortizacionDef > 0 ? +$planAmortizacionDefinitivo->perPage() : 0,
         'pagina_actual' => isset($planAmortizacionDefinitivo) && $cantidadPlanAmortizacionDef > 0 ? $planAmortizacionDefinitivo->currentPage() : 1,
         'ultima_pagina' => isset($planAmortizacionDefinitivo) && $cantidadPlanAmortizacionDef > 0 ? $planAmortizacionDefinitivo->lastPage() : 0,
         'total' => isset($planAmortizacionDefinitivo) && $cantidadPlanAmortizacionDef > 0 ? $planAmortizacionDefinitivo->total() : 0
      ];
   }

   public static function getHeaders($id) 
   { 
      $proyecto = Proyecto::find($id);
      $persona = $proyecto->solicitante;
      return $proyecto;
   }

   public static function calcularPlan($params) 
   { 
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
