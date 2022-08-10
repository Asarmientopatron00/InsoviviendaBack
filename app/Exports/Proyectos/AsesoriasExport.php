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

class AsesoriasExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithStrictNullComparison
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
      $query = DB::table('orientaciones')
         ->join('tipos_orientacion', 'tipos_orientacion.id', 'orientaciones.tipo_orientacion_id')
         ->join('orientadores', 'orientadores.id', 'orientaciones.orientador_id')
         ->join('personas_asesorias', 'personas_asesorias.id', 'orientaciones.persona_asesoria_id')      
         ->select(
            'tipos_orientacion.tipOriDescripcion',
            'orientadores.orientadoresNombre',
            'orientaciones.orientacionesFechaOrientacion',
            'personas_asesorias.nombre',
            'orientaciones.orientacionesSolicitud',
            'orientaciones.orientacionesNota',
            'orientaciones.orientacionesRespuesta', 
            'orientaciones.estado',
            'orientaciones.usuario_creacion_nombre',
            'orientaciones.created_at',
            'orientaciones.usuario_modificacion_nombre',
            'orientaciones.updated_at',
         );

      if (isset($dto['tipoAsesoria']))
         $query->where('tipos_orientacion.id', '=', $dto['tipoAsesoria']);
        
      if (isset($dto['identificacionOrientador']))
         $query->where('orientadores.orientadoresIdentificacion', '=', $dto['identificacionOrientador']);
        
      if (isset($dto['fechaOrientacion']))
         $query->where('orientaciones.orientacionesFechaOrientacion', 'like', '%' . $dto['fechaOrientacion'] . '%');
        
      if (isset($dto['identificacionPersona']))
         $query->where('personas_asesorias.numero_documento', '=', $dto['identificacionPersona']);
        
      if (isset($dto['estado']))
         $query->where('orientaciones.estado', 'like', '%' . $dto['estado'] . '%');
        
      $query->orderBy('orientadores.orientadoresNombre', 'asc');
      
      return $query;
   }
   
   public function styles(Worksheet $sheet)
   {
      $sheet->getStyle('A1:L1')->getFont()->setBold(true);
   }
   
   public function headings(): array
   {
      return [
         "Tipo asesoria", 
         "Asesor",   
         "Fecha asesoria",   
         "Nombre asesorado",   
         "Solicitud", 
         "Nota",  
         "Respuesta", 
         "Estado", 
         "Usuario Creación",
         "Fecha Creación",
         "Usuario Modificación",
         "Fecha Modificación",
      ];
   } 
}
