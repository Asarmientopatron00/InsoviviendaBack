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

class TipoAsesoria extends Model
{
    protected $table = 'tipos_orientacion'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipOriDescripcion',
        'tipOriEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_orientacion')
            ->select(
                'id',
                'tipOriDescripcion AS nombre',
                'tipOriEstado AS estado',
            );
        $query->orderBy('tipOriDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_orientacion')
            ->select(
                'id',
                'tipOriDescripcion As nombre',
                'tipOriEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipOriDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_orientacion.tipOriDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_orientacion.tipOriEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_orientacion.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_orientacion.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_orientacion.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_orientacion.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_orientacion.updated_at", "desc");
        }

        $tiposAsesorias = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tiposAsesorias ?? [] as $tipoAsesoria){
            array_push($datos, $tipoAsesoria);
        }

        $cantidadTiposAsesorias = count($tiposAsesorias);
        $to = isset($tiposAsesorias) && $cantidadTiposAsesorias > 0 ? $tiposAsesorias->currentPage() * $tiposAsesorias->perPage() : null;
        $to = isset($to) && isset($tiposAsesorias) && $to > $tiposAsesorias->total() && $cantidadTiposAsesorias > 0 ? $tiposAsesorias->total() : $to;
        $from = isset($to) && isset($tiposAsesorias) && $cantidadTiposAsesorias > 0 ?
            ( $tiposAsesorias->perPage() > $to ? 1 : ($to - $cantidadTiposAsesorias) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tiposAsesorias) && $cantidadTiposAsesorias > 0 ? +$tiposAsesorias->perPage() : 0,
            'pagina_actual' => isset($tiposAsesorias) && $cantidadTiposAsesorias > 0 ? $tiposAsesorias->currentPage() : 1,
            'ultima_pagina' => isset($tiposAsesorias) && $cantidadTiposAsesorias > 0 ? $tiposAsesorias->lastPage() : 0,
            'total' => isset($tiposAsesorias) && $cantidadTiposAsesorias > 0 ? $tiposAsesorias->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoAsesoria = TipoAsesoria::find($id);
        return [
            'id' => $tipoAsesoria->id,
            'nombre' => $tipoAsesoria->tipOriDescripcion,
            'estado' => $tipoAsesoria->tipOriEstado,
            'usuario_creacion_id' => $tipoAsesoria->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoAsesoria->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoAsesoria->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoAsesoria->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoAsesoria->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoAsesoria->updated_at))->format("Y-m-d H:i:s")
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
        $tipoAsesoria = isset($dto['id']) ? TipoAsesoria::find($dto['id']) : new TipoAsesoria();

        // Guardar objeto original para auditoria
        $tipoAsesoriaOriginal = $tipoAsesoria->toJson();

        $tipoAsesoria->fill($dto);
        $guardado = $tipoAsesoria->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoAsesoria);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoAsesoria->id,
            'nombre_recurso' => TipoAsesoria::class,
            'descripcion_recurso' => $tipoAsesoria->tipOriDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $tipoAsesoriaOriginal : $tipoAsesoria->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoAsesoria->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoAsesoria::cargar($tipoAsesoria->id);
    }

    public static function eliminar($id)
    {
        $tipoAsesoria = TipoAsesoria::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoAsesoria->id,
            'nombre_recurso' => TipoAsesoria::class,
            'descripcion_recurso' => $tipoAsesoria->tipOriDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoAsesoria->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoAsesoria->delete();
    }
    use HasFactory;
}
