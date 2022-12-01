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

class DonacionesExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithStrictNullComparison
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
      $query = DB::table('donaciones') 
         ->leftJoin('personas', 'personas.id', '=', 'donaciones.persona_id')
         ->join('tipos_donacion', 'tipos_donacion.id', '=', 'donaciones.tipo_donacion_id')
         ->join('formas_pago', 'formas_pago.id', '=', 'donaciones.forma_pago_id')
         ->leftJoin('bancos', 'bancos.id', '=', 'donaciones.banco_id')
         ->select ( 
            'tipos_donacion.tipDonDescripcion',
            'donaciones.donacionesNumeroRecibo',
            'personas.personasIdentificacion',
            DB::Raw("
               CONCAT(
                  IFNULL(CONCAT(personas.personasNombres), ''), 
                  IFNULL(CONCAT(' ', personas.personasPrimerApellido), ''),
                  IFNULL(CONCAT(' ', personas.personasSegundoApellido), '')
               ) AS nombre
            "),
            'donaciones.donacionesNumeroDocumentoTercero',
            'donaciones.donacionesNombreTercero',
            'donaciones.donacionesFechaDonacion',
            'donaciones.donacionesFechaRecibo',
            'donaciones.donacionesValorDonacion',
            'donaciones.donacionesNotas',
            DB::raw("CASE donaciones.donacionesEstadoDonacion
               WHEN 'PD' THEN 'Por Desembolsar'
               WHEN 'DE' THEN 'Desembolsado'
               ELSE '' END AS donacionesEstadoDonacion
            "),
            'formas_pago.forPagDescripcion',
            'donaciones.donacionesNumeroCheque',
            'bancos.bancosDescripcion',
            DB::raw("CASE donaciones.estado
               WHEN 1 THEN 'Activo'
               ELSE 'Inactivo' END AS estado
            "),
         );
 
      if (isset($this->dto['benefactor'])) {
         $query->where('donaciones.donacionesNombreTercero', 'like', '%' . $this->dto['benefactor'] . '%');
      }

      if (isset($this->dto['fechaInicial'])) {
         $query->where('donaciones.donacionesFechaDonacion', '>=', $this->dto['fechaInicial']);
      }

      if (isset($this->dto['fechaFinal'])) {
         $query->where('donaciones.donacionesFechaDonacion', '<=', $this->dto['fechaFinal']);
      }

      if(isset($this->dto['identificacion'])){
         $query->where('personas.personasIdentificacion', 'like', '%' . $this->dto['identificacion'] . '%');
      }

      $query->orderBy('donaciones.donacionesNumeroRecibo', 'asc');
      
      return $query;
   }
   
   public function styles(Worksheet $sheet)
   {
      $sheet->getStyle('A1:O1')->getFont()->setBold(true);
      $sheet->getStyle('I')->getNumberFormat()->setFormatCode('$#,##0');   
   }
   
   public function headings(): array
   {
      return [
         "Tipo", 
         "N. Recibo",   
         "Documento Beneficiario",   
         "Nombre Beneficiario",   
         "Documento Tercero",   
         "Nombre Tercero",   
         "Fecha", 
         "Fecha Recibo", 
         "Valor",  
         "Concepto", 
         "Estado",  
         "Forma de Pago",  
         "Cheque N.",  
         "Banco", 
         "Estado Registro", 
      ];
   } 
}
