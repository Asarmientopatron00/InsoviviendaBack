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
         ->select(
            DB::raw("(SELECT CURRENT_TIMESTAMP()) AS fecha"),
            't3.tipIdeDescripcion AS tipo_identificacion',
            DB::raw(
               "CONCAT(
                   IFNULL(CONCAT(t2.personasNombres), ''),
                   IFNULL(CONCAT(' ',t2.personasPrimerApellido),''),
                   IFNULL(CONCAT(' ',t2.personasSegundoApellido), '')
                   )
               AS nombre"
            ),
            't2.personasTelefonoCasa',
            't2.personasTelefonoCelular',
            't1.proyectosValorCuotaAprobada',
            DB::raw("
               (
                  SELECT MAX(t4.plAmDeFechaVencimientoCuota)
                  FROM plan_amortizacion_def t4
                  WHERE t4.proyecto_id = t1.id
                  AND t4.plAmDeCuotaCancelada = 'S'
               ) AS ultima_fecha_vencimiento
            "),
            DB::raw("
               (
                  SELECT MAX(t4.plAmDeFechaUltimoPagoCuota)
                  FROM plan_amortizacion_def t4
                  WHERE t4.proyecto_id = t1.id
                  AND t4.plAmDeCuotaCancelada = 'S'
               ) AS ultima_fecha_pago
            "),
            DB::raw("
                  CASE WHEN 
               (
                  SELECT t4.pagosSaldoDespPago
                  FROM pagos t4
                  WHERE t4.proyecto_id = t1.id
                  AND t4.pagosEstado = 1
                  AND t4.pagosFechaPago = (
                     SELECT MAX(t5.pagosFechaPago)
                     FROM pagos t5
                     WHERE t5.proyecto_id = t1.id
                     AND t5.pagosEstado = 1
                  ) 
               ) IS NULL 
                  THEN
                  (
                     SELECT SUM(desembolsos.desembolsosValorDesembolso)
                     FROM desembolsos
                     WHERE desembolsos.proyecto_id = t1.id
                     AND desembolsos.desembolsosEstado = 1
                  )
                  ELSE
                  (
                     SELECT t4.pagosSaldoDespPago
                     FROM pagos t4
                     WHERE t4.proyecto_id = t1.id
                     AND t4.pagosEstado = 1
                     AND t4.pagosFechaPago = (
                        SELECT MAX(t5.pagosFechaPago)
                        FROM pagos t5
                        WHERE t5.proyecto_id = t1.id
                        AND t5.pagosEstado = 1
                     ) 
                  )
                END AS saldo
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

      $query->orderBy(DB::raw("
         CONCAT(
            IFNULL(CONCAT(t2.personasNombres), ''),
            IFNULL(CONCAT(' ',t2.personasPrimerApellido),''),
            IFNULL(CONCAT(' ',t2.personasSegundoApellido), '')
            )"), 
         'asc');
      
      return $query;
   }
   
   public function styles(Worksheet $sheet)
   {
      $sheet->getStyle('A1:I1')->getFont()->setBold(true);
      $sheet->getStyle('F')->getNumberFormat()->setFormatCode('$#,##0');    
      $sheet->getStyle('I')->getNumberFormat()->setFormatCode('$#,##0');    
   }
   
   public function headings(): array
   {
      return [
         "Fecha y Hora", 
         "Tipo Documento",   
         "Nombre Solicitante",
         "Tel. Fijo",
         "Tel. Celular",
         "Valor Cuota",
         "Fecha Venc. Ult. Cuota Cancelada",
         "Fecha Último Pago",
         "Valor Saldo Capital",
      ];
   } 
}
