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

class TipoParentesco extends Model
{
    protected $table = 'tipos_parentesco'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipParDescripcion',
        'tipParEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_parentesco')
            ->select(
                'id',
                'tipParDescripcion AS nombre',
                'tipParEstado AS estado',
            );
        $query->orderBy('tipParDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_parentesco')
            ->select(
                'id',
                'tipParDescripcion As nombre',
                'tipParEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipParDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_parentesco.tipParDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_parentesco.tipParEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_parentesco.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_parentesco.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_parentesco.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_parentesco.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_parentesco.updated_at", "desc");
        }

        $tiposParentesco = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tiposParentesco ?? [] as $tipoParentesco){
            array_push($datos, $tipoParentesco);
        }

        $cantidadTiposParentesco = count($tiposParentesco);
        $to = isset($tiposParentesco) && $cantidadTiposParentesco > 0 ? $tiposParentesco->currentPage() * $tiposParentesco->perPage() : null;
        $to = isset($to) && isset($tiposParentesco) && $to > $tiposParentesco->total() && $cantidadTiposParentesco > 0 ? $tiposParentesco->total() : $to;
        $from = isset($to) && isset($tiposParentesco) && $cantidadTiposParentesco > 0 ?
            ( $tiposParentesco->perPage() > $to ? 1 : ($to - $cantidadTiposParentesco) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tiposParentesco) && $cantidadTiposParentesco > 0 ? +$tiposParentesco->perPage() : 0,
            'pagina_actual' => isset($tiposParentesco) && $cantidadTiposParentesco > 0 ? $tiposParentesco->currentPage() : 1,
            'ultima_pagina' => isset($tiposParentesco) && $cantidadTiposParentesco > 0 ? $tiposParentesco->lastPage() : 0,
            'total' => isset($tiposParentesco) && $cantidadTiposParentesco > 0 ? $tiposParentesco->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoParentesco = TipoParentesco::find($id);
        return [
            'id' => $tipoParentesco->id,
            'nombre' => $tipoParentesco->tipParDescripcion,
            'estado' => $tipoParentesco->tipParEstado,
            'usuario_creacion_id' => $tipoParentesco->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoParentesco->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoParentesco->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoParentesco->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoParentesco->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoParentesco->updated_at))->format("Y-m-d H:i:s")
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
        $tipoParentesco = isset($dto['id']) ? TipoParentesco::find($dto['id']) : new TipoParentesco();

        // Guardar objeto original para auditoria
        $tipoParentescoOriginal = $tipoParentesco->toJson();

        $tipoParentesco->fill($dto);
        $guardado = $tipoParentesco->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoParentesco);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoParentesco->id,
            'nombre_recurso' => TipoParentesco::class,
            'descripcion_recurso' => $tipoParentesco->tipParDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $tipoParentescoOriginal : $tipoParentesco->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoParentesco->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoParentesco::cargar($tipoParentesco->id);
    }

    public static function eliminar($id)
    {
        $tipoParentesco = TipoParentesco::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoParentesco->id,
            'nombre_recurso' => TipoParentesco::class,
            'descripcion_recurso' => $tipoParentesco->tipParDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoParentesco->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoParentesco->delete();
    }

    use HasFactory;
}
