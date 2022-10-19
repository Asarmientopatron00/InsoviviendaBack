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

class ProyectoExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles
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
      $query = DB::table('proyectos')
         ->join('personas', 'personas.id', '=', 'proyectos.persona_id')
         ->join('tipos_programa','tipos_programa.id', '=', 'proyectos.tipo_programa_id')
         ->leftJoin('paises', 'paises.id', '=', 'proyectos.pais_id')
         ->leftJoin('departamentos', 'departamentos.id', '=','proyectos.departamento_id')
         ->leftJoin('ciudades', 'ciudades.id', '=', 'proyectos.ciudad_id')
         ->leftJoin('personas AS remitente', 'remitente.id', '=', 'proyectos.remitido_id')
         ->leftJoin('comunas', 'comunas.id', '=', 'proyectos.comuna_id')
         ->leftJoin('barrios', 'barrios.id', '=', 'proyectos.barrio_id')
         ->leftJoin('bancos', 'bancos.id', '=', 'proyectos.banco_id')
         ->leftJoin('orientadores', 'orientadores.id', '=', 'proyectos.orientador_id')
         ->select(
            'proyectos.id',
            'personas.personasIdentificacion',
            DB::Raw(
               "CONCAT(
                   IFNULL(CONCAT(personas.personasNombres), ''),
                   IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
                   IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
                   )
               AS nombre"
           ),
            DB::Raw("CASE proyectos.proyectosEstadoProyecto
                     WHEN 'SOL' THEN 'Solicitud'
                     WHEN 'EST' THEN 'Estudio'
                     WHEN 'APR' THEN 'Aprobado'
                     WHEN 'REC' THEN 'Rechazado'
                     WHEN 'FOR' THEN 'Formalización'
                     WHEN 'DES' THEN 'Desembolsado'
                     WHEN 'CAN' THEN 'Cancelado'
                     WHEN 'CON' THEN 'Congelado'
                     ELSE '' END AS proyectosEstadoProyecto"),
            DB::Raw("DATE(proyectos.proyectosFechaSolicitud) AS proyectosFechaSolicitud"),
            DB::Raw("CASE proyectos.proyectosTipoProyecto
                     WHEN 'NOR' THEN 'Normalización'
                     WHEN 'MEJ' THEN 'Mejora'
                     WHEN 'CON' THEN 'Construcción'
                     WHEN 'COM' THEN 'Compra'
                     WHEN 'LHO' THEN 'Línea Hogar'
                     ELSE '' END AS proyectosTipoProyecto"),
            DB::Raw("IFNULL(tipos_programa.tipProDescripcion, '') AS tipoProgramaDescripcion"),
            DB::Raw("CASE proyectos.proyectosRemitido
                     WHEN 'S' THEN 'Si'
                     ELSE 'No' END AS proyectosRemitido"),
            DB::Raw("IFNULL(remitente.personasIdentificacion, '') AS remitenteIdentificacion"),
            DB::Raw("IFNULL(remitente.personasNombres, '') AS remitenteNombres"),
            DB::Raw("IFNULL(remitente.personasPrimerApellido, '') AS remitentePrimerApellido"),
            'paises.paisesDescripcion',
            'departamentos.departamentosDescripcion',
            'ciudades.ciudadesDescripcion',
            DB::Raw("IFNULL(comunas.comunasDescripcion, '') AS comunasDescripcion"),
            DB::Raw("IFNULL(barrios.barriosDescripcion, '') AS barriosDescripcion"),
            DB::Raw("CASE proyectos.proyectosZona
                     WHEN 'UR' THEN 'Urbana'
                     WHEN 'RU' THEN 'Rural'
                     ELSE '' END AS proyectosZona"),
            'proyectos.proyectosDireccion',
            DB::Raw("CASE proyectos.proyectosVisitaDomiciliaria
                     WHEN 'S' THEN 'Si'
                     ELSE 'No' END AS proyectosVisitaDomiciliaria"),
            'proyectos.proyectosFechaVisitaDom',
            DB::Raw("CASE proyectos.proyectosPagoEstudioCre
                     WHEN 'S' THEN 'Si'
                     ELSE 'No' END AS proyectosPagoEstudioCre"),
            DB::Raw("CASE proyectos.proyectosReqLicenciaCon
                     WHEN 'S' THEN 'Si'
                     ELSE 'No' END AS proyectosReqLicenciaCon"),
            'proyectos.proyectosFechaInicioEstudio',
            'proyectos.proyectosFechaAproRec',
            'proyectos.proyectosFechaEstInicioObr',
            'proyectos.proyectosValorProyecto',
            'proyectos.proyectosValorSolicitud',
            'proyectos.proyectosValorRecursosSol',
            'proyectos.proyectosValorSubsidios',
            'proyectos.proyectosValorDonaciones',
            'proyectos.proyectosValorCuotaAprobada',
            'proyectos.proyectosValorCapPagoMen',
            'proyectos.proyectosValorAprobado',
            'proyectos.proyectosValorSeguroVida',
            'proyectos.proyectosTasaInteresNMV',
            'proyectos.proyectosTasaInteresEA',
            'proyectos.proyectosNumeroCuotas',
            DB::Raw("IFNULL(bancos.bancosDescripcion, '') AS bancosDescripcion"),
            DB::Raw("CASE proyectos.proyectosTipoCuentaRecaudo
                     WHEN 'AH' THEN 'Ahorros'
                     WHEN 'CO' THEN 'Corriente'
                     ELSE '' END AS proyectosTipoCuentaRecaudo"),
            'proyectos.proyectosNumCuentaRecaudo',
            DB::Raw("CASE proyectos.proyectosEstadoFormalizacion
                     WHEN 'AN' THEN 'Autorización Notaria'
                     WHEN 'FI' THEN 'Firma'
                     WHEN 'IR' THEN 'Ingreso Reg'
                     WHEN 'SR' THEN 'Salida Reg'
                     WHEN 'PA' THEN 'Pagaré'
                     ELSE '' END
            AS proyectosEstadoFormalizacion"),
            'proyectos.proyectosFechaAutNotaria',
            'proyectos.proyectosFechaFirEscrituras',
            'proyectos.proyectosFechaIngresoReg',
            'proyectos.proyectosFechaSalidaReg',
            DB::Raw("CASE proyectos.proyectosAutorizacionDes
                     WHEN 'S' THEN 'Sí'
                     WHEN 'N' THEN 'No'
                     ELSE '' END
            AS proyectosAutorizacionDes"),
            'proyectos.proyectosFechaAutDes',
            'proyectos.proyectosFechaCancelacion',
            DB::Raw("IFNULL(orientadores.orientadoresIdentificacion, '') AS orientadoresIdentificacion"),
            DB::Raw("IFNULL(orientadores.orientadoresNombre, '') AS orientadoresNombre"),
            'proyectosObservaciones',
            'proyectos.usuario_modificacion_nombre',
            'proyectos.updated_at AS fecha_modificacion',
            'proyectos.usuario_creacion_nombre',
            'proyectos.created_at AS fecha_creacion',
         );

      if (isset($this->dto['solicitante']))
         $query->where('personas.personasIdentificacion', 'like', '%' . $this->dto['solicitante'] . '%');

      if (isset($this->dto['tipo']))
         $query->where('proyectos.proyectosTipoProyecto', $this->dto['tipo']);

      if (isset($this->dto['estado']))
         $query->where('proyectos.proyectosEstadoProyecto', $this->dto['estado']);

      if (isset($this->dto['fecha'])) {
         $initialDate = $this->dto['fecha'].' 00:00:00';
         $finalDate = $this->dto['fecha'].' 23:59:59';
         $query->whereBetween('proyectos.proyectosFechaSolicitud', [$initialDate, $finalDate]);
      }

      $query->orderBy('proyectos.id', 'asc');
      return $query;
   }

   public function styles(Worksheet $sheet){
      $sheet->getStyle('A1:BE1')->getFont()->setBold(true);
      $sheet->getStyle('Z:AH')->getNumberFormat()->setFormatCode('$#,##0');
   }

   public function headings(): array
   {
      return [
         "Número Proyecto",
         "Identificación",
         "Nombre Solicitante",
         "Estado Proyecto",
         "Fecha Solicitud",
         "Tipo Proyecto",
         "Tipo Programa",
         "Remitido",
         "Identificación Remitente",
         "Nombre Remitente",
         "Primer Apellido Remitente",
         "País",
         "Departamento",
         "Ciudad",
         "Comuna",
         "Barrio",
         "Zona",
         "Dirección",
         "Visita Domiciliaria",
         "Fecha Visita Domiciliaria",
         "Pago Estudio Crédito",
         "Requiere Licencia Construcción",
         "Fecha Inicio Estudio",
         "Fecha Aprobación/Rechazo",
         "Fecha Estimada Inicio Obra",
         "Valor Proyecto",
         "Valor Solicitud",
         "Valor Recursos Solicitante",
         "Valor Subsidios",
         "Valor Donaciones",
         "Valor Cuota Aprobada",
         "Valor Capacidad Pago Mensual",
         "Valor Aprobado",
         "Valor Seguro Vida",
         "Tasa Interés NMV",
         "Tasa Interés EA",
         "Número Cuotas",
         "Banco Recaudo",
         "Tipo Cuenta Recaudo",
         "Número Cuenta Recaudo",
         "Estado Formalización",
         "Fecha Autorización Notaria",
         "Fecha Firma Escrituras",
         "Fecha Ingreso Registro",
         "Fecha Salida Registro",
         "Autorización Desembolso",
         "Fecha Autorización Desembolso",
         "Fecha Cancelación",
         "Identificación Asesor",
         "Nombre Asesor",
         "Observaciones",
         "Usuario Creación",
         "Fecha Creación",
         "Usuario Modificación",
         "Fecha Modificación",
      ];
   }
}
