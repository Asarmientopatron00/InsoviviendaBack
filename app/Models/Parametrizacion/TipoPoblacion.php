<?php

namespace App\Models\Parametrizacion;

use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoPoblacion extends Model
{
    protected $table = 'tipos_poblacion'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipPobDescripcion',
        'tipPobEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_poblacion')
            ->select(
                'id',
                'tipPobDescripcion AS nombre',
                'tipPobEstado AS estado',
            );
        $query->orderBy('tipPobDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_poblacion')
            ->select(
                'id',
                'tipPobDescripcion As nombre',
                'tipPobEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipPobDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_poblacion.tipPobDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_poblacion.tipPobEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_poblacion.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_poblacion.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_poblacion.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_poblacion.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_poblacion.updated_at", "desc");
        }

        $tiposPoblacion = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tiposPoblacion ?? [] as $tipoPoblacion){
            array_push($datos, $tipoPoblacion);
        }

        $cantidadTiposPoblacion = count($tiposPoblacion);
        $to = isset($tiposPoblacion) && $cantidadTiposPoblacion > 0 ? $tiposPoblacion->currentPage() * $tiposPoblacion->perPage() : null;
        $to = isset($to) && isset($tiposPoblacion) && $to > $tiposPoblacion->total() && $cantidadTiposPoblacion > 0 ? $tiposPoblacion->total() : $to;
        $from = isset($to) && isset($tiposPoblacion) && $cantidadTiposPoblacion > 0 ?
            ( $tiposPoblacion->perPage() > $to ? 1 : ($to - $cantidadTiposPoblacion) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tiposPoblacion) && $cantidadTiposPoblacion > 0 ? +$tiposPoblacion->perPage() : 0,
            'pagina_actual' => isset($tiposPoblacion) && $cantidadTiposPoblacion > 0 ? $tiposPoblacion->currentPage() : 1,
            'ultima_pagina' => isset($tiposPoblacion) && $cantidadTiposPoblacion > 0 ? $tiposPoblacion->lastPage() : 0,
            'total' => isset($tiposPoblacion) && $cantidadTiposPoblacion > 0 ? $tiposPoblacion->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoPoblacion = TipoPoblacion::find($id);
        return [
            'id' => $tipoPoblacion->id,
            'nombre' => $tipoPoblacion->tipPobDescripcion,
            'estado' => $tipoPoblacion->tipPobEstado,
            'usuario_creacion_id' => $tipoPoblacion->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoPoblacion->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoPoblacion->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoPoblacion->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoPoblacion->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoPoblacion->updated_at))->format("Y-m-d H:i:s")
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
        $tipoPoblacion = isset($dto['id']) ? TipoPoblacion::find($dto['id']) : new TipoPoblacion();

        // Guardar objeto original para auditoria
        $tipoPoblacionOriginal = $tipoPoblacion->toJson();

        $tipoPoblacion->fill($dto);
        $guardado = $tipoPoblacion->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoPoblacion);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoPoblacion->id,
            'nombre_recurso' => TipoPoblacion::class,
            'descripcion_recurso' => $tipoPoblacion->tipPobDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $tipoPoblacionOriginal : $tipoPoblacion->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoPoblacion->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoPoblacion::cargar($tipoPoblacion->id);
    }

    public static function eliminar($id)
    {
        $tipoPoblacion = TipoPoblacion::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoPoblacion->id,
            'nombre_recurso' => TipoPoblacion::class,
            'descripcion_recurso' => $tipoPoblacion->tipPobDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoPoblacion->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoPoblacion->delete();
    }    
    use HasFactory;
}
