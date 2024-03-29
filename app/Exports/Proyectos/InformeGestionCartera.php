<?php

namespace App\Exports\Proyectos;

use Carbon\Carbon;
use App\Models\AsociadoNegocio;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class InformeGestionCartera implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithStrictNullComparison
{
   /**
   * @return \Illuminate\Support\Collection
   */
   use Exportable;

   public function __construct($dto)
   {
      $this->dto = $dto;
   }
  
   public function query()
   {
      $query = DB::table('proyectos AS t1')
         ->join('personas AS t2', 't2.id', 't1.persona_id')
         ->join('tipos_identificacion AS t3', 't3.id', 't2.tipo_identificacion_id')
         ->leftJoin('orientadores AS t4', 't4.id', 't1.asesor_gestion_cartera_id')
         ->select(
            DB::raw("(SELECT DATE_FORMAT(CURRENT_TIMESTAMP(), '%Y-%m-%d %H:%i')) AS fecha"),
            't3.tipIdeDescripcion AS tipo_identificacion',
            't2.personasIdentificacion',
            DB::raw(
               "CONCAT(
                   IFNULL(CONCAT(t2.personasNombres), ''),
                   IFNULL(CONCAT(' ',t2.personasPrimerApellido),''),
                   IFNULL(CONCAT(' ',t2.personasSegundoApellido), '')
                   )
               AS nombre"
            ),
            't4.orientadoresNombre AS asesor',
            't1.proyectosObservacionesGestionC',
            't2.personasTelefonoCasa',
            't2.personasTelefonoCelular',
            't1.proyectosValorCuotaAprobada',
            DB::raw("
               (
                  SELECT DATE_FORMAT(MAX(t4.plAmDeFechaVencimientoCuota), '%Y-%m-%d')
                  FROM plan_amortizacion_def t4
                  WHERE t4.proyecto_id = t1.id
                  AND t4.plAmDeCuotaCancelada = 'S'
               ) AS ultima_fecha_vencimiento
            "),
            DB::raw("
               (
                  SELECT DATE_FORMAT(MAX(t4.plAmDeFechaUltimoPagoCuota), '%Y-%m-%d')
                  FROM plan_amortizacion_def t4
                  WHERE t4.proyecto_id = t1.id
                  AND t4.plAmDeCuotaCancelada = 'S'
               ) AS ultima_fecha_pago
            "),
            DB::raw("(
               SELECT COALESCE(t1.proyectosValorSaldoUnificado, 0)
               ) AS valor_saldo_unificado
            "),
            DB::raw("(
               SELECT SUM(desembolsos.desembolsosValorDesembolso)
               FROM desembolsos
               WHERE desembolsos.proyecto_id = t1.id
               AND desembolsos.desembolsosEstado = 1
               ) AS valor_desembolsos
            "),
            DB::raw("(
               SELECT IFNULL(SUM(pd.pagDetValorCapitalCuotaPagado) + SUM(IFNULL(pd.pagDetValorSaldoCuotaPagado,0)),0) 
               FROM pagos_detalle pd 
               WHERE pd.proyecto_id = t1.id
               AND pd.pagDetEstado = 1
               ) AS valor_pagos
            "),
         )
         ->where('t1.proyectosEstadoProyecto', 'DES')
         ->whereRaw("
            (
               SELECT COUNT(1)
               FROM desembolsos
               WHERE desembolsos.proyecto_id = t1.id
               AND desembolsos.desembolsosEstado = 1
            ) > 0
         ");

      $subQuery = DB::table($query, 'sub')
         ->select(
             'sub.fecha',
             'sub.tipo_identificacion',
             'sub.personasIdentificacion',
             'sub.nombre',
             'sub.asesor',
             'sub.proyectosObservacionesGestionC',
             'sub.personasTelefonoCasa',
             'sub.personasTelefonoCelular',
             'sub.proyectosValorCuotaAprobada',
             'sub.ultima_fecha_vencimiento',
             'sub.ultima_fecha_pago',
             DB::raw("(SELECT DATEDIFF(sub.fecha, sub.ultima_fecha_vencimiento))"),
             DB::raw("sub.valor_saldo_unificado+sub.valor_desembolsos-sub.valor_pagos")
         );

      $subQuery->orderBy('sub.nombre', "asc");
      
      return $subQuery;
   }
   
   public function styles(Worksheet $sheet)
   {
      $sheet->getStyle('A1:M1')->getFont()->setBold(true);
      $sheet->getStyle('I')->getNumberFormat()->setFormatCode('$#,##0');    
      $sheet->getStyle('M')->getNumberFormat()->setFormatCode('$#,##0');    
   }
   
   public function headings(): array
   {
      return [
         "Fecha y Hora", 
         "Tipo Documento",   
         "Número Documento",   
         "Nombre Solicitante",
         "Responsable",
         "Observaciones Gestión Cartera",
         "Tel. Fijo",
         "Tel. Celular",
         "Valor Cuota",
         "Fecha Venc. Ult. Cuota Cancelada",
         "Fecha Último Pago",
         "Dias Cartera",
         "Valor Saldo Capital",
      ];
   } 
}
