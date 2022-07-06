<?php

namespace App\Exports\PersonasEntidades;

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

class FamiliasExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithStrictNullComparison
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
      $query = DB::table('familias')
         ->leftJoin('condiciones_familia', 'condiciones_familia.id', 'familias.condicion_familia_id')
         ->join('tipos_familia', 'tipos_familia.id', 'familias.tipo_familia_id')
         ->join('personas', 'personas.personasIdentificacion', 'familias.identificacion_persona')
         ->select(
            'familias.identificacion_persona',
            DB::Raw(
               "CONCAT(
                  IFNULL(CONCAT(personas.personasNombres), ''),
                  IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
                  IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
                  )
               AS nombre"
            ),
            'tipos_familia.tipFamDescripcion',
            'condiciones_familia.conFamDescripcion',
            'familias.familiasFechaVisitaDomici',
            'familias.familiasAportesFormales',
            'familias.familiasAportesInformales',
            'familias.familiasAportesArriendo',
            'familias.familiasAportesSubsidios',
            'familias.familiasAportesPaternidad',
            'familias.familiasAportesTerceros',
            'familias.familiasAportesOtros',
            'familias.familiasEgresosDeudas',
            'familias.familiasEgresosEducacion',
            'familias.familiasEgresosSalud',
            'familias.familiasEgresosTransporte',
            'familias.familiasEgresosSerPublicos',
            'familias.familiasEgresosAlimentacion',
            'familias.familiasEgresosVivienda',
            DB::Raw('CASE familias.familiasEstado
                     WHEN 0 THEN "Inactivo"
                     WHEN 1 THEN "Activo"
                     ELSE "" END AS familiasEstado'
            ),
            'familias.familiasObservaciones',
            'familias.usuario_creacion_nombre',
            'familias.created_at',
            'familias.usuario_modificacion_nombre',
            'familias.updated_at',
         );

      if(isset($this->dto['identificacion'])){
         $query->where('familias.identificacion_persona', 'like', '%' . $this->dto['identificacion'] . '%');
      }

      if(isset($this->dto['estado'])){
         $query->where('familias.familiasEstado', $this->dto['estado']);
      }

      if(isset($this->dto['tipoFamilia'])){
         $query->where('familias.tipo_familia_id', $this->dto['tipoFamilia']);
      }

      if(isset($this->dto['condicionFamilia'])){
         $query->where('familias.condicion_familia_id', $this->dto['condicionFamilia']);
      }

      $query->orderBy('personas.personasPrimerApellido', 'asc');
      $query->orderBy('personas.personasSegundoApellido', 'asc');
      
      return $query;
   }
   
   public function styles(Worksheet $sheet)
   {
      $sheet->getStyle('A1:Y1')->getFont()->setBold(true);
      $sheet->getStyle('F:S')->getNumberFormat()->setFormatCode('$#,##0');
   }
   
   public function headings(): array
   {
      return [
         "Identificación Cabeza Familia", 
         "Nombre Cabeza Familia",   
         "Tipo Familia",   
         "Condición Familia",   
         "Fecha Visita Domicil.", 
         "Aportes Formales",  
         "Aportes Informales",  
         "Aportes Arriendo",  
         "Aportes Subsidios",  
         "Aportes Paternidad",  
         "Aportes Terceros",  
         "Aportes Otros",  
         "Egresos Deudas",  
         "Egresos Educación",  
         "Egresos Salud",  
         "Egresos Transporte",  
         "Egresos Serv. Públicos",  
         "Egresos Alimentación",  
         "Egresos Vivienda",  
         "Estado",  
         "Observaciones",  
         "Usuario Creación",
         "Fecha Creación",
         "Usuario Modificación",
         "Fecha Modificación",
      ];
   } 
}
