<?php

namespace App\Models\PersonasEntidades;

use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use App\Models\Parametrizacion\EPS;
use App\Models\Parametrizacion\Pais;
use Illuminate\Support\Facades\Auth;
use App\Models\Parametrizacion\Barrio;
use App\Models\Parametrizacion\Ciudad;
use App\Models\Parametrizacion\Comuna;
use Illuminate\Database\Eloquent\Model;
use App\Models\Parametrizacion\TipoPiso;
use App\Models\Seguridad\AuditoriaTabla;
use App\Models\Parametrizacion\Ocupacion;
use App\Models\Parametrizacion\TipoTecho;
use App\Models\PersonasEntidades\Familia;
use App\Models\Parametrizacion\EstadoCivil;
use App\Models\Parametrizacion\Departamento;
use App\Models\Parametrizacion\TipoDivision;
use App\Models\Parametrizacion\TipoVivienda;
use App\Models\Parametrizacion\TipoPoblacion;
use App\Models\Parametrizacion\TipoParentesco;
use App\Models\Parametrizacion\GradoEscolaridad;
use App\Models\Parametrizacion\TipoDiscapacidad;
use App\Models\Parametrizacion\TipoIdentificacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Persona extends Model
{
    protected $table = 'personas';

    protected $fillable = [
        'personasIdentificacion',
        'tipo_identificacion_id',
        'personasCategoriaAportes',
        'personasNombres',
        'personasPrimerApellido',
        'personasSegundoApellido',
        'personasFechaNacimiento',
        'pais_nacimiento_id',
        'departamento_nacimiento_id',
        'ciudad_nacimiento_id',
        'personasGenero',
        'estado_civil_id',
        'tipo_parentesco_id',
        'tipo_poblacion_id',
        'tipo_discapacidad_id',
        'personasSeguridadSocial',
        'eps_id',
        'grado_escolaridad_id',
        'personasVehiculo',
        'personasCorreo',
        'personasFechaVinculacion',
        'departamento_id',
        'ciudad_id',
        'comuna_id',
        'barrio_id',
        'personasDireccion',
        'personasZona',
        'personasEstrato',
        'personasTelefonoCasa',
        'personasTelefonoCelular',
        'tipo_vivienda_id',
        'personasTipoPropiedad',
        'personasNumeroEscritura',
        'personasNotariaEscritura',
        'personasFechaEscritura',
        'personasIndicativoPC',
        'personasNumeroHabitaciones',
        'personasNumeroBanos',
        'tipo_techo_id',
        'tipo_piso_id',
        'tipo_division_id',
        'personasSala',
        'personasComedor',
        'personasCocina',
        'personasPatio',
        'personasTerraza',
        'ocupacion_id',
        'personasTipoTrabajo',
        'personasTipoContrato',
        'personasNombreEmpresa',
        'personasTelefonoEmpresa',
        'personasPuntajeProcredito',
        'personasPuntajeDatacredito',
        'departamento_correspondencia_id',
        'ciudad_correspondencia_id',
        'comuna_correspondencia_id',
        'barrio_correspondencia_id',
        'personasCorDireccion',
        'personasCorTelefono',
        'personasIngresosFormales',
        'personasIngresosInformales',
        'personasIngresosArriendo',
        'personasIngresosSubsidios',
        'personasIngresosPaternidad',
        'personasIngresosTerceros',
        'personasIngresosOtros',
        'personasAportesFormales',
        'personasAportesInformales',
        'personasAportesArriendo',
        'personasAportesSubsidios',
        'personasAportesPaternidad',
        'personasAportesTerceros',
        'personasAportesOtros',
        'personasRefNombre1',
        'personasRefTelefono1',
        'personasRefNombre2',
        'personasRefTelefono2',
        'personasObservaciones',
        'personasEstadoTramite',
        'personasEstadoRegistro',
        'familia_id',        
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function tipoIdentificacion(){
        return $this->belongsTo(TipoIdentificacion::class, 'tipo_identificacion_id');
    }

    public function pais(){
        return $this->belongsTo(Pais::class, 'pais_nacimiento_id');
    }

    public function departamentoNacimiento(){
        return $this->belongsTo(Departamento::class, 'departamento_nacimiento_id');
    }

    public function ciudadNacimiento(){
        return $this->belongsTo(Ciudad::class, 'ciudad_nacimiento_id');
    }

    public function estadoCivil(){
        return $this->belongsTo(EstadoCivil::class, 'estado_civil_id');
    }

    public function tipoParentesco(){
        return $this->belongsTo(TipoParentesco::class, 'tipo_parentesco_id');
    }
    
    public function tipoPoblacion(){
        return $this->belongsTo(TipoPoblacion::class, 'tipo_poblacion_id');
    }

    public function tipoDiscapacidad(){
        return $this->belongsTo(TipoDiscapacidad::class, 'tipo_discapacidad_id');
    }

    public function eps(){
        return $this->belongsTo(EPS::class, 'eps_id');
    }

    public function gradoEscolaridad(){
        return $this->belongsTo(GradoEscolaridad::class, 'grado_escolaridad_id');
    }

    public function departamento(){
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function ciudad(){
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }

    public function comuna(){
        return $this->belongsTo(Comuna::class, 'comuna_id');
    }

    public function barrio(){
        return $this->belongsTo(Barrio::class, 'barrio_id');
    }

    public function tipoVivienda(){
        return $this->belongsTo(TipoVivienda::class, 'tipo_vivienda_id');
    }

    public function tipoTecho(){
        return $this->belongsTo(TipoTecho::class, 'tipo_techo_id');
    }

    public function tipoPiso(){
        return $this->belongsTo(TipoPiso::class, 'tipo_piso_id');
    }

    public function tipoDivision(){
        return $this->belongsTo(TipoDivision::class, 'tipo_division_id');
    }

    public function departamentoCor(){
        return $this->belongsTo(Departamento::class, 'departamento_correspondencia_id');
    }

    public function ciudadCor(){
        return $this->belongsTo(Ciudad::class, 'ciudad_correspondencia_id');
    }

    public function comunaCor(){
        return $this->belongsTo(Comuna::class, 'comuna_correspondencia_id');
    }

    public function barrioCor(){
        return $this->belongsTo(Barrio::class, 'barrio_correspondencia_id');
    }

    public function ocupacion(){
        return $this->belongsTo(Ocupacion::class, 'ocupacion_id');
    }

    public function familia(){
        return $this->belongsTo(Familia::class, 'familia_id');
    }

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('personas')
            ->select(
                'id',
                'personasIdentificacion As identificacion',
                'personasEstadoRegistro AS estado',
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(personasNombres), ''),
                        IFNULL(CONCAT(' ',personasPrimerApellido),''),
                        IFNULL(CONCAT(' ',personasSegundoApellido), '')
                        )
                    AS nombre"
                ),
            );
        $query->orderBy('personasNombres', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('personas')
            ->join('tipos_identificacion','tipos_identificacion.id','=','personas.tipo_identificacion_id')
            ->join('paises','paises.id','=','personas.pais_nacimiento_id')
            ->join('departamentos AS departamento_nacimiento','departamento_nacimiento.id','=','personas.departamento_nacimiento_id')
            ->join('ciudades AS ciudad_nacimiento','ciudad_nacimiento.id','=','personas.ciudad_nacimiento_id')
            ->join('estados_civil','estados_civil.id','=','personas.estado_civil_id')
            ->join('tipos_parentesco','tipos_parentesco.id','=','personas.tipo_parentesco_id')
            ->join('tipos_poblacion','tipos_poblacion.id','=','personas.tipo_poblacion_id')
            ->join('tipos_discapacidad','tipos_discapacidad.id','=','personas.tipo_discapacidad_id')
            ->leftJoin('eps','eps.id','=','personas.eps_id')
            ->join('grados_escolaridad','grados_escolaridad.id','=','personas.grado_escolaridad_id')
            ->join('departamentos','departamentos.id','=','personas.departamento_id')
            ->join('ciudades','ciudades.id','=','personas.ciudad_id')
            ->leftJoin('comunas','comunas.id','=','personas.comuna_id')
            ->leftJoin('barrios','barrios.id','=','personas.barrio_id')
            ->join('tipos_vivienda','tipos_vivienda.id','=','personas.tipo_vivienda_id')
            ->join('tipos_techo','tipos_techo.id','=','personas.tipo_techo_id')
            ->join('tipos_piso','tipos_piso.id','=','personas.tipo_piso_id')
            ->join('tipos_division','tipos_division.id','=','personas.tipo_division_id')
            ->join('ocupaciones','ocupaciones.id','=','personas.ocupacion_id')
            ->leftJoin('departamentos AS departamento_cor','departamento_cor.id','=','personas.departamento_correspondencia_id')
            ->leftJoin('ciudades AS ciudad_cor','ciudad_cor.id','=','personas.ciudad_correspondencia_id')
            ->leftJoin('comunas AS comuna_cor','comuna_cor.id','=','personas.comuna_correspondencia_id')
            ->leftJoin('barrios AS barrio_cor','barrio_cor.id','=','personas.barrio_correspondencia_id')
            ->Leftjoin('familias','familias.id','=','personas.familia_id')
            ->select(
                'personas.id',
                'personas.personasIdentificacion',
                'tipos_identificacion.tipIdeDescripcion',
                'personas.personasCategoriaAportes',
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
                'estados_civil.estCivDescripcion',
                'tipos_parentesco.tipParDescripcion',
                'tipos_poblacion.tipPobDescripcion',
                'tipos_discapacidad.tipDisDescripcion',
                'personas.personasSeguridadSocial',
                'eps.epsDescripcion',
                'grados_escolaridad.graEscDescripcion',
                'personas.personasVehiculo',
                'personas.personasCorreo',
                'personas.personasFechaVinculacion',
                'departamentos.departamentosDescripcion',
                'ciudades.ciudadesDescripcion',
                'comunas.comunasDescripcion',
                'barrios.barriosDescripcion',
                'personas.personasDireccion',
                'personas.personasZona',
                'personas.personasEstrato',
                'personas.personasTelefonoCasa',
                'personas.personasTelefonoCelular',
                'tipos_vivienda.tipVivDescripcion',
                'personas.personasTipoPropiedad',
                'personas.personasNumeroEscritura',
                'personas.personasNotariaEscritura',
                'personas.personasFechaEscritura',
                'personas.personasIndicativoPC',
                'personas.personasNumeroHabitaciones',
                'personas.personasNumeroBanos',
                'tipos_techo.tipTecDescripcion',
                'tipos_piso.tipPisDescripcion',
                'tipos_division.tipDivDescripcion',
                'personas.personasSala',
                'personas.personasComedor',
                'personas.personasCocina',
                'personas.personasPatio',
                'personas.personasTerraza',
                'ocupaciones.ocupacionesDescripcion',
                'personas.personasTipoTrabajo',
                'personas.personasTipoContrato',
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
                'personas.personasEstadoTramite',
                'personas.personasEstadoRegistro',
                'familias.identificacion_persona',        
                'personas.usuario_creacion_id',
                'personas.usuario_creacion_nombre',
                'personas.usuario_modificacion_id',
                'personas.usuario_modificacion_nombre',
                'personas.created_at AS fecha_creacion',
                'personas.updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $arrayNames = explode(' ', $dto['nombre']);
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
        if(isset($dto['identificacion'])){
            $query->where('personas.personasIdentificacion', 'like', '%' . $dto['identificacion'] . '%');
        }
        if(isset($dto['categoriaAp'])){
            $query->where('personas.personasCategoriaAportes', $dto['categoriaAp']);
        }
        if(isset($dto['estado'])){
            $query->where('personas.personasEstadoRegistro', $dto['estado']);
        }
        if(isset($dto['familia'])){
            $query->where('familias.identificacion_persona', $dto['familia']);
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'personasIdentificacion'){
                    $query->orderBy('personas.personasIdentificacion', $value);
                }
                if($attribute == 'tipIdeDescripcion'){
                    $query->orderBy('tipos_identificacion.tipIdeDescripcion', $value);
                }
                if($attribute == 'personasCategoriaAportes'){
                    $query->orderBy('personas.personasCategoriaAportes', $value);
                }
                if($attribute == 'nombre'){
                    $query->orderBy('personas.personasNombres', $value);
                }
                if($attribute == 'personasFechaNacimiento'){
                    $query->orderBy('personas.personasFechaNacimiento', $value);
                }
                if($attribute == 'paisesDescripcion'){
                    $query->orderBy('paises.paisesDescripcion', $value);
                }
                if($attribute == 'depNacimiento'){
                    $query->orderBy('departamento_nacimiento.depNacimiento', $value);
                }
                if($attribute == 'ciuNacimiento'){
                    $query->orderBy('ciudad_nacimiento.ciuNacimiento', $value);
                }
                if($attribute == 'personasGenero'){
                    $query->orderBy('personas.personasGenero', $value);
                }
                if($attribute == 'estCivDescripcion'){
                    $query->orderBy('estados_civil.estCivDescripcion', $value);
                }
                if($attribute == 'tipParDescripcion'){
                    $query->orderBy('tipos_parentesco.tipParDescripcion', $value);
                }
                if($attribute == 'tipPobDescripcion'){
                    $query->orderBy('tipos_poblacion.tipPobDescripcion', $value);
                }
                if($attribute == 'tipDisDescripcion'){
                    $query->orderBy('tipos_discapacidad.tipDisDescripcion', $value);
                }
                if($attribute == 'personasSeguridadSocial'){
                    $query->orderBy('personas.personasSeguridadSocial', $value);
                }
                if($attribute == 'epsDescripcion'){
                    $query->orderBy('eps.epsDescripcion', $value);
                }
                if($attribute == 'graEscDescripcion'){
                    $query->orderBy('grados_escolaridad.graEscDescripcion', $value);
                }
                if($attribute == 'personasVehiculo'){
                    $query->orderBy('personas.personasVehiculo', $value);
                }
                if($attribute == 'personasCorreo'){
                    $query->orderBy('personas.personasCorreo', $value);
                }
                if($attribute == 'personasFechaVinculacion'){
                    $query->orderBy('personas.personasFechaVinculacion', $value);
                }
                if($attribute == 'departamentosDescripcion'){
                    $query->orderBy('departamentos.departamentosDescripcion', $value);
                }
                if($attribute == 'ciudadesDescripcion'){
                    $query->orderBy('ciudades.ciudadesDescripcion', $value);
                }
                if($attribute == 'comunasDescripcion'){
                    $query->orderBy('comunas.comunasDescripcion', $value);
                }
                if($attribute == 'barriosDescripcion'){
                    $query->orderBy('barrios.barriosDescripcion', $value);
                }
                if($attribute == 'personasDireccion'){
                    $query->orderBy('personas.personasDireccion', $value);
                }
                if($attribute == 'personasZona'){
                    $query->orderBy('personas.personasZona', $value);
                }
                if($attribute == 'personasEstrato'){
                    $query->orderBy('personas.personasEstrato', $value);
                }
                if($attribute == 'personasTelefonoCasa'){
                    $query->orderBy('personas.personasTelefonoCasa', $value);
                }
                if($attribute == 'personasTelefonoCelular'){
                    $query->orderBy('personas.personasTelefonoCelular', $value);
                }
                if($attribute == 'tipVivDescripcion'){
                    $query->orderBy('tipos_vivienda.tipVivDescripcion', $value);
                }
                if($attribute == 'personasTipoPropiedad'){
                    $query->orderBy('personas.personasTipoPropiedad', $value);
                }
                if($attribute == 'personasNumeroEscritura'){
                    $query->orderBy('personas.personasNumeroEscritura', $value);
                }
                if($attribute == 'personasNotariaEscritura'){
                    $query->orderBy('personas.personasNotariaEscritura', $value);
                }
                if($attribute == 'personasFechaEscritura'){
                    $query->orderBy('personas.personasFechaEscritura', $value);
                }
                if($attribute == 'personasIndicativoPC'){
                    $query->orderBy('personas.personasIndicativoPC', $value);
                }
                if($attribute == 'personasNumeroHabitaciones'){
                    $query->orderBy('personas.personasNumeroHabitaciones', $value);
                }
                if($attribute == 'personasNumeroBanos'){
                    $query->orderBy('personas.personasNumeroBanos', $value);
                }
                if($attribute == 'tipTecDescripcion'){
                    $query->orderBy('tipos_techo.tipTecDescripcion', $value);
                }
                if($attribute == 'tipPisDescripcion'){
                    $query->orderBy('tipos_piso.tipPisDescripcion', $value);
                }
                if($attribute == 'tipDivDescripcion'){
                    $query->orderBy('tipos_division.tipDivDescripcion', $value);
                }
                if($attribute == 'personasSala'){
                    $query->orderBy('personas.personasSala', $value);
                }
                if($attribute == 'personasComedor'){
                    $query->orderBy('personas.personasComedor', $value);
                }
                if($attribute == 'personasCocina'){
                    $query->orderBy('personas.personasCocina', $value);
                }
                if($attribute == 'personasPatio'){
                    $query->orderBy('personas.personasPatio', $value);
                }
                if($attribute == 'personasTerraza'){
                    $query->orderBy('personas.personasTerraza', $value);
                }
                if($attribute == 'ocupacionesDescripcion'){
                    $query->orderBy('ocupaciones.ocupacionesDescripcion', $value);
                }
                if($attribute == 'personasTipoTrabajo'){
                    $query->orderBy('personas.personasTipoTrabajo', $value);
                }
                if($attribute == 'personasTipoContrato'){
                    $query->orderBy('personas.personasTipoContrato', $value);
                }
                if($attribute == 'personasNombreEmpresa'){
                    $query->orderBy('personas.personasNombreEmpresa', $value);
                }
                if($attribute == 'personasTelefonoEmpresa'){
                    $query->orderBy('personas.personasTelefonoEmpresa', $value);
                }
                if($attribute == 'personasPuntajeProcredito'){
                    $query->orderBy('personas.personasPuntajeProcredito', $value);
                }
                if($attribute == 'personasPuntajeDatacredito'){
                    $query->orderBy('personas.personasPuntajeDatacredito', $value);
                }
                if($attribute == 'depCorr'){
                    $query->orderBy('departamento_cor.depCorr', $value);
                }
                if($attribute == 'ciuCorr'){
                    $query->orderBy('ciudad_cor.ciuCorr', $value);
                }
                if($attribute == 'comCorr'){
                    $query->orderBy('comuna_cor.comCorr', $value);
                }
                if($attribute == 'barCorr'){
                    $query->orderBy('barrio_cor.barCorr', $value);
                }
                if($attribute == 'personasCorDireccion'){
                    $query->orderBy('personas.personasCorDireccion', $value);
                }
                if($attribute == 'personasCorTelefono'){
                    $query->orderBy('personas.personasCorTelefono', $value);
                }
                if($attribute == 'personasIngresosFormales'){
                    $query->orderBy('personas.personasIngresosFormales', $value);
                }
                if($attribute == 'personasIngresosInformales'){
                    $query->orderBy('personas.personasIngresosInformales', $value);
                }
                if($attribute == 'personasIngresosArriendo'){
                    $query->orderBy('personas.personasIngresosArriendo', $value);
                }
                if($attribute == 'personasIngresosSubsidios'){
                    $query->orderBy('personas.personasIngresosSubsidios', $value);
                }
                if($attribute == 'personasIngresosPaternidad'){
                    $query->orderBy('personas.personasIngresosPaternidad', $value);
                }
                if($attribute == 'personasIngresosTerceros'){
                    $query->orderBy('personas.personasIngresosTerceros', $value);
                }
                if($attribute == 'personasIngresosOtros'){
                    $query->orderBy('personas.personasIngresosOtros', $value);
                }
                if($attribute == 'personasAportesFormales'){
                    $query->orderBy('personas.personasAportesFormales', $value);
                }
                if($attribute == 'personasAportesInformales'){
                    $query->orderBy('personas.personasAportesInformales', $value);
                }
                if($attribute == 'personasAportesArriendo'){
                    $query->orderBy('personas.personasAportesArriendo', $value);
                }
                if($attribute == 'personasAportesSubsidios'){
                    $query->orderBy('personas.personasAportesSubsidios', $value);
                }
                if($attribute == 'personasAportesPaternidad'){
                    $query->orderBy('personas.personasAportesPaternidad', $value);
                }
                if($attribute == 'personasAportesTerceros'){
                    $query->orderBy('personas.personasAportesTerceros', $value);
                }
                if($attribute == 'personasAportesOtros'){
                    $query->orderBy('personas.personasAportesOtros', $value);
                }
                if($attribute == 'personasRefNombre1'){
                    $query->orderBy('personas.personasRefNombre1', $value);
                }
                if($attribute == 'personasRefTelefono1'){
                    $query->orderBy('personas.personasRefTelefono1', $value);
                }
                if($attribute == 'personasRefNombre2'){
                    $query->orderBy('personas.personasRefNombre2', $value);
                }
                if($attribute == 'personasRefTelefono2'){
                    $query->orderBy('personas.personasRefTelefono2', $value);
                }
                if($attribute == 'personasObservaciones'){
                    $query->orderBy('personas.personasObservaciones', $value);
                }
                if($attribute == 'personasEstadoTramite'){
                    $query->orderBy('personas.personasEstadoTramite', $value);
                }
                if($attribute == 'personasEstadoRegistro'){
                    $query->orderBy('personas.personasEstadoRegistro', $value);
                }
                if($attribute == 'identificacion_persona'){
                    $query->orderBy('familias.identificacion_persona', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('personas.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('personas.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('personas.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('personas.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("personas.updated_at", "desc");
        }
        $personas = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($personas ?? [] as $persona){
            array_push($datos, $persona);
        }

        $cantidadPersonas = count($personas);
        $to = isset($personas) && $cantidadPersonas > 0 ? $personas->currentPage() * $personas->perPage() : null;
        $to = isset($to) && isset($personas) && $to > $personas->total() && $cantidadPersonas > 0 ? $personas->total() : $to;
        $from = isset($to) && isset($personas) && $cantidadPersonas > 0 ?
            ( $personas->perPage() > $to ? 1 : ($to - $cantidadPersonas) + 1 )
            : null;
        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($personas) && $cantidadPersonas > 0 ? +$personas->perPage() : 0,
            'pagina_actual' => isset($personas) && $cantidadPersonas > 0 ? $personas->currentPage() : 1,
            'ultima_pagina' => isset($personas) && $cantidadPersonas > 0 ? $personas->lastPage() : 0,
            'total' => isset($personas) && $cantidadPersonas > 0 ? $personas->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $persona = Persona::find($id);
        $tipoIdentificacion = $persona->tipoIdentificacion;
        $paisNacimiento = $persona->pais;
        $departamentoNacimiento = $persona->departamentoNacimiento;
        $ciudadNacimiento = $persona->ciudadNacimiento;
        $estadoCivil = $persona->estadoCivil;
        $tipoParentesco = $persona->tipoParentesco;
        $tipoPoblacion = $persona->tipoPoblacion;
        $tipoDicapacidad = $persona->tipoDiscapacidad;
        $eps = $persona->eps;
        $gradoEscolaridad = $persona->gradoEscolaridad;
        $departamento = $persona->departamento;
        $ciudad = $persona->ciudad;
        $comuna = $persona->comuna;
        $barrio = $persona->barrio;
        $tipoVivienda = $persona->tipoVivienda;
        $tipoTecho = $persona->tipoTecho;
        $tipoPiso = $persona->tipoPiso;
        $tipoDivision = $persona->tipoDivision;
        $ocupacion = $persona->ocupacion;
        $departamentoCor = $persona->departamentoCor;
        $ciudadCor = $persona->ciudadCor;
        $comunaCor = $persona->comunaCor;
        $barrioCor = $persona->barrioCor;
        $familia = $persona->familia;

        return [
            'id' => $persona->id,
            'personasIdentificacion' => $persona->personasIdentificacion,
            'tipo_identificacion_id' => $persona->tipo_identificacion_id,
            'personasCategoriaAportes' => $persona->personasCategoriaAportes,
            'personasNombres' => $persona->personasNombres,
            'personasPrimerApellido' => $persona->personasPrimerApellido,
            'personasSegundoApellido' => $persona->personasSegundoApellido,
            'personasFechaNacimiento' => $persona->personasFechaNacimiento,
            'pais_nacimiento_id' => $persona->pais_nacimiento_id,
            'departamento_nacimiento_id' => $persona->departamento_nacimiento_id,
            'ciudad_nacimiento_id' => $persona->ciudad_nacimiento_id,
            'personasGenero' => $persona->personasGenero,
            'estado_civil_id' => $persona->estado_civil_id,
            'tipo_parentesco_id' => $persona->tipo_parentesco_id,
            'tipo_poblacion_id' => $persona->tipo_poblacion_id,
            'tipo_discapacidad_id' => $persona->tipo_discapacidad_id,
            'personasSeguridadSocial' => $persona->personasSeguridadSocial,
            'eps_id' => $persona->eps_id,
            'grado_escolaridad_id' => $persona->grado_escolaridad_id,
            'personasVehiculo' => $persona->personasVehiculo,
            'personasCorreo' => $persona->personasCorreo,
            'personasFechaVinculacion' => $persona->personasFechaVinculacion,
            'departamento_id' => $persona->departamento_id,
            'ciudad_id' => $persona->ciudad_id,
            'comuna_id' => $persona->comuna_id,
            'barrio_id' => $persona->barrio_id,
            'personasDireccion' => $persona->personasDireccion,
            'personasZona' => $persona->personasZona,
            'personasEstrato' => $persona->personasEstrato,
            'personasTelefonoCasa' => $persona->personasTelefonoCasa,
            'personasTelefonoCelular' => $persona->personasTelefonoCelular,
            'tipo_vivienda_id' => $persona->tipo_vivienda_id,
            'personasTipoPropiedad' => $persona->personasTipoPropiedad,
            'personasNumeroEscritura' => $persona->personasNumeroEscritura,
            'personasNotariaEscritura' => $persona->personasNotariaEscritura,
            'personasFechaEscritura' => $persona->personasFechaEscritura,
            'personasIndicativoPC' => $persona->personasIndicativoPC,
            'personasNumeroHabitaciones' => $persona->personasNumeroHabitaciones,
            'personasNumeroBanos' => $persona->personasNumeroBanos,
            'tipo_techo_id' => $persona->tipo_techo_id,
            'tipo_piso_id' => $persona->tipo_piso_id,
            'tipo_division_id' => $persona->tipo_division_id,
            'personasSala' => $persona->personasSala,
            'personasComedor' => $persona->personasComedor,
            'personasCocina' => $persona->personasCocina,
            'personasPatio' => $persona->personasPatio,
            'personasTerraza' => $persona->personasTerraza,
            'ocupacion_id' => $persona->ocupacion_id,
            'personasTipoTrabajo' => $persona->personasTipoTrabajo,
            'personasTipoContrato' => $persona->personasTipoContrato,
            'personasNombreEmpresa' => $persona->personasNombreEmpresa,
            'personasTelefonoEmpresa' => $persona->personasTelefonoEmpresa,
            'personasPuntajeProcredito' => $persona->personasPuntajeProcredito,
            'personasPuntajeDatacredito' => $persona->personasPuntajeDatacredito,
            'departamento_correspondencia_id' => $persona->departamento_correspondencia_id,
            'ciudad_correspondencia_id' => $persona->ciudad_correspondencia_id,
            'comuna_correspondencia_id' => $persona->comuna_correspondencia_id,
            'barrio_correspondencia_id' => $persona->barrio_correspondencia_id,
            'personasCorDireccion' => $persona->personasCorDireccion,
            'personasCorTelefono' => $persona->personasCorTelefono,
            'personasIngresosFormales' => $persona->personasIngresosFormales,
            'personasIngresosInformales' => $persona->personasIngresosInformales,
            'personasIngresosArriendo' => $persona->personasIngresosArriendo,
            'personasIngresosSubsidios' => $persona->personasIngresosSubsidios,
            'personasIngresosPaternidad' => $persona->personasIngresosPaternidad,
            'personasIngresosTerceros' => $persona->personasIngresosTerceros,
            'personasIngresosOtros' => $persona->personasIngresosOtros,
            'personasAportesFormales' => $persona->personasAportesFormales,
            'personasAportesInformales' => $persona->personasAportesInformales,
            'personasAportesArriendo' => $persona->personasAportesArriendo,
            'personasAportesSubsidios' => $persona->personasAportesSubsidios,
            'personasAportesPaternidad' => $persona->personasAportesPaternidad,
            'personasAportesTerceros' => $persona->personasAportesTerceros,
            'personasAportesOtros' => $persona->personasAportesOtros,
            'personasRefNombre1' => $persona->personasRefNombre1,
            'personasRefTelefono1' => $persona->personasRefTelefono1,
            'personasRefNombre2' => $persona->personasRefNombre2,
            'personasRefTelefono2' => $persona->personasRefTelefono2,
            'personasObservaciones' => $persona->personasObservaciones,
            'personasEstadoTramite' => $persona->personasEstadoTramite,
            'personasEstadoRegistro' => $persona->personasEstadoRegistro,
            'familia_id' => $persona->familia_id,
            'usuario_creacion_id' => $persona->usuario_creacion_id,
            'usuario_creacion_nombre' => $persona->usuario_creacion_nombre,
            'usuario_modificacion_id' => $persona->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $persona->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($persona->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($persona->updated_at))->format("Y-m-d H:i:s"),
            'tipoIdentificacion' => isset($tipoIdentificacion) ? [
                'id' => $tipoIdentificacion->id,
                'nombre' => $tipoIdentificacion->tipIdeDescripcion
            ] : null,
            'paisNacimiento' => isset($paisNacimiento) ? [
                'id' => $paisNacimiento->id,
                'nombre' => $paisNacimiento->paisesDescripcion
            ] : null,
            'departamentoNacimiento' => isset($departamentoNacimiento) ? [
                'id' => $departamentoNacimiento->id,
                'nombre' => $departamentoNacimiento->departamentosDescripcion
            ] : null,
            'ciudadNacimiento' => isset($ciudadNacimiento) ? [
                'id' => $ciudadNacimiento->id,
                'nombre' => $ciudadNacimiento->ciudadesDescripcion
            ] : null,
            'estadoCivil' => isset($estadoCivil) ? [
                'id' => $estadoCivil->id,
                'nombre' => $estadoCivil->estCivDescripcion
            ] : null,
            'tipoParentesco' => isset($tipoParentesco) ? [
                'id' => $tipoParentesco->id,
                'nombre' => $tipoParentesco->tipParDescripcion
            ] : null,
            'tipoPoblacion' => isset($tipoPoblacion) ? [
                'id' => $tipoPoblacion->id,
                'nombre' => $tipoPoblacion->tipPobDescripcion
            ] : null,
            'tipoDicapacidad' => isset($tipoDicapacidad) ? [
                'id' => $tipoDicapacidad->id,
                'nombre' => $tipoDicapacidad->tipDisDescripcion
            ] : null,
            'eps' => isset($eps) ? [
                'id' => $eps->id,
                'nombre' => $eps->epsDescripcion
            ] : null,
            'gradoEscolaridad' => isset($gradoEscolaridad) ? [
                'id' => $gradoEscolaridad->id,
                'nombre' => $gradoEscolaridad->graEscDescripcion
            ] : null,
            'departamento' => isset($departamento) ? [
                'id' => $departamento->id,
                'nombre' => $departamento->departamentosDescripcion
            ] : null,
            'ciudad' => isset($ciudad) ? [
                'id' => $ciudad->id,
                'nombre' => $ciudad->ciudadesDescripcion
            ] : null,
            'comuna' => isset($comuna) ? [
                'id' => $comuna->id,
                'nombre' => $comuna->comunasDescripcion
            ] : null,
            'barrio' => isset($barrio) ? [
                'id' => $barrio->id,
                'nombre' => $barrio->barriosDescripcion
            ] : null,
            'tipoVivienda' => isset($tipoVivienda) ? [
                'id' => $tipoVivienda->id,
                'nombre' => $tipoVivienda->tipVivDescripcion
            ] : null,
            'tipoTecho' => isset($tipoTecho) ? [
                'id' => $tipoTecho->id,
                'nombre' => $tipoTecho->tipTecDescripcion
            ] : null,
            'tipoPiso' => isset($tipoPiso) ? [
                'id' => $tipoPiso->id,
                'nombre' => $tipoPiso->tipPisDescripcion
            ] : null,
            'tipoDivision' => isset($tipoDivision) ? [
                'id' => $tipoDivision->id,
                'nombre' => $tipoDivision->tipDivDescripcion
            ] : null,
            'ocupacion' => isset($ocupacion) ? [
                'id' => $ocupacion->id,
                'nombre' => $ocupacion->ocupacionesDescripcion
            ] : null,
            'departamentoCor' => isset($departamentoCor) ? [
                'id' => $departamentoCor->id,
                'nombre' => $departamentoCor->departamentosDescripcion
            ] : null,
            'ciudadCor' => isset($ciudadCor) ? [
                'id' => $ciudadCor->id,
                'nombre' => $ciudadCor->ciudadesDescripcion
            ] : null,
            'comunaCor' => isset($comunaCor) ? [
                'id' => $comunaCor->id,
                'nombre' => $comunaCor->comunasDescripcion
            ] : null,
            'barrioCor' => isset($barrioCor) ? [
                'id' => $barrioCor->id,
                'nombre' => $barrioCor->barriosDescripcion
            ] : null,
            'familia' => isset($familia) ? [
                'id' => $familia->id,
                'identificacion' => $familia->identificacion_persona,
                'nombre' => $familia->persona->personasNombres.' '.$familia->persona->personasPrimerApellido.' '.$familia->persona->personasSegundoApellido,
            ] : null,
        ];
    }

    public static function modificarOCrear($dto)
    {
        $user = Auth::user();
        $usuario = $user->usuario();

        if(!isset($dto['id'])){
            $dto['usuario_creacion_id'] = $usuario->id ?? ($dto['usuario_creacion_id'] ?? null);
            $dto['usuario_creacion_nombre'] = $usuario->nombre ?? ($dto['usuario_creacion_nombre'] ?? null);
        }
        if(isset($usuario) || isset($dto['usuario_modificacion_id'])){
            $dto['usuario_modificacion_id'] = $usuario->id ?? ($dto['usuario_modificacion_id'] ?? null);
            $dto['usuario_modificacion_nombre'] = $usuario->nombre ?? ($dto['usuario_modificacion_nombre'] ?? null);
        }

        // Consultar módulos
        $persona = isset($dto['id']) ? Persona::find($dto['id']) : new Persona();

        // Guardar objeto original para auditoria
        $personaOriginal = $persona->toJson();

        $persona->fill($dto);
        $guardado = $persona->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la persona.", $persona);
        }

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $persona->id,
            'nombre_recurso' => Persona::class,
            'descripcion_recurso' => $persona->personasNombres,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $personaOriginal : $persona->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $persona->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);

        // --- Para proceso de Calculo Aportes Familia ---

        $registroInicial = json_decode($personaOriginal);
        if(!isset($dto['id'])&&isset($dto['familia_id'])){
            Familia::calcularAportes($dto);
        } else if (isset($dto['id'])){
            $data['usuario_creacion_id'] = $usuario->id;
            $data['usuario_creacion_nombre'] = $usuario->nombre;
            if(isset($dto['familia_id'])){
                $dto['usuario_creacion_id'] = $usuario->id;
                $dto['usuario_creacion_nombre'] = $usuario->nombre;
                if($registroInicial->familia_id){
                    $data['familia_id'] = $registroInicial->familia_id;
                    if($registroInicial->familia_id==$dto['familia_id']){
                        Familia::calcularAportes($dto);
                    } else {
                        Familia::calcularAportes($dto);
                        Familia::calcularAportes($data);
                    }
                } else {
                    Familia::calcularAportes($dto);
                }
            } else if ($registroInicial->familia_id){
                $data['familia_id'] = $registroInicial->familia_id;
                Familia::calcularAportes($data);
            }
        }
        
        return Persona::cargar($persona->id);
        // return $persona;
    }

    public static function eliminar($id)
    {
        $user = Auth::user();
        $usuario = $user->usuario();
        $persona = Persona::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $persona->id,
            'nombre_recurso' => Persona::class,
            'descripcion_recurso' => $persona->personasNombres,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $persona->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        if($persona->familia_id){
            $dto['usuario_creacion_id'] = $usuario->id;
            $dto['usuario_creacion_nombre'] = $usuario->nombre;
            $dto['familia_id'] = $persona->familia_id;
            Familia::calcularAportes($dto);
        }

        return $persona->delete();
    }

    use HasFactory;
}
