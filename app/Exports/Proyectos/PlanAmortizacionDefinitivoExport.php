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

class PlanAmortizacionDefinitivoExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithStrictNullComparison
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
      $query = DB::table('plan_amortizacion_def')
      ->join('proyectos', 'proyectos.id', 'plan_amortizacion_def.proyecto_id')
      ->join('personas', 'personas.id', 'proyectos.persona_id')
      ->select(
            'plan_amortizacion_def.proyecto_id',   
            'personas.personasIdentificacion',
            DB::Raw(
               "CONCAT(
                  IFNULL(CONCAT(personas.personasNombres), ''),
                  IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
                  IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
                  )
                  AS nombre"
            ),
            'plan_amortizacion_def.plAmDeNumeroCuota',
            DB::Raw("DATE(plan_amortizacion_def.plAmDeFechaVencimientoCuota) AS plAmDeFechaVencimientoCuota"),  
            'plan_amortizacion_def.plAmDeValorSaldoCapital', 
            'plan_amortizacion_def.plAmDeValorCapitalCuota', 
            'plan_amortizacion_def.plAmDeValorInteresCuota', 
            'plan_amortizacion_def.plAmDeValorSeguroCuota', 
            DB::Raw("(plan_amortizacion_def.plAmDeValorCapitalCuota + 
                      plan_amortizacion_def.plAmDeValorInteresCuota + 
                      plan_amortizacion_def.plAmDeValorSeguroCuota) AS ValorCuotaMensual"),
            'plan_amortizacion_def.plAmDeValorInteresMora', 
            'plan_amortizacion_def.plAmDeDiasMora', 
            DB::Raw("DATE(plan_amortizacion_def.plAmDeFechaUltimoPagoCuota) AS plAmDeFechaUltimoPagoCuota"),  
            DB::Raw("CASE plan_amortizacion_def.plAmDeCuotaCancelada 
                     WHEN 'S' THEN 'Si'
                     ELSE 'No' END AS plAmDeCuotaCancelada"), 
            DB::Raw("CASE plan_amortizacion_def.plAmDeEstadoPlanAmortizacion
                     WHEN 'DES' THEN 'Desembolso'
                     WHEN 'DEF' THEN 'Definitivo'
                     WHEN 'REG' THEN 'Regenerado'
                     ELSE '' END AS plAmDeEstadoPlanAmortizacion"), 
            DB::Raw("CASE plan_amortizacion_def.plAmDeEstado 
                     WHEN 0 THEN 'Inactivo'
                     ELSE 'Activo' END AS plAmDeEstado"),  
            'plan_amortizacion_def.usuario_modificacion_nombre',
            'plan_amortizacion_def.updated_at AS fecha_modificacion',
            'plan_amortizacion_def.usuario_creacion_nombre',
            'plan_amortizacion_def.created_at AS fecha_creacion',
         )
         ->where('plan_amortizacion_def.proyecto_id', $this->dto['proyecto_id'])
         ->orderBy('plan_amortizacion_def.plAmDeNumeroCuota', 'asc');

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
         "Numero Proyecto",   
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
