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

class DesembolsoExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles
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
      $query = DB::table('desembolsos')
         ->join('proyectos', 'proyectos.id', 'desembolsos.proyecto_id')
         ->join('personas', 'personas.id', 'proyectos.persona_id')
         ->leftJoin('bancos', 'bancos.id', 'proyectos.banco_id')
         ->select(
            'desembolsos.proyecto_id',
            'desembolsos.desembolsosFechaDesembolso',
            'personas.personasIdentificacion',
            DB::Raw(
            "CONCAT(
               IFNULL(CONCAT(personas.personasNombres), ''),
               IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
               IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
               )
               AS nombre"
            ),
            'desembolsos.desembolsosDescripcionDes',
            DB::Raw("IFNULL(bancos.bancosDescripcion, '') AS bancosDescripcion"),
            'desembolsos.desembolsosTipoCuentaDes',
            'desembolsos.desembolsosNumeroCuentaDes',
            'desembolsos.desembolsosNumeroComEgreso',
            'desembolsos.desembolsosValorDesembolso',
            'desembolsos.desembolsosFechaNormalizacionP',
            'desembolsos.desembolsosPlanDefinitivo',
            'desembolsos.desembolsosEstado',
            'desembolsos.usuario_creacion_nombre',
            'desembolsos.usuario_modificacion_nombre',
            'desembolsos.created_at AS fecha_creacion',
            'desembolsos.updated_at AS fecha_modificacion',
         );

      if (isset($this->dto['proyecto']))
         $query->where('desembolsos.proyecto_id', '>=', $this->dto['proyecto']);
      
      if(isset($this->dto['solicitante']))
         $query->where('personas.personasIdentificacion', $this->dto['solicitante']);

      if(isset($this->dto['fechaDesde']))
         $query->where('desembolsos.desembolsosFechaDesembolso', '>=', $this->dto['fechaDesde'].' 00:00:00');
        
      if(isset($this->dto['fechaHasta']))
         $query->where('desembolsos.desembolsosFechaDesembolso', '<=', $this->dto['fechaHasta'] . ' 23:59:59');
        
      if(isset($this->dto['estado']))
         $query->where('desembolsos.desembolsosEstado', $this->dto['estado']);

      $query->orderBy('desembolsos.proyecto_id', 'asc');
      
      return $query;
   }
   
   public function styles(Worksheet $sheet)
   {
      $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
      $sheet->getStyle('J')->getNumberFormat()->setFormatCode('$ #,##0');   
   }
   
   public function headings(): array
   {
      return [
         "Numero Proyecto",   
         "Fecha Desembolso", 
         "Identificación",  
         "Nombre Solicitante", 
         "Descripción Desembolso", 
         "Banco Desembolso", 
         "Tipo Cuenta Desembolso", 
         "Número Cuenta Desembolso", 
         "Número Comprobante Egreso", 
         "Valor Desembolso", 
         "Fecha Normalización Pago",  
         "Definitivo", 
         "Estado", 
         "Usuario Modificación",
         "Fecha Modificación",
         "Usuario Creación",
         "Fecha Creación",
      ];
   } 
}
