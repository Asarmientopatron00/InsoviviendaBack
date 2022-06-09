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

class PlanAmortizacionDefinitivoExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles
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
         ->select(
            'plan_amortizacion_def.proyecto_id',   
            'plan_amortizacion_def.plAmDeNumeroCuota',
            DB::Raw("DATE(plan_amortizacion_def.plAmDeFechaVencimientoCuota) AS plAmDeFechaVencimientoCuota"),  
            'plan_amortizacion_def.plAmDeValorSaldoCapital', 
            'plan_amortizacion_def.plAmDeValorCapitalCuota', 
            'plan_amortizacion_def.plAmDeValorInteresCuota', 
            'plan_amortizacion_def.plAmDeValorSeguroCuota', 
            'plan_amortizacion_def.plAmDeValorInteresMora', 
            'plan_amortizacion_def.plAmDeDiasMora', 
            DB::Raw("DATE(plan_amortizacion_def.plAmDeFechaUltimoPagoCuota) AS plAmDeFechaUltimoPagoCuota"),  
            'plan_amortizacion_def.plAmDeCuotaCancelada', 
            'plan_amortizacion_def.plAmDeEstadoPlanAmortizacion', 
            'plan_amortizacion_def.plAmDeEstado', 
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
      $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
      $sheet->getStyle('D')->getNumberFormat()->setFormatCode('$ #,##0');
      $sheet->getStyle('E')->getNumberFormat()->setFormatCode('$ #,##0');
      $sheet->getStyle('F')->getNumberFormat()->setFormatCode('$ #,##0');
      $sheet->getStyle('G')->getNumberFormat()->setFormatCode('$ #,##0');
      $sheet->getStyle('H')->getNumberFormat()->setFormatCode('$ #,##0');
   }

   public function headings(): array
   {
      return [
         "Numero Proyecto",   
         "Número Cuota", 
         "Fecha Vencimiento Cuota",  
         "Valor Saldo Capital", 
         "Valor Capital Cuota", 
         "Valor Interes Cuota", 
         "Valor Seguro Cuota", 
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
