<?php

namespace App\Models\Proyectos;

use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Parametrizacion\Banco;
use App\Models\Parametrizacion\Barrio;
use App\Models\Parametrizacion\Ciudad;
use App\Models\Parametrizacion\Comuna;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use App\Models\PersonasEntidades\Persona;
use App\Models\Parametrizacion\Departamento;
use App\Models\Parametrizacion\TipoPrograma;
use App\Models\PersonasEntidades\Orientador;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proyecto extends Model
{
    protected $table = 'proyectos'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'persona_id',
        'proyectosEstadoProyecto',
        'proyectosFechaSolicitud',
        'proyectosTipoProyecto',
        'tipo_programa_id',
        'proyectosRemitido',
        'remitido_id',
        'pais_id',
        'departamento_id',
        'ciudad_id',
        'comuna_id',
        'barrio_id',
        'proyectosZona',
        'proyectosDireccion',
        'proyectosVisitaDomiciliaria',
        'proyectosFechaVisitaDom',
        'proyectosPagoEstudioCre',
        'proyectosReqLicenciaCon',
        'proyectosFechaInicioEstudio',
        'proyectosFechaAproRec',
        'proyectosFechaEstInicioObr',
        'proyectosValorProyecto',
        'proyectosValorSolicitud',
        'proyectosValorRecursosSol',
        'proyectosValorSubsidios',
        'proyectosValorDonaciones',
        'proyectosValorCuotaAprobada',
        'proyectosValorCapPagoMen',
        'proyectosValorAprobado',
        'proyectosValorSeguroVida',
        'proyectosTasaInteresNMV',
        'proyectosTasaInteresEA',
        'proyectosNumeroCuotas',
        'banco_id',
        'proyectosTipoCuentaRecaudo',
        'proyectosNumCuentaRecaudo',
        'proyectosEstadoFormalizacion',
        'proyectosFechaAutNotaria',
        'proyectosFechaFirEscrituras',
        'proyectosFechaIngresosReg',
        'proyectosAutorizacionDes',
        'proyectosFechaAutDes',
        'proyectosFechaCancelacion',
        'orientador_id',
        'proyectosObservaciones',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function solicitante(){
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function tipoPrograma(){
        return $this->belongsTo(TipoPrograma::class, 'tipo_programa_id');
    }

    public function remitente(){
        return $this->belongsTo(Persona::class, 'remitente_id');
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

    public function banco(){
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function orientador(){
        return $this->belongsTo(Orientador::class, 'orientador_id');
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('proyectos')
            ->join('personas', 'proyectos.persona_id', 'personas.id')
            ->join('tipos_programa', 'proyectos.tipo_programa_id', 'tipos_programa.id')
            ->leftJoin('personas AS remitidos', 'proyectos.remitido_id', 'remitidos.id')
            ->leftJoin('paises', 'proyectos.pais_id', 'paises.id')
            ->leftJoin('departamentos', 'proyectos.departamento_id', 'departamentos.id')
            ->leftJoin('ciudades', 'proyectos.ciudad_id', 'ciudades.id')
            ->leftJoin('comunas', 'proyectos.comuna_id', 'comunas.id')
            ->leftJoin('barrios', 'proyectos.barrio_id', 'barrios.id')
            ->leftJoin('bancos', 'proyectos.banco_id', 'bancos.id')
            ->leftJoin('orientadores', 'proyectos.orientador_id', 'orientadores.id')
            ->select(
                'proyectos.id',
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(personas.personasNombres), ''),
                        IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
                        IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
                        )
                    AS solicitante"
                ),
                'proyectos.proyectosEstadoProyecto',
                'proyectos.proyectosFechaSolicitud',
                'proyectos.proyectosTipoProyecto',
                'tipos_programa.tipProDescripcion As tipo_programa',
                'proyectos.proyectosRemitido',
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(remitidos.personasNombres), ''),
                        IFNULL(CONCAT(' ',remitidos.personasPrimerApellido),''),
                        IFNULL(CONCAT(' ',remitidos.personasSegundoApellido), '')
                        )
                    AS remitente"
                ),
                'paises.paisesDescripcion AS pais',
                'departamentos.departamentosDescripcion AS departamento',
                'ciudades.ciudadesDescripcion AS ciudad',
                'comunas.comunasDescripcion AS comuna',
                'barrios.barriosDescripcion AS barrio',
                'proyectos.proyectosZona',
                'proyectos.proyectosDireccion',
                'proyectos.proyectosVisitaDomiciliaria',
                'proyectos.proyectosFechaVisitaDom',
                'proyectos.proyectosPagoEstudioCre',
                'proyectos.proyectosReqLicenciaCon',
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
                'bancos.bancosDescripcion AS banco',
                'proyectos.proyectosTipoCuentaRecaudo',
                'proyectos.proyectosNumCuentaRecaudo',
                'proyectos.proyectosEstadoFormalizacion',
                'proyectos.proyectosFechaAutNotaria',
                'proyectos.proyectosFechaFirEscrituras',
                'proyectos.proyectosFechaIngresosReg',
                'proyectos.proyectosAutorizacionDes',
                'proyectos.proyectosFechaAutDes',
                'proyectos.proyectosFechaCancelacion',
                'orientadores.orientadoresNombre AS orientador',
                'proyectos.proyectosObservaciones',
                'proyectos.usuario_creacion_id',
                'proyectos.usuario_creacion_nombre',
                'proyectos.usuario_modificacion_id',
                'proyectos.usuario_modificacion_nombre',
                'proyectos.created_at AS fecha_creacion',
                'proyectos.updated_at AS fecha_modificacion',
            );

        if(isset($dto['solicitante'])){
            $query->where('proyectos.persona_id', $dto['solicitante']);
        }
        if(isset($dto['tipo'])){
            $query->where('proyectos.proyectosTipoProyecto', $dto['tipo']);
        }
        if(isset($dto['estado'])){
            $query->where('proyectos.proyectosEstadoProyecto', $dto['estado']);
        }
        if(isset($dto['fecha'])){
            $initialDate = $dto['fecha'].' 00:00:00';
            $finalDate = $dto['fecha'].' 23:59:59';
            $query->whereBetween('proyectos.proyectosFechaSolicitud', [$initialDate, $finalDate]);
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'solicitante'){
                    $query->orderBy('personas.personasNombres', $value);
                }
                if($attribute == 'proyectosEstadoProyecto'){
                    $query->orderBy('proyectos.proyectosEstadoProyecto', $value);
                }
                if($attribute == 'proyectosFechaSolicitud'){
                    $query->orderBy('proyectos.proyectosFechaSolicitud', $value);
                }
                if($attribute == 'proyectosTipoProyecto'){
                    $query->orderBy('proyectos.proyectosTipoProyecto', $value);
                }
                if($attribute == 'tipo_programa'){
                    $query->orderBy('tipos_programa.tipProDescripcion', $value);
                }
                if($attribute == 'proyectosRemitido'){
                    $query->orderBy('proyectos.proyectosRemitido', $value);
                }
                if($attribute == 'remitido'){
                    $query->orderBy('remitidos.personasNombres', $value);
                }
                if($attribute == 'pais'){
                    $query->orderBy('paises.paisesDescripcion', $value);
                }
                if($attribute == 'departamento'){
                    $query->orderBy('departamentos.departamentosDescripcion', $value);
                }
                if($attribute == 'ciudad'){
                    $query->orderBy('ciudades.ciudadesDescripcion', $value);
                }
                if($attribute == 'comuna_id'){
                    $query->orderBy('comunas.comunasDescripcion', $value);
                }
                if($attribute == 'barrio_id'){
                    $query->orderBy('barrios.barriosDescripcion', $value);
                }
                if($attribute == 'proyectosZona'){
                    $query->orderBy('proyectos.proyectosZona', $value);
                }
                if($attribute == 'proyectosDireccion'){
                    $query->orderBy('proyectos.proyectosDireccion', $value);
                }
                if($attribute == 'proyectosVisitaDomiciliaria'){
                    $query->orderBy('proyectos.proyectosVisitaDomiciliaria', $value);
                }
                if($attribute == 'proyectosFechaVisitaDom'){
                    $query->orderBy('proyectos.proyectosFechaVisitaDom', $value);
                }
                if($attribute == 'proyectosPagoEstudioCre'){
                    $query->orderBy('proyectos.proyectosPagoEstudioCre', $value);
                }
                if($attribute == 'proyectosReqLicenciaCon'){
                    $query->orderBy('proyectos.proyectosReqLicenciaCon', $value);
                }
                if($attribute == 'proyectosFechaInicioEstudio'){
                    $query->orderBy('proyectos.proyectosFechaInicioEstudio', $value);
                }
                if($attribute == 'proyectosFechaAproRec'){
                    $query->orderBy('proyectos.proyectosFechaAproRec', $value);
                }
                if($attribute == 'proyectosFechaEstInicioObr'){
                    $query->orderBy('proyectos.proyectosFechaEstInicioObr', $value);
                }
                if($attribute == 'proyectosValorProyecto'){
                    $query->orderBy('proyectos.proyectosValorProyecto', $value);
                }
                if($attribute == 'proyectosValorSolicitud'){
                    $query->orderBy('proyectos.proyectosValorSolicitud', $value);
                }
                if($attribute == 'proyectosValorRecursosSol'){
                    $query->orderBy('proyectos.proyectosValorRecursosSol', $value);
                }
                if($attribute == 'proyectosValorSubsidios'){
                    $query->orderBy('proyectos.proyectosValorSubsidios', $value);
                }
                if($attribute == 'proyectosValorDonaciones'){
                    $query->orderBy('proyectos.proyectosValorDonaciones', $value);
                }
                if($attribute == 'proyectosValorCuotaAprobada'){
                    $query->orderBy('proyectos.proyectosValorCuotaAprobada', $value);
                }
                if($attribute == 'proyectosValorCapPagoMen'){
                    $query->orderBy('proyectos.proyectosValorCapPagoMen', $value);
                }
                if($attribute == 'proyectosValorAprobado'){
                    $query->orderBy('proyectos.proyectosValorAprobado', $value);
                }
                if($attribute == 'proyectosValorSeguroVida'){
                    $query->orderBy('proyectos.proyectosValorSeguroVida', $value);
                }
                if($attribute == 'proyectosTasaInteresNMV'){
                    $query->orderBy('proyectos.proyectosTasaInteresNMV', $value);
                }
                if($attribute == 'proyectosTasaInteresEA'){
                    $query->orderBy('proyectos.proyectosTasaInteresEA', $value);
                }
                if($attribute == 'proyectosNumeroCuotas'){
                    $query->orderBy('proyectos.proyectosNumeroCuotas', $value);
                }
                if($attribute == 'banco'){
                    $query->orderBy('bancos.bancosDescripcion', $value);
                }
                if($attribute == 'proyectosTipoCuentaRecaudo'){
                    $query->orderBy('proyectos.proyectosTipoCuentaRecaudo', $value);
                }
                if($attribute == 'proyectosNumCuentaRecaudo'){
                    $query->orderBy('proyectos.proyectosNumCuentaRecaudo', $value);
                }
                if($attribute == 'proyectosEstadoFormalizacion'){
                    $query->orderBy('proyectos.proyectosEstadoFormalizacion', $value);
                }
                if($attribute == 'proyectosFechaAutNotaria'){
                    $query->orderBy('proyectos.proyectosFechaAutNotaria', $value);
                }
                if($attribute == 'proyectosFechaFirEscrituras'){
                    $query->orderBy('proyectos.proyectosFechaFirEscrituras', $value);
                }
                if($attribute == 'proyectosFechaIngresosReg'){
                    $query->orderBy('proyectos.proyectosFechaIngresosReg', $value);
                }
                if($attribute == 'proyectosAutorizacionDes'){
                    $query->orderBy('proyectos.proyectosAutorizacionDes', $value);
                }
                if($attribute == 'proyectosFechaAutDes'){
                    $query->orderBy('proyectos.proyectosFechaAutDes', $value);
                }
                if($attribute == 'proyectosFechaCancelacion'){
                    $query->orderBy('proyectos.proyectosFechaCancelacion', $value);
                }
                if($attribute == 'orientador'){
                    $query->orderBy('orientadores.orientadoresNombre', $value);
                }
                if($attribute == 'proyectosObservaciones'){
                    $query->orderBy('proyectos.proyectosObservaciones', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('proyectos.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('proyectos.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('proyectos.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('proyectos.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("proyectos.updated_at", "desc");
        }

        $proyectos = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($proyectos ?? [] as $proyecto){
            array_push($datos, $proyecto);
        }

        $cantidadProyectos = count($proyectos);
        $to = isset($proyectos) && $cantidadProyectos > 0 ? $proyectos->currentPage() * $proyectos->perPage() : null;
        $to = isset($to) && isset($proyectos) && $to > $proyectos->total() && $cantidadProyectos > 0 ? $proyectos->total() : $to;
        $from = isset($to) && isset($proyectos) && $cantidadProyectos > 0 ?
            ( $proyectos->perPage() > $to ? 1 : ($to - $cantidadProyectos) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($proyectos) && $cantidadProyectos > 0 ? +$proyectos->perPage() : 0,
            'pagina_actual' => isset($proyectos) && $cantidadProyectos > 0 ? $proyectos->currentPage() : 1,
            'ultima_pagina' => isset($proyectos) && $cantidadProyectos > 0 ? $proyectos->lastPage() : 0,
            'total' => isset($proyectos) && $cantidadProyectos > 0 ? $proyectos->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $proyecto = Proyecto::find($id);
        $solicitante = $proyecto->solicitante;
        // $tipoPrograma = $proyecto->tipoPrograma;
        $remitente = $proyecto->remitente;
        // $pais = $proyecto->pais;
        // $departamento = $proyecto->departamento;
        // $ciudad = $proyecto->ciudad;
        // $comuna = $proyecto->comuna;
        // $barrio = $proyecto->barrio;
        // $banco = $proyecto->banco;
        // $orientador = $proyecto->orientador;

        return [
            'id' => $proyecto->id,
            'persona_id' => $proyecto->persona_id,
            'proyectosEstadoProyecto' => $proyecto->proyectosEstadoProyecto,
            'proyectosFechaSolicitud' => $proyecto->proyectosFechaSolicitud,
            'proyectosTipoProyecto' => $proyecto->proyectosTipoProyecto,
            'tipo_programa_id' => $proyecto->tipo_programa_id,
            'proyectosRemitido' => $proyecto->proyectosRemitido,
            'remitido_id' => $proyecto->remitido_id,
            'pais_id' => $proyecto->pais_id,
            'departamento_id' => $proyecto->departamento_id,
            'ciudad_id' => $proyecto->ciudad_id,
            'comuna_id' => $proyecto->comuna_id,
            'barrio_id' => $proyecto->barrio_id,
            'proyectosZona' => $proyecto->proyectosZona,
            'proyectosDireccion' => $proyecto->proyectosDireccion,
            'proyectosVisitaDomiciliaria' => $proyecto->proyectosVisitaDomiciliaria,
            'proyectosFechaVisitaDom' => $proyecto->proyectosFechaVisitaDom,
            'proyectosPagoEstudioCre' => $proyecto->proyectosPagoEstudioCre,
            'proyectosReqLicenciaCon' => $proyecto->proyectosReqLicenciaCon,
            'proyectosFechaInicioEstudio' => $proyecto->proyectosFechaInicioEstudio,
            'proyectosFechaAproRec' => $proyecto->proyectosFechaAproRec,
            'proyectosFechaEstInicioObr' => $proyecto->proyectosFechaEstInicioObr,
            'proyectosValorProyecto' => $proyecto->proyectosValorProyecto,
            'proyectosValorSolicitud' => $proyecto->proyectosValorSolicitud,
            'proyectosValorRecursosSol' => $proyecto->proyectosValorRecursosSol,
            'proyectosValorSubsidios' => $proyecto->proyectosValorSubsidios,
            'proyectosValorDonaciones' => $proyecto->proyectosValorDonaciones,
            'proyectosValorCuotaAprobada' => $proyecto->proyectosValorCuotaAprobada,
            'proyectosValorCapPagoMen' => $proyecto->proyectosValorCapPagoMen,
            'proyectosValorAprobado' => $proyecto->proyectosValorAprobado,
            'proyectosValorSeguroVida' => $proyecto->proyectosValorSeguroVida,
            'proyectosTasaInteresNMV' => $proyecto->proyectosTasaInteresNMV,
            'proyectosTasaInteresEA' => $proyecto->proyectosTasaInteresEA,
            'proyectosNumeroCuotas' => $proyecto->proyectosNumeroCuotas,
            'banco_id' => $proyecto->banco_id,
            'proyectosTipoCuentaRecaudo' => $proyecto->proyectosTipoCuentaRecaudo,
            'proyectosNumCuentaRecaudo' => $proyecto->proyectosNumCuentaRecaudo,
            'proyectosEstadoFormalizacion' => $proyecto->proyectosEstadoFormalizacion,
            'proyectosFechaAutNotaria' => $proyecto->proyectosFechaAutNotaria,
            'proyectosFechaFirEscrituras' => $proyecto->proyectosFechaFirEscrituras,
            'proyectosFechaIngresosReg' => $proyecto->proyectosFechaIngresosReg,
            'proyectosAutorizacionDes' => $proyecto->proyectosAutorizacionDes,
            'proyectosFechaAutDes' => $proyecto->proyectosFechaAutDes,
            'proyectosFechaCancelacion' => $proyecto->proyectosFechaCancelacion,
            'orientador_id' => $proyecto->orientador_id,
            'proyectosObservaciones' => $proyecto->proyectosObservaciones,
            'usuario_creacion_id' => $proyecto->usuario_creacion_id,
            'usuario_creacion_nombre' => $proyecto->usuario_creacion_nombre,
            'usuario_modificacion_id' => $proyecto->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $proyecto->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($proyecto->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($proyecto->updated_at))->format("Y-m-d H:i:s"),
            'solicitante' => isset($solicitante) ? [
                'id' => $solicitante->id,
                'nombre' => $solicitante->personasNombres.' '.$solicitante->personasPrimerApellido.' '.$solicitante->personasSegundoApellido
            ] : null,
            'remitente' => isset($remitente) ? [
                'id' => $remitente->id,
                'nombre' => $remitente->personasNombres.' '.$remitente->personasPrimerApellido.' '.$remitente->personasSegundoApellido
            ] : null,
            // 'orientador' => isset($orientador) ? [
            //     'id' => $orientador->id,
            //     'nombre' => $orientador->orientadoresNombre
            // ] : null,
            // 'tipoPrograma' => isset($tipoPrograma) ? [
            //     'id' => $tipoPrograma->id,
            //     'nombre' => $tipoPrograma->tipProDescripcion
            // ] : null,
            // 'banco' => isset($banco) ? [
            //     'id' => $banco->id,
            //     'nombre' => $banco->bancosDescripcion
            // ] : null,
            // 'departamento' => isset($departamento) ? [
            //     'id' => $departamento->id,
            //     'nombre' => $departamento->departamentosDescripcion
            // ] : null,
            // 'ciudad' => isset($ciudad) ? [
            //     'id' => $ciudad->id,
            //     'nombre' => $ciudad->ciudadesDescripcion
            // ] : null,
            // 'comuna' => isset($comuna) ? [
            //     'id' => $comuna->id,
            //     'nombre' => $comuna->comunasDescripcion
            // ] : null,
            // 'barrio' => isset($barrio) ? [
            //     'id' => $barrio->id,
            //     'nombre' => $barrio->barriosDescripcion
            // ] : null,
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

        // Consultar aplicación
        $proyecto = isset($dto['id']) ? Proyecto::find($dto['id']) : new Proyecto();

        // Guardar objeto original para auditoria
        $proyectoOriginal = $proyecto->toJson();

        $proyecto->fill($dto);
        $guardado = $proyecto->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar el proyecto.", $proyecto);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $proyecto->id,
            'nombre_recurso' => Proyecto::class,
            'descripcion_recurso' => $proyecto->solicitante->personasNombres,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $proyectoOriginal : $proyecto->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $proyecto->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return Proyecto::cargar($proyecto->id);
    }

    public static function eliminar($id)
    {
        $proyecto = Proyecto::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $proyecto->id,
            'nombre_recurso' => Proyecto::class,
            'descripcion_recurso' => $proyecto->solicitante->personasNombres,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $proyecto->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $proyecto->delete();
    }

    use HasFactory;
}
