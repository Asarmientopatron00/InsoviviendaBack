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

class TipoPrograma extends Model
{
    protected $table = 'tipos_programa'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipProDescripcion',
        'tipProEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_programa')
            ->select(
                'id',
                'tipProDescripcion AS nombre',
                'tipProEstado AS estado',
            );
        $query->orderBy('tipProDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_programa')
            ->select(
                'id',
                'tipProDescripcion As nombre',
                'tipProEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipProDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_programa.tipProDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_programa.tipProEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_programa.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_programa.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_programa.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_programa.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_programa.updated_at", "desc");
        }

        $tiposPrograma = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tiposPrograma ?? [] as $tipoPrograma){
            array_push($datos, $tipoPrograma);
        }

        $cantidadTiposPrograma = count($tiposPrograma);
        $to = isset($tiposPrograma) && $cantidadTiposPrograma > 0 ? $tiposPrograma->currentPage() * $tiposPrograma->perPage() : null;
        $to = isset($to) && isset($tiposPrograma) && $to > $tiposPrograma->total() && $cantidadTiposPrograma > 0 ? $tiposPrograma->total() : $to;
        $from = isset($to) && isset($tiposPrograma) && $cantidadTiposPrograma > 0 ?
            ( $tiposPrograma->perPage() > $to ? 1 : ($to - $cantidadTiposPrograma) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tiposPrograma) && $cantidadTiposPrograma > 0 ? +$tiposPrograma->perPage() : 0,
            'pagina_actual' => isset($tiposPrograma) && $cantidadTiposPrograma > 0 ? $tiposPrograma->currentPage() : 1,
            'ultima_pagina' => isset($tiposPrograma) && $cantidadTiposPrograma > 0 ? $tiposPrograma->lastPage() : 0,
            'total' => isset($tiposPrograma) && $cantidadTiposPrograma > 0 ? $tiposPrograma->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoPrograma = TipoPrograma::find($id);
        return [
            'id' => $tipoPrograma->id,
            'nombre' => $tipoPrograma->tipProDescripcion,
            'estado' => $tipoPrograma->tipProEstado,
            'usuario_creacion_id' => $tipoPrograma->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoPrograma->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoPrograma->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoPrograma->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoPrograma->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoPrograma->updated_at))->format("Y-m-d H:i:s")
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
        $tipoPrograma = isset($dto['id']) ? TipoPrograma::find($dto['id']) : new TipoPrograma();

        // Guardar objeto original para auditoria
        $tipoProgramaOriginal = $tipoPrograma->toJson();

        $tipoPrograma->fill($dto);
        $guardado = $tipoPrograma->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoPrograma);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoPrograma->id,
            'nombre_recurso' => TipoPrograma::class,
            'descripcion_recurso' => $tipoPrograma->tipProDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $tipoProgramaOriginal : $tipoPrograma->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoPrograma->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoPrograma::cargar($tipoPrograma->id);
    }

    public static function eliminar($id)
    {
        $tipoPrograma = TipoPrograma::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoPrograma->id,
            'nombre_recurso' => TipoPrograma::class,
            'descripcion_recurso' => $tipoPrograma->tipProDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoPrograma->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoPrograma->delete();
    }

    use HasFactory;
}
