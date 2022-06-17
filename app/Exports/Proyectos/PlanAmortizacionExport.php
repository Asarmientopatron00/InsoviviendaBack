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

class PlanAmortizacionExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithStrictNullComparison
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
      $query = DB::table('plan_amortizacion')
         ->join('proyectos', 'proyectos.id', 'plan_amortizacion.proyecto_id')
         ->join('personas', 'personas.id', 'proyectos.persona_id')
         ->select(
            'plan_amortizacion.proyecto_id',   
            'personas.personasIdentificacion',
            DB::Raw(
               "CONCAT(
                  IFNULL(CONCAT(personas.personasNombres), ''),
                  IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
                  IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
                  )
                  AS nombre"
            ),
            'plan_amortizacion.plaAmoNumeroCuota', 
            DB::Raw("DATE(plan_amortizacion.plaAmoFechaVencimientoCuota) AS plaAmoFechaVencimientoCuota"),  
            'plan_amortizacion.plaAmoValorSaldoCapital', 
            'plan_amortizacion.plaAmoValorCapitalCuota', 
            'plan_amortizacion.plaAmoValorInteresCuota', 
            'plan_amortizacion.plaAmoValorSeguroCuota',
            DB::Raw("(plan_amortizacion.plaAmoValorCapitalCuota + 
                      plan_amortizacion.plaAmoValorInteresCuota + 
                      plan_amortizacion.plaAmoValorSeguroCuota) AS ValorCuotaMensual"),
            'plan_amortizacion.plaAmoValorInteresMora', 
            'plan_amortizacion.plaAmoDiasMora', 
            DB::Raw("DATE(plan_amortizacion.plaAmoFechaUltimoPagoCuota) AS plaAmoFechaUltimoPagoCuota"),  
            DB::Raw("CASE plan_amortizacion.plaAmoCuotaCancelada 
                     WHEN 'S' THEN 'Si'
                     ELSE 'No' END AS plaAmoCuotaCancelada"), 
            DB::Raw("CASE plan_amortizacion.plaAmoEstadoPlanAmortizacion
                     WHEN 'DES' THEN 'Desembolso'
                     WHEN 'DEF' THEN 'Definitivo'
                     WHEN 'REG' THEN 'Regenerado'
                     ELSE '' END AS plaAmoEstadoPlanAmortizacion"), 
            DB::Raw("CASE plan_amortizacion.plaAmoEstado 
                     WHEN 0 THEN 'Inactivo'
                     ELSE 'Activo' END AS plaAmoEstado"),  
            'plan_amortizacion.usuario_modificacion_nombre',
            'plan_amortizacion.updated_at AS fecha_modificacion',
            'plan_amortizacion.usuario_creacion_nombre',
            'plan_amortizacion.created_at AS fecha_creacion',
         )
         ->where('plan_amortizacion.proyecto_id', $this->dto['proyecto_id'])
         ->orderBy('plan_amortizacion.plaAmoNumeroCuota', 'asc');

      return $query;
   }
   
   public function styles(Worksheet $sheet)
   {
      $sheet->getStyle('A1:T1')->getFont()->setBold(true);
      $sheet->getStyle('F')->getNumberFormat()->setFormatCode('$ #,##0');   
      $sheet->getStyle('G')->getNumberFormat()->setFormatCode('$ #,##0');   
      $sheet->getStyle('H')->getNumberFormat()->setFormatCode('$ #,##0');   
      $sheet->getStyle('I')->getNumberFormat()->setFormatCode('$ #,##0');   
      $sheet->getStyle('J')->getNumberFormat()->setFormatCode('$ #,##0');   
      $sheet->getStyle('K')->getNumberFormat()->setFormatCode('$ #,##0');   
   }
   
   public function headings(): array
   {
      return [
         "Número Proyecto",   
         "Identificación Solicitante",
         "Nombre solicitante",
         "Número Cuota", 
         "Fecha Vencimiento Cuota",  
         "Valor Saldo Capital", 
         "Valor Capital Cuota", 
         "Valor Interes Cuota", 
         "Valor Seguro Cuota", 
         "Valor Cuota Mensual", 
         "Valor Interes Mora", 
         "Dias Mora", 
         "Fecha Ultimo Pago Cuota",  
         "Cuota Cancelada", 
         "Estado Plan Amortizacion", 
         "Estado", 
         "Usuario Modificación",
         "Fecha Modificación",
         "Usuario Creación",
         "Fecha Creación",
      ];
   } 
}
