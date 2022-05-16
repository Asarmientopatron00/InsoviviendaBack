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

class TipoVivienda extends Model
{
    protected $table = 'tipos_vivienda'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipVivDescripcion',
        'tipVivEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_vivienda')
            ->select(
                'id',
                'tipVivDescripcion AS nombre',
                'tipVivEstado AS estado',
            );
        $query->orderBy('tipVivDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_vivienda')
            ->select(
                'id',
                'tipVivDescripcion As nombre',
                'tipVivEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipVivDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_vivienda.tipVivDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_vivienda.tipVivEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_vivienda.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_vivienda.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_vivienda.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_vivienda.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_vivienda.updated_at", "desc");
        }

        $tiposVivienda = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tiposVivienda ?? [] as $tipoVivienda){
            array_push($datos, $tipoVivienda);
        }

        $cantidadTiposVivienda = count($tiposVivienda);
        $to = isset($tiposVivienda) && $cantidadTiposVivienda > 0 ? $tiposVivienda->currentPage() * $tiposVivienda->perPage() : null;
        $to = isset($to) && isset($tiposVivienda) && $to > $tiposVivienda->total() && $cantidadTiposVivienda > 0 ? $tiposVivienda->total() : $to;
        $from = isset($to) && isset($tiposVivienda) && $cantidadTiposVivienda > 0 ?
            ( $tiposVivienda->perPage() > $to ? 1 : ($to - $cantidadTiposVivienda) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tiposVivienda) && $cantidadTiposVivienda > 0 ? +$tiposVivienda->perPage() : 0,
            'pagina_actual' => isset($tiposVivienda) && $cantidadTiposVivienda > 0 ? $tiposVivienda->currentPage() : 1,
            'ultima_pagina' => isset($tiposVivienda) && $cantidadTiposVivienda > 0 ? $tiposVivienda->lastPage() : 0,
            'total' => isset($tiposVivienda) && $cantidadTiposVivienda > 0 ? $tiposVivienda->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoVivienda = TipoVivienda::find($id);
        return [
            'id' => $tipoVivienda->id,
            'nombre' => $tipoVivienda->tipVivDescripcion,
            'estado' => $tipoVivienda->tipVivEstado,
            'usuario_creacion_id' => $tipoVivienda->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoVivienda->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoVivienda->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoVivienda->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoVivienda->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoVivienda->updated_at))->format("Y-m-d H:i:s")
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
        $tipoVivienda = isset($dto['id']) ? TipoVivienda::find($dto['id']) : new TipoVivienda();

        // Guardar objeto original para auditoria
        $tipoViviendaOriginal = $tipoVivienda->toJson();

        $tipoVivienda->fill($dto);
        $guardado = $tipoVivienda->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoVivienda);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoVivienda->id,
            'nombre_recurso' => TipoVivienda::class,
            'descripcion_recurso' => $tipoVivienda->tipVivDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $tipoViviendaOriginal : $tipoVivienda->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoVivienda->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoVivienda::cargar($tipoVivienda->id);
    }

    public static function eliminar($id)
    {
        $tipoVivienda = TipoVivienda::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoVivienda->id,
            'nombre_recurso' => TipoVivienda::class,
            'descripcion_recurso' => $tipoVivienda->tipVivDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoVivienda->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoVivienda->delete();
    }    
    use HasFactory;
}
