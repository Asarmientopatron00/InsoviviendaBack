<?php

namespace App\Models\PersonasEntidades;

use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use App\Models\PersonasEntidades\Persona;
use App\Models\Parametrizacion\TipoFamilia;
use App\Models\Parametrizacion\CondicionFamilia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Familia extends Model
{
    protected $table = 'familias'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'identificacion_persona',
        'tipo_familia_id',
        'condicion_familia_id',
        'familiasFechaVisitaDomici',
        'familiasAportesFormales',
        'familiasAportesInformales',
        'familiasAportesArriendo',
        'familiasAportesSubsidios',
        'familiasAportesPaternidad',
        'familiasAportesTerceros',
        'familiasAportesOtros',
        'familiasEgresosDeudas',
        'familiasEgresosEducacion',
        'familiasEgresosSalud',
        'familiasEgresosTransporte',
        'familiasEgresosSerPublicos',
        'familiasEgresosAlimentacion',
        'familiasEgresosVivienda',
        'familiasEstado',
        'familiasObservaciones',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function persona(){
        return $this->belongsTo(Persona::class, 'identificacion_persona', 'personasIdentificacion');
    }

    public function tipoFamilia(){
        return $this->belongsTo(TipoFamilia::class, 'tipo_familia_id');
    }

    public function condicionFamilia(){
        return $this->belongsTo(CondicionFamilia::class, 'condicion_familia_id');
    }

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('familias')
            ->join('personas', 'personas.personasIdentificacion', 'familias.identificacion_persona')
            ->select(
                'familias.id',
                'familias.identificacion_persona',
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(personas.personasNombres), ''),
                        IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
                        IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
                        )
                    AS nombre"
                ),
                'familias.familiasEstado AS estado',
            );
        $query->orderBy('personas.personasNombres', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('familias')
            ->join('personas', 'personas.personasIdentificacion', 'familias.identificacion_persona')
            ->join('tipos_familia', 'tipos_familia.id', 'familias.tipo_familia_id')
            ->leftJoin('condiciones_familia', 'condiciones_familia.id', 'familias.condicion_familia_id')
            ->select(
                'familias.id',
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
                'familias.familiasObservaciones',
                'familias.familiasEstado AS estado',
                'familias.usuario_creacion_id',
                'familias.usuario_creacion_nombre',
                'familias.usuario_modificacion_id',
                'familias.usuario_modificacion_nombre',
                'familias.created_at AS fecha_creacion',
                'familias.updated_at AS fecha_modificacion',
            );

        if(isset($dto['identificacion'])){
            $query->where('familias.identificacion_persona', 'like', '%' . $dto['identificacion'] . '%');
        }

        if(isset($dto['estado'])){
            $query->where('familias.familiasEstado', $dto['estado']);
        }

        if(isset($dto['tipoFamilia'])){
            $query->where('familias.tipo_familia_id', $dto['tipoFamilia']);
        }

        if(isset($dto['condicionFamilia'])){
            $query->where('familias.condicion_familia_id', $dto['condicionFamilia']);
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'identificacion_persona'){
                    $query->orderBy('familias.identificacion_persona', $value);
                }
                if($attribute == 'nombre'){
                    $query->orderBy('personas.nombre', $value);
                }
                if($attribute == 'tipFamDescripcion'){
                    $query->orderBy('tipos_familia.tipFamDescripcion', $value);
                }
                if($attribute == 'conFamDescripcion'){
                    $query->orderBy('condiciones_familia.conFamDescripcion', $value);
                }
                if($attribute == 'familiasFechaVisitaDomici'){
                    $query->orderBy('familias.familiasFechaVisitaDomici', $value);
                }
                if($attribute == 'familiasAportesFormales'){
                    $query->orderBy('familias.familiasAportesFormales', $value);
                }
                if($attribute == 'familiasAportesInformales'){
                    $query->orderBy('familias.familiasAportesInformales', $value);
                }
                if($attribute == 'familiasAportesArriendo'){
                    $query->orderBy('familias.familiasAportesArriendo', $value);
                }
                if($attribute == 'familiasAportesSubsidios'){
                    $query->orderBy('familias.familiasAportesSubsidios', $value);
                }
                if($attribute == 'familiasAportesPaternidad'){
                    $query->orderBy('familias.familiasAportesPaternidad', $value);
                }
                if($attribute == 'familiasAportesTerceros'){
                    $query->orderBy('familias.familiasAportesTerceros', $value);
                }
                if($attribute == 'familiasAportesOtros'){
                    $query->orderBy('familias.familiasAportesOtros', $value);
                }
                if($attribute == 'familiasEgresosDeudas'){
                    $query->orderBy('familias.familiasEgresosDeudas', $value);
                }
                if($attribute == 'familiasEgresosEducacion'){
                    $query->orderBy('familias.familiasEgresosEducacion', $value);
                }
                if($attribute == 'familiasEgresosSalud'){
                    $query->orderBy('familias.familiasEgresosSalud', $value);
                }
                if($attribute == 'familiasEgresosTransporte'){
                    $query->orderBy('familias.familiasEgresosTransporte', $value);
                }
                if($attribute == 'familiasEgresosSerPublicos'){
                    $query->orderBy('familias.familiasEgresosSerPublicos', $value);
                }
                if($attribute == 'familiasEgresosAlimentacion'){
                    $query->orderBy('familias.familiasEgresosAlimentacion', $value);
                }
                if($attribute == 'familiasEgresosVivienda'){
                    $query->orderBy('familias.familiasEgresosVivienda', $value);
                }
                if($attribute == 'familiasObservaciones'){
                    $query->orderBy('familias.familiasObservaciones', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('familias.familiasEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('familias.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('familias.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('familias.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('familias.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("familias.updated_at", "desc");
        }

        $familias = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($familias ?? [] as $familia){
            array_push($datos, $familia);
        }

        $cantidadFamilias = count($familias);
        $to = isset($familias) && $cantidadFamilias > 0 ? $familias->currentPage() * $familias->perPage() : null;
        $to = isset($to) && isset($familias) && $to > $familias->total() && $cantidadFamilias > 0 ? $familias->total() : $to;
        $from = isset($to) && isset($familias) && $cantidadFamilias > 0 ?
            ( $familias->perPage() > $to ? 1 : ($to - $cantidadFamilias) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($familias) && $cantidadFamilias > 0 ? +$familias->perPage() : 0,
            'pagina_actual' => isset($familias) && $cantidadFamilias > 0 ? $familias->currentPage() : 1,
            'ultima_pagina' => isset($familias) && $cantidadFamilias > 0 ? $familias->lastPage() : 0,
            'total' => isset($familias) && $cantidadFamilias > 0 ? $familias->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $familia = Familia::find($id);
        $persona = $familia->persona;
        // $tipoFamilia = $familia->tipoFamilia;
        // $condicionFamilia = $familia->condicionFamilia;

        return [
            'id' => $familia->id,
            'identificacion_persona' => $familia->identificacion_persona,
            'familiasFechaVisitaDomici' => $familia->familiasFechaVisitaDomici,
            'familiasAportesFormales' => $familia->familiasAportesFormales,
            'familiasAportesInformales' => $familia->familiasAportesInformales,
            'familiasAportesArriendo' => $familia->familiasAportesArriendo,
            'familiasAportesSubsidios' => $familia->familiasAportesSubsidios,
            'familiasAportesPaternidad' => $familia->familiasAportesPaternidad,
            'familiasAportesTerceros' => $familia->familiasAportesTerceros,
            'familiasAportesOtros' => $familia->familiasAportesOtros,
            'familiasEgresosDeudas' => $familia->familiasEgresosDeudas,
            'familiasEgresosEducacion' => $familia->familiasEgresosEducacion,
            'familiasEgresosSalud' => $familia->familiasEgresosSalud,
            'familiasEgresosTransporte' => $familia->familiasEgresosTransporte,
            'familiasEgresosSerPublicos' => $familia->familiasEgresosSerPublicos,
            'familiasEgresosAlimentacion' => $familia->familiasEgresosAlimentacion,
            'familiasEgresosVivienda' => $familia->familiasEgresosVivienda,
            'familiasEstado' => $familia->familiasEstado,
            'familiasObservaciones' => $familia->familiasObservaciones,
            'estado' => $familia->familiasEstado,
            'usuario_creacion_id' => $familia->usuario_creacion_id,
            'usuario_creacion_nombre' => $familia->usuario_creacion_nombre,
            'usuario_modificacion_id' => $familia->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $familia->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($familia->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($familia->updated_at))->format("Y-m-d H:i:s"),
            'persona' => isset($persona) ? [
                'id' => $persona->id,
                'nombre' => $persona->personasNombres.' '.$persona->personasPrimerApellido.' '.$persona->personasSegundoApellido
            ] : null,
            // 'tipoFamilia' => isset($tipoFamilia) ? [
            //     'id' => $tipoFamilia->id,
            //     'nombre' => $tipoFamilia->tipFamDescripcion
            // ] : null,
            // 'condicionFamilia' => isset($condicionFamilia) ? [
            //     'id' => $condicionFamilia->id,
            //     'nombre' => $condicionFamilia->conFamDescripcion
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
        $familia = isset($dto['id']) ? Familia::find($dto['id']) : new Familia();

        // Guardar objeto original para auditoria
        $familiaOriginal = $familia->toJson();

        $familia->fill($dto);
        $guardado = $familia->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la familia.", $familia);
        }

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $familia->id,
            'nombre_recurso' => Familia::class,
            'descripcion_recurso' => $familia->identificacion_persona,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $familiaOriginal : $familia->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $familia->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return Familia::cargar($familia->id);
    }

    public static function eliminar($id)
    {
        $familia = Familia::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $familia->id,
            'nombre_recurso' => Familia::class,
            'descripcion_recurso' => $familia->identificacion_persona,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $familia->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $familia->delete();
    }

    use HasFactory;
}
