<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\AsociadoNegocio;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Parametros\ParametroConstante;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;


class ListaParticipantesActividad implements FromQuery, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents, WithStyles, WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;

    public function __construct($dto){
        $this->headingInfo = DB::table('proyectos_actividades_participantes')
            ->join('proyectos','proyectos.id','=','proyectos_actividades_participantes.proyecto_id')
            ->join('tipos_actividades','tipos_actividades.id','=','proyectos_actividades_participantes.actividad_id')
            ->join('proyectos_actividades','proyectos_actividades.actividad_id','=','proyectos_actividades_participantes.actividad_id')
            ->join('colaboradores AS c1','c1.id','=','proyectos_actividades.responsable_id')
            ->join('colaboradores AS c2','c2.id','=','proyectos_actividades.responsable2_id')
            ->select(
                'proyectos.nombre AS nombre_proyecto',
                'tipos_actividades.nombre AS nombre_actividad',
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(c1.nombre_colaborador), ''),
                        IFNULL(CONCAT(' ',c1.segundo_nombre_colaborador),''),
                        IFNULL(CONCAT(' ',c1.primer_apellido_colaborador), ''),
                        IFNULL(CONCAT(' ',c1.segundo_apellido_colaborador),'')
                        )
                    AS nombre_responsable_1"
                ),
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(c2.nombre_colaborador), ''),
                        IFNULL(CONCAT(' ',c2.segundo_nombre_colaborador),''),
                        IFNULL(CONCAT(' ',c2.primer_apellido_colaborador), ''),
                        IFNULL(CONCAT(' ',c2.segundo_apellido_colaborador),'')
                        )
                    AS nombre_responsable_2"
                ),
                'proyectos_actividades.fecha_inicio',
                'proyectos_actividades.hora_inicio',
                'proyectos_actividades.lugar_actividad',
            )
            ->where('proyectos_actividades_participantes.proyecto_id', $dto['proyecto_id'])
            ->where('proyectos_actividades_participantes.actividad_id', $dto['actividad_id'])
            ->get();

        $this->count = DB::table('proyectos_actividades_participantes')
            ->join('participantes','participantes.id','=','proyectos_actividades_participantes.participante_id')
            ->select(
                'participantes.numero_documento'
            )
            ->where('proyectos_actividades_participantes.proyecto_id', $dto['proyecto_id'])
            ->where('proyectos_actividades_participantes.actividad_id', $dto['actividad_id'])
            ->get();
       
        $this->dto = $dto;
        $this->parametros = ParametroConstante::cargarParametros();
        $this->columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $this->index = 12;
    }

    public function startCell() : string {
        return 'A11';
    }

    public function registerEvents() : array {
        return [
            AfterSheet::class => function(AfterSheet $event){
                $sheet = $event->sheet;
                $sheet->setCellValue('A1', $this->parametros['NOMBRE_CORPORACION']??'Proyectarte');
                $sheet->setCellValue('A3', "Nombre Proyecto:");
                $sheet->setCellValue('B3', $this->headingInfo[0]->nombre_proyecto);
                $sheet->setCellValue('A4', "Nombre Actividad:");
                $sheet->setCellValue('B4', $this->headingInfo[0]->nombre_actividad);
                $sheet->setCellValue('A5', "Nombre Responsable 1:");
                $sheet->setCellValue('B5', $this->headingInfo[0]->nombre_responsable_1);
                $sheet->setCellValue('A6', "Nombre Responsable 2:");
                $sheet->setCellValue('B6', $this->headingInfo[0]->nombre_responsable_2);
                $sheet->setCellValue('A7', "Fecha Actividad:");
                $sheet->setCellValue('B7', $this->headingInfo[0]->fecha_inicio);
                $sheet->setCellValue('A8', "Hora Inicio:");
                $sheet->setCellValue('B8', $this->headingInfo[0]->hora_inicio);
                $sheet->setCellValue('A9', "Lugar:");
                $sheet->setCellValue('B9', $this->headingInfo[0]->lugar_actividad);
               
                $sheet->setCellValue('A'.count($this->count)+13, $this->parametros['NOTA_LISTA']??'Nota: No se ha definido la nota');
                $event->sheet->getDelegate()->getStyle('B7')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                $event->sheet->getDelegate()->getParent()->getDefaultStyle()->getFont()->setSize(12);

            }
        ];
    }

    public function query(){
        $query = DB::table('proyectos_actividades_participantes')
            ->join('participantes','participantes.id','=','proyectos_actividades_participantes.participante_id')
            ->leftJoin('familias', 'familias.id', 'participantes.familia_id')
            ->select(
                'participantes.numero_documento',
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(participantes.nombre_participante), ''),
                        IFNULL(CONCAT(' ',participantes.segundo_nombre_participante),''),
                        IFNULL(CONCAT(' ',participantes.primer_apellido_participante), ''),
                        IFNULL(CONCAT(' ',participantes.segundo_apellido_participante),'')
                        )
                    AS nombre"
                ),
                'participantes.telefono_contacto_familia',
            )
            ->where('proyectos_actividades_participantes.proyecto_id', $this->dto['proyecto_id'])
            ->where('proyectos_actividades_participantes.actividad_id', $this->dto['actividad_id'])
            ->orderBy('familias.nombre', 'asc')
            ->orderBy(DB::Raw(
                "CONCAT(
                    IFNULL(CONCAT(participantes.nombre_participante), ''),
                    IFNULL(CONCAT(' ',participantes.segundo_nombre_participante),''),
                    IFNULL(CONCAT(' ',participantes.primer_apellido_participante), ''),
                    IFNULL(CONCAT(' ',participantes.segundo_apellido_participante),'')
                    )"
                ), 'asc');

        return $query;
   }

   public function styles(Worksheet $sheet){
        $sheet->getStyle('A1:A9')->getFont()->setBold(true)->setSize(12);  
        $sheet->getStyle('A11:H11')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A11:H11')->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A11:H11')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        foreach($this->columns as $column){
            $sheet->getStyle($column.'11')->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($column.'11')->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
        }
        foreach($this->count as $row){
            foreach($this->columns as $column){
                $sheet->getStyle($column.$this->index)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($column.$this->index)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
            }
            $sheet->getStyle('A'.$this->index.':H'.$this->index)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A'.$this->index.':H'.$this->index)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A'.$this->index.':H'.$this->index)->getFont()->setSize(12);
            $this->index+=1;
        }
   }

   public function columnWidths(): array {
       return [
           'A' => 23,
           'D' => 25,
           'E' => 25,
           'F' => 25,
           'G' => 25,
           'H' => 25,
       ];
   }

    public function headings(): array
    {
        return [
            "Numero Documento",
            "Nombre Participante",
            "Telefono Contacto",
            "Firma",
            "Firma",
            "Firma",
            "Firma",
            "Firma",
        ];
    }
}