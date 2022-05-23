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

class PersonaInformacion implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles
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
    $query = DB::table('personas')
        ->join('tipos_identificacion','tipos_identificacion.id','=','personas.tipo_identificacion_id')
        ->join('paises','paises.id','=','personas.pais_nacimiento_id')
        ->join('departamentos AS departamento_nacimiento','departamento_nacimiento.id','=','personas.departamento_nacimiento_id')
        ->join('ciudades AS ciudad_nacimiento','ciudad_nacimiento.id','=','personas.ciudad_nacimiento_id')
        ->join('estados_civil','estados_civil.id','=','personas.estado_civil_id')
        ->join('tipos_parentesco','tipos_parentesco.id','=','personas.tipo_parentesco_id')
        ->join('tipos_poblacion','tipos_poblacion.id','=','personas.tipo_poblacion_id')
        ->join('tipos_discapacidad','tipos_discapacidad.id','=','personas.tipo_discapacidad_id')
        ->join('eps','eps.id','=','personas.eps_id')
        ->join('grados_escolaridad','grados_escolaridad.id','=','personas.grado_escolaridad_id')
        ->join('departamentos','departamentos.id','=','personas.departamento_id')
        ->join('ciudades','ciudades.id','=','personas.ciudad_id')
        ->join('comunas','comunas.id','=','personas.comuna_id')
        ->join('barrios','barrios.id','=','personas.barrio_id')
        ->join('tipos_vivienda','tipos_vivienda.id','=','personas.tipo_vivienda_id')
        ->join('tipos_techo','tipos_techo.id','=','personas.tipo_techo_id')
        ->join('tipos_piso','tipos_piso.id','=','personas.tipo_piso_id')
        ->join('tipos_division','tipos_division.id','=','personas.tipo_division_id')
        ->join('ocupaciones','ocupaciones.id','=','personas.ocupacion_id')
        ->join('departamentos AS departamento_cor','departamento_cor.id','=','personas.departamento_correspondencia_id')
        ->join('ciudades AS ciudad_cor','ciudad_cor.id','=','personas.ciudad_correspondencia_id')
        ->join('comunas AS comuna_cor','comuna_cor.id','=','personas.comuna_correspondencia_id')
        ->join('barrios AS barrio_cor','barrio_cor.id','=','personas.barrio_correspondencia_id')
        ->Leftjoin('familias','familias.id','=','personas.familia_id')
        ->select(
            'tipos_identificacion.tipIdeDescripcion',
            'personas.personasIdentificacion',
            DB::Raw('CASE personas.personasCategoriaAportes
                    WHEN "SO" THEN "Solicitante"
                    WHEN "AG" THEN "Aportante Grupo Familiar"
                    WHEN "NG" THEN "No Aportante Grupo Familiar"
                    WHEN "CO" THEN "Codeudor"
                    ELSE "" END AS personasCategoriaAportes'
            ),
            DB::Raw(
                "CONCAT(
                    IFNULL(CONCAT(personasNombres), ''),
                    IFNULL(CONCAT(' ',personasPrimerApellido),''),
                    IFNULL(CONCAT(' ',personasSegundoApellido), '')
                    )
                AS nombre"
            ),
            'personas.personasFechaNacimiento',
            'paises.paisesDescripcion',
            'departamento_nacimiento.departamentosDescripcion as depNacimiento',
            'ciudad_nacimiento.ciudadesDescripcion as ciuNacimiento',
            'personas.personasGenero',
            DB::Raw('CASE personas.personasGenero
                    WHEN "MA" THEN "Masculino"
                    WHEN "FE" THEN "Femenino"
                    ELSE "" END AS personasGenero'
            ),
            'estados_civil.estCivDescripcion',
            'tipos_parentesco.tipParDescripcion',
            'tipos_poblacion.tipPobDescripcion',
            'tipos_discapacidad.tipDisDescripcion',
            DB::Raw('CASE personas.personasSeguridadSocial
                    WHEN "SU" THEN "Subsidiado"
                    WHEN "CO" THEN "Contributivo"
                    ELSE "" END AS personasSeguridadSocial'
            ),
            'eps.epsDescripcion',
            'grados_escolaridad.graEscDescripcion',
            DB::Raw('CASE personas.personasVehiculo
                    WHEN "S" THEN "Sí"
                    WHEN "N" THEN "No"
                    ELSE "" END AS personasVehiculo'
            ),
            'personas.personasCorreo',
            'personas.personasFechaVinculacion',
            'departamentos.departamentosDescripcion',
            'ciudades.ciudadesDescripcion',
            'comunas.comunasDescripcion',
            'barrios.barriosDescripcion',
            'personas.personasDireccion',
            DB::Raw('CASE personas.personasZona
                    WHEN "UR" THEN "Urbana"
                    WHEN "RU" THEN "Rural"
                    ELSE "" END AS personasZona'
            ),
            'personas.personasEstrato',
            'personas.personasTelefonoCasa',
            'personas.personasTelefonoCelular',
            'tipos_vivienda.tipVivDescripcion',
            DB::Raw('CASE personas.personasTipoPropiedad
                    WHEN "ES" THEN "Escritura"
                    WHEN "CO" THEN "Compraventa"
                    WHEN "PO" THEN "Posesión"
                    WHEN "SD" THEN "Sin Documento"
                    ELSE "" END AS personasTipoPropiedad'
            ),
            'personas.personasNumeroEscritura',
            'personas.personasNotariaEscritura',
            'personas.personasFechaEscritura',
            DB::Raw('CASE personas.personasIndicativoPC
                    WHEN "PO" THEN "Posesión"
                    WHEN "CV" THEN "Compraventa"
                    ELSE "" END AS personasIndicativoPC'
            ),
            'personas.personasNumeroHabitaciones',
            'personas.personasNumeroBanos',
            'tipos_techo.tipTecDescripcion',
            'tipos_piso.tipPisDescripcion',
            'tipos_division.tipDivDescripcion',
            DB::Raw('CASE personas.personasSala
                    WHEN "S" THEN "Sí"
                    WHEN "N" THEN "No"
                    ELSE "" END AS personasSala'
            ),
            DB::Raw('CASE personas.personasComedor
                    WHEN "S" THEN "Sí"
                    WHEN "N" THEN "No"
                    ELSE "" END AS personasComedor'
            ),
            DB::Raw('CASE personas.personasCocina
                    WHEN "S" THEN "Sí"
                    WHEN "N" THEN "No"
                    ELSE "" END AS personasCocina'
            ),
            DB::Raw('CASE personas.personasPatio
                    WHEN "S" THEN "Sí"
                    WHEN "N" THEN "No"
                    ELSE "" END AS personasPatio'
            ),
            DB::Raw('CASE personas.personasTerraza
                    WHEN "S" THEN "Sí"
                    WHEN "N" THEN "No"
                    ELSE "" END AS personasTerraza'
            ),
            'ocupaciones.ocupacionesDescripcion',
            DB::Raw('CASE personas.personasTipoTrabajo
                    WHEN "FO" THEN "Formal"
                    WHEN "IN" THEN "Informal"
                    WHEN "PE" THEN "Pensionado"
                    ELSE "" END AS personasTipoTrabajo'
            ),
            DB::Raw('CASE personas.personasTipoContrato
                    WHEN "IN" THEN "Indefinidio"
                    WHEN "TF" THEN "Término Fijo"
                    WHEN "OL" THEN "Por Obra Labor"
                    WHEN "PS" THEN "Prestación de Servicios"
                    ELSE "" END AS personasTipoContrato'
            ),
            'personas.personasNombreEmpresa',
            'personas.personasTelefonoEmpresa',
            'personas.personasPuntajeProcredito',
            'personas.personasPuntajeDatacredito',
            'departamento_cor.departamentosDescripcion as depCorr',
            'ciudad_cor.ciudadesDescripcion as ciuCorr',
            'comuna_cor.comunasDescripcion as comCorr',
            'barrio_cor.barriosDescripcion as barCorr',
            'personas.personasCorDireccion',
            'personas.personasCorTelefono',
            'personas.personasIngresosFormales',
            'personas.personasIngresosInformales',
            'personas.personasIngresosArriendo',
            'personas.personasIngresosSubsidios',
            'personas.personasIngresosPaternidad',
            'personas.personasIngresosTerceros',
            'personas.personasIngresosOtros',
            'personas.personasAportesFormales',
            'personas.personasAportesInformales',
            'personas.personasAportesArriendo',
            'personas.personasAportesSubsidios',
            'personas.personasAportesPaternidad',
            'personas.personasAportesTerceros',
            'personas.personasAportesOtros',
            'personas.personasRefNombre1',
            'personas.personasRefTelefono1',
            'personas.personasRefNombre2',
            'personas.personasRefTelefono2',
            'personas.personasObservaciones',
            DB::Raw('CASE personas.personasEstadoTramite
                    WHEN "SO" THEN "Solicitud"
                    WHEN "PR" THEN "Prospecto"
                    WHEN "ES" THEN "Estudio"
                    WHEN "AP" THEN "Aprobado"
                    WHEN "RE" THEN "Rechazado"
                    ELSE "" END AS personasEstadoTramite'
            ),
            DB::Raw('CASE personas.personasEstadoRegistro
                    WHEN "IN" THEN "Inactivo"
                    WHEN "AC" THEN "Activo"
                    ELSE "" END AS personasEstadoRegistro'
            ),
            'familias.identificacion_persona',        
            'personas.usuario_modificacion_nombre',
            'personas.updated_at AS fecha_modificacion',
            'personas.usuario_creacion_nombre',
            'personas.created_at AS fecha_creacion',
        );

        if(isset($this->dto['nombre'])){
            $arrayNames = explode(' ', $this->dto['nombre']);
            $long = count($arrayNames);
            if($long===1){
                $query->orWhere('personas.personasNombres', 'like', '%' . $arrayNames[0] . '%');
                $query->orWhere('personas.personasPrimerApellido', 'like', '%' . $arrayNames[0] . '%');
                $query->orWhere('personas.personasSegundoApellido', 'like', '%' . $arrayNames[0] . '%');
            }
            if($long===2){
                $query->orWhere('personas.personasNombres', 'like', '%'.$arrayNames[0].' '.$arrayNames[1].'%');
                $query->orWhereRaw("CONCAT(TRIM(personas.personasNombres), ' ', 
                    TRIM(personas.personasPrimerApellido)) like ?",
                    ['%'.$arrayNames[0].' '.$arrayNames[1].'%']);
                $query->orWhereRaw("CONCAT(TRIM(personas.personasPrimerApellido), ' ', 
                    TRIM(personas.personasSegundoApellido)) like ?",
                    ['%'.$arrayNames[0].' '.$arrayNames[1].'%']);
                $query->orWhereRaw("CONCAT(TRIM(personas.personasPrimerApellido), ' ', 
                    TRIM(personas.personasNombres)) like ?",
                    ['%'.$arrayNames[0].' '.$arrayNames[1].'%']);
            }
            if($long===3){
                $query->orWhereRaw("CONCAT(TRIM(personas.personasNombres), ' ', 
                    TRIM(personas.personasPrimerApellido)) like ?",
                    ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
                $query->orWhereRaw("CONCAT(
                    TRIM(personas.personasNombres), ' ', 
                    TRIM(personas.personasPrimerApellido)) like ?",
                    ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
                $query->orWhereRaw("CONCAT(
                    TRIM(personas.personasNombres), ' ', 
                    TRIM(personas.personasPrimerApellido), ' ', 
                    TRIM(personas.personasSegundoApellido)) like ?",
                    ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
                $query->orWhereRaw("CONCAT(
                    TRIM(personas.personasPrimerApellido), ' ', 
                    TRIM(personas.personasSegundoApellido), ' ', 
                    TRIM(personas.personasNombres)) like ?",
                    ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
            }
            if($long===4){
                $query->orWhereRaw("CONCAT(
                    TRIM(personas.personasNombres), ' ',
                    TRIM(personas.personasPrimerApellido), ' ', 
                    TRIM(personas.personasSegundoApellido)) like ?",
                    ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].' '.$arrayNames[3].'%']);
                $query->orWhereRaw("CONCAT(
                    TRIM(personas.personasPrimerApellido), ' ', 
                    TRIM(personas.personasSegundoApellido), ' ', 
                    TRIM(personas.personasNombres)) like ?",
                    ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].' '.$arrayNames[3].'%']);
            }
        }
        if(isset($this->dto['identificacion'])){
            $query->where('personas.personasIdentificacion', 'like', '%' . $this->dto['identificacion'] . '%');
        }
        if(isset($this->dto['categoriaAp'])){
            $query->where('personas.personasCategoriaAportes', $this->dto['categoriaAp']);
        }
        if(isset($this->dto['estado'])){
            $query->where('personas.personasEstadoRegistro', $this->dto['estado']);
        }
        if(isset($this->dto['familia'])){
            $query->where('personas.familia_id', $this->dto['familia']);
        }

        $query->orderBy('personas.personasNombres', 'asc');
        return $query;
    }
    
    public function styles(Worksheet $sheet){
        $sheet->getStyle('A1:CE1')->getFont()->setBold(true);
        $sheet->getStyle('BF:BS')->getNumberFormat()->setFormatCode('$#,##0');    
    }

    public function headings(): array
    {
        return [
            "Tipo Identificación",
            "Identificación",
            "Cat. Aportes",
            "Nombre",
            "Fecha Nacimiento",
            "Pais Nacimiento",
            "Dpto. Nacimiento",
            "Ciudad Nacimiento",
            "Género",
            "Est. Civil",
            "Parentesco",
            "Tipo Población",
            "Tipo Discapacidad",
            "Seg. Social",
            "EPS",
            "Grado Escolaridad",
            "Vehículo",
            "Correo",
            "Fecha Vinculación",
            "Departamento",
            "Ciudad",
            "Comuna",
            "Barrio",
            "Dirección",
            "Zona",
            "Estrato",
            "Teléfono Casa",
            "Teléfono Cel.",
            "Tipo Vivienda",
            "Tipo Propiedad",
            "Núm. Escritura",
            "Notaria Escritura",
            "Fecha Escritura",
            "Indicativo PC",
            "Núm. Habitaciones",
            "Núm. Baños",
            "Tipo Techo",
            "Tipo Piso",
            "Tipo Disivión",
            "Sala",
            "Comedor",
            "Cocina",
            "Patio",
            "Terraza",
            "Ocupación",
            "Tipo Trabajo",
            "Tipo Contrato",
            "Nombre Empresa",
            "Teléf. Empresa",
            "Punt. Procrédito",
            "Punt. Datacrédito",
            "Dpto. Correspodencia",
            "Ciudad Correspodencia",
            "Comuna Correspodencia",
            "Barrio Correspodencia",
            "Dir. Correspodencia",
            "Teléf. Correspodencia",
            "Ing. Formales",
            "Ing. Informales",
            "Ing. Arriendos",
            "Ing. Subsidios",
            "Ing. Paternidad",
            "Ing. Terceros",
            "Ing. Otros",
            "Apor. Formales",
            "Apor. Informales",
            "Apor. Arriendos",
            "Apor. Subsidios",
            "Apor. Paternidad",
            "Apor. Terceros",
            "Apor. Otros",
            "Ref. 1 Nombre",
            "Ref. 1 Teléfono",
            "Ref. 2 Nombre",
            "Ref. 2 Teléfono",
            "Observaciones",
            "Est. Trámite",
            "Est. Registro",
            "Familia Id",
            "Usuario Modificación",
            "Fecha Modificación",
            "Usuario Creación",
            "Fecha Creación",
        ];
    }
}
