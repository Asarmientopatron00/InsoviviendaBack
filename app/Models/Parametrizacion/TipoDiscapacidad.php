<?php

namespace App\Models\Parametrizacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Seguridad\AuditoriaTabla;


class TipoDiscapacidad extends Model
{
    protected $table = 'tipos_discapacidad'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipDisDescripcion',
        'tipDisEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_discapacidad')
            ->select(
                'id',
                'tipDisDescripcion AS nombre',
                'tipDisEstado AS estado',
            );
        $query->orderBy('tipDisDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_discapacidad')
            ->select(
                'id',
                'tipDisDescripcion As nombre',
                'tipDisEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipDisDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_discapacidad.tipDisDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_discapacidad.tipDisEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_discapacidad.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_discapacidad.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_discapacidad.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_discapacidad.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_discapacidad.updated_at", "desc");
        }

        $tiposDiscapacidad = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tiposDiscapacidad ?? [] as $tipoDiscapacidad){
            array_push($datos, $tipoDiscapacidad);
        }

        $cantidadTiposDiscapacidad = count($tiposDiscapacidad);
        $to = isset($tiposDiscapacidad) && $cantidadTiposDiscapacidad > 0 ? $tiposDiscapacidad->currentPage() * $tiposDiscapacidad->perPage() : null;
        $to = isset($to) && isset($tiposDiscapacidad) && $to > $tiposDiscapacidad->total() && $cantidadTiposDiscapacidad > 0 ? $tiposDiscapacidad->total() : $to;
        $from = isset($to) && isset($tiposDiscapacidad) && $cantidadTiposDiscapacidad > 0 ?
            ( $tiposDiscapacidad->perPage() > $to ? 1 : ($to - $cantidadTiposDiscapacidad) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tiposDiscapacidad) && $cantidadTiposDiscapacidad > 0 ? +$tiposDiscapacidad->perPage() : 0,
            'pagina_actual' => isset($tiposDiscapacidad) && $cantidadTiposDiscapacidad > 0 ? $tiposDiscapacidad->currentPage() : 1,
            'ultima_pagina' => isset($tiposDiscapacidad) && $cantidadTiposDiscapacidad > 0 ? $tiposDiscapacidad->lastPage() : 0,
            'total' => isset($tiposDiscapacidad) && $cantidadTiposDiscapacidad > 0 ? $tiposDiscapacidad->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoDiscapacidad = TipoDiscapacidad::find($id);
        return [
            'id' => $tipoDiscapacidad->id,
            'nombre' => $tipoDiscapacidad->tipDisDescripcion,
            'estado' => $tipoDiscapacidad->tipDisEstado,
            'usuario_creacion_id' => $tipoDiscapacidad->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoDiscapacidad->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoDiscapacidad->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoDiscapacidad->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoDiscapacidad->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoDiscapacidad->updated_at))->format("Y-m-d H:i:s")
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
        $tipoDiscapacidad = isset($dto['id']) ? TipoDiscapacidad::find($dto['id']) : new TipoDiscapacidad();

        // Guardar objeto original para auditoria
        $tipoDiscapacidadOriginal = $tipoDiscapacidad->toJson();

        $tipoDiscapacidad->fill($dto);
        $guardado = $tipoDiscapacidad->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoDiscapacidad);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoDiscapacidad->id,
            'nombre_recurso' => TipoDiscapacidad::class,
            'descripcion_recurso' => $tipoDiscapacidad->tipDisDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $tipoDiscapacidadOriginal : $tipoDiscapacidad->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoDiscapacidad->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoDiscapacidad::cargar($tipoDiscapacidad->id);
    }

    public static function eliminar($id)
    {
        $tipoDiscapacidad = TipoDiscapacidad::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoDiscapacidad->id,
            'nombre_recurso' => TipoDiscapacidad::class,
            'descripcion_recurso' => $tipoDiscapacidad->tipDisDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoDiscapacidad->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoDiscapacidad->delete();
    }

    use HasFactory;
}
