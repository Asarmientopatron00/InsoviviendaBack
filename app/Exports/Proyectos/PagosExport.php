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

class PagosExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithStrictNullComparison
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
      $query = DB::table('pagos')
         ->join('pagos_detalle', 'pagos_detalle.pago_id', 'pagos.id')
         ->select(
            'pagos.proyecto_id',
            'pagos.pagosConsecutivo',
            'pagos.pagosFechaPago',
            'pagos.pagosValorTotalPago',
            'pagos.pagosDescripcionPago',
            'pagos.pagosEstado',
            'pagos_detalle.pagDetNumeroCuota',
            'pagos_detalle.pagDetFechaVencimientoCuota',
            'pagos_detalle.pagDetValorCapitalCuotaPagado',
            'pagos_detalle.pagDetValorSaldoCuotaPagado',
            'pagos_detalle.pagDetValorInteresCuotaPagado',
            'pagos_detalle.pagDetValorSeguroCuotaPagado',
            'pagos_detalle.pagDetValorInteresMoraPagado',
            'pagos_detalle.pagDetDiasMora',
            'pagos_detalle.usuario_creacion_nombre',
            'pagos_detalle.created_at',
            'pagos_detalle.usuario_modificacion_nombre',
            'pagos_detalle.updated_at',
         );

      if (isset($this->dto['proyecto'])){
         $query->where('pagos.proyecto_id', '>=', $this->dto['proyecto']);
      }

      if(isset($this->dto['fechaDesde'])){
         $query->where('pagos.pagosFechaPago', '>=', $this->dto['fechaDesde'].' 00:00:00');
      }
        
      if(isset($this->dto['fechaHasta'])){
         $query->where('pagos.pagosFechaPago', '<=', $this->dto['fechaHasta'] . ' 23:59:59');
      }
        
      if(isset($this->dto['estado'])){
         $query->where('pagos.pagosEstado', $this->dto['estado']);
      }

      $query->orderBy('pagos.proyecto_id', 'asc');
      $query->orderBy('pagos.pagosConsecutivo', 'asc');
      $query->orderBy('pagos_detalle.pagDetNumeroCuota', 'asc');
      
      return $query;
   }
   
   public function styles(Worksheet $sheet)
   {
      $sheet->getStyle('A1:R1')->getFont()->setBold(true);
      $sheet->getStyle('D')->getNumberFormat()->setFormatCode('$#,##0');   
      $sheet->getStyle('I:M')->getNumberFormat()->setFormatCode('$#,##0');   
   }
   
   public function headings(): array
   {
      return [
         "Proyecto N.",   
         "Consecutivo", 
         "Fecha Pago", 
         "Valor Pago",  
         "Descripción Pago", 
         "Estado Pago", 
         "Cuota N.", 
         "Fecha Vencimiento Cuota", 
         "Valor Capital Pagado", 
         "Valor Saldo Abonado", 
         "Valor Interes Pagado", 
         "Valor Seguro Pagado",  
         "Valor Interes Mora Pagado", 
         "Dias Mora", 
         "Usuario Creación",
         "Fecha Creación",
         "Usuario Modificación",
         "Fecha Modificación",
      ];
   } 
}
