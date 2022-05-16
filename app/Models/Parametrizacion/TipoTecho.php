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

class TipoTecho extends Model
{
    protected $table = 'tipos_techo'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipTecDescripcion',
        'tipTecEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_techo')
            ->select(
                'id',
                'tipTecDescripcion AS nombre',
                'tipTecEstado AS estado',
            );
        $query->orderBy('tipTecDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_techo')
            ->select(
                'id',
                'tipTecDescripcion As nombre',
                'tipTecEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipTecDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_techo.tipTecDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_techo.tipTecEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_techo.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_techo.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_techo.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_techo.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_techo.updated_at", "desc");
        }

        $tiposTecho = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tiposTecho ?? [] as $tipoTecho){
            array_push($datos, $tipoTecho);
        }

        $cantidadTiposTecho = count($tiposTecho);
        $to = isset($tiposTecho) && $cantidadTiposTecho > 0 ? $tiposTecho->currentPage() * $tiposTecho->perPage() : null;
        $to = isset($to) && isset($tiposTecho) && $to > $tiposTecho->total() && $cantidadTiposTecho > 0 ? $tiposTecho->total() : $to;
        $from = isset($to) && isset($tiposTecho) && $cantidadTiposTecho > 0 ?
            ( $tiposTecho->perPage() > $to ? 1 : ($to - $cantidadTiposTecho) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tiposTecho) && $cantidadTiposTecho > 0 ? +$tiposTecho->perPage() : 0,
            'pagina_actual' => isset($tiposTecho) && $cantidadTiposTecho > 0 ? $tiposTecho->currentPage() : 1,
            'ultima_pagina' => isset($tiposTecho) && $cantidadTiposTecho > 0 ? $tiposTecho->lastPage() : 0,
            'total' => isset($tiposTecho) && $cantidadTiposTecho > 0 ? $tiposTecho->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoTecho = TipoTecho::find($id);
        return [
            'id' => $tipoTecho->id,
            'nombre' => $tipoTecho->tipTecDescripcion,
            'estado' => $tipoTecho->tipTecEstado,
            'usuario_creacion_id' => $tipoTecho->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoTecho->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoTecho->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoTecho->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoTecho->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoTecho->updated_at))->format("Y-m-d H:i:s")
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
        $tipoTecho = isset($dto['id']) ? TipoTecho::find($dto['id']) : new TipoTecho();

        // Guardar objeto original para auditoria
        $tipoTechoOriginal = $tipoTecho->toJson();

        $tipoTecho->fill($dto);
        $guardado = $tipoTecho->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoTecho);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoTecho->id,
            'nombre_recurso' => TipoTecho::class,
            'descripcion_recurso' => $tipoTecho->tipTecDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $tipoTechoOriginal : $tipoTecho->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoTecho->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoTecho::cargar($tipoTecho->id);
    }

    public static function eliminar($id)
    {
        $tipoTecho = TipoTecho::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoTecho->id,
            'nombre_recurso' => TipoTecho::class,
            'descripcion_recurso' => $tipoTecho->tipTecDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoTecho->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoTecho->delete();
    }    
    use HasFactory;
}
