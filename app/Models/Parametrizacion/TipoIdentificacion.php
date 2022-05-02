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

class TipoIdentificacion extends Model
{
    protected $table = 'tipos_identificacion'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipIdeDescripcion',
        'tipIdeEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_identificacion')
            ->select(
                'id',
                'tipIdeDescripcion AS nombre',
                'tipIdeEstado AS estado',
            );
        $query->orderBy('tipIdeDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_identificacion')
            ->select(
                'id',
                'tipIdeDescripcion As nombre',
                'tipIdeEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipIdeDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_identificacion.tipIdeDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_identificacion.tipIdeEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_identificacion.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_identificacion.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_identificacion.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_identificacion.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_identificacion.updated_at", "desc");
        }

        $tiposIdentificacion = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tiposIdentificacion ?? [] as $tipoIdentificacion){
            array_push($datos, $tipoIdentificacion);
        }

        $cantidadTiposIdentificacion = count($tiposIdentificacion);
        $to = isset($tiposIdentificacion) && $cantidadTiposIdentificacion > 0 ? $tiposIdentificacion->currentPage() * $tiposIdentificacion->perPage() : null;
        $to = isset($to) && isset($tiposIdentificacion) && $to > $tiposIdentificacion->total() && $cantidadTiposIdentificacion > 0 ? $tiposIdentificacion->total() : $to;
        $from = isset($to) && isset($tiposIdentificacion) && $cantidadTiposIdentificacion > 0 ?
            ( $tiposIdentificacion->perPage() > $to ? 1 : ($to - $cantidadTiposIdentificacion) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tiposIdentificacion) && $cantidadTiposIdentificacion > 0 ? +$tiposIdentificacion->perPage() : 0,
            'pagina_actual' => isset($tiposIdentificacion) && $cantidadTiposIdentificacion > 0 ? $tiposIdentificacion->currentPage() : 1,
            'ultima_pagina' => isset($tiposIdentificacion) && $cantidadTiposIdentificacion > 0 ? $tiposIdentificacion->lastPage() : 0,
            'total' => isset($tiposIdentificacion) && $cantidadTiposIdentificacion > 0 ? $tiposIdentificacion->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoIdentificacion = TipoIdentificacion::find($id);
        return [
            'id' => $tipoIdentificacion->id,
            'nombre' => $tipoIdentificacion->tipIdeDescripcion,
            'estado' => $tipoIdentificacion->tipIdeEstado,
            'usuario_creacion_id' => $tipoIdentificacion->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoIdentificacion->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoIdentificacion->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoIdentificacion->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoIdentificacion->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoIdentificacion->updated_at))->format("Y-m-d H:i:s")
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
        $tipoIdentificacion = isset($dto['id']) ? TipoIdentificacion::find($dto['id']) : new TipoIdentificacion();

        // Guardar objeto original para auditoria
        $tipoIdentificacionOriginal = $tipoIdentificacion->toJson();

        $tipoIdentificacion->fill($dto);
        $guardado = $tipoIdentificacion->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoIdentificacion);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoIdentificacion->id,
            'nombre_recurso' => TipoIdentificacion::class,
            'descripcion_recurso' => $tipoIdentificacion->tipIdeDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $tipoIdentificacionOriginal : $tipoIdentificacion->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoIdentificacion->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoIdentificacion::cargar($tipoIdentificacion->id);
    }

    public static function eliminar($id)
    {
        $tipoIdentificacion = TipoIdentificacion::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoIdentificacion->id,
            'nombre_recurso' => TipoIdentificacion::class,
            'descripcion_recurso' => $tipoIdentificacion->tipIdeDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoIdentificacion->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoIdentificacion->delete();
    }

    use HasFactory;
}
