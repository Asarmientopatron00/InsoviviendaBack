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

class TipoPiso extends Model
{
    protected $table = 'tipos_piso'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipPisDescripcion',
        'tipPisEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_piso')
            ->select(
                'id',
                'tipPisDescripcion AS nombre',
                'tipPisEstado AS estado',
            );
        $query->orderBy('tipPisDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_piso')
            ->select(
                'id',
                'tipPisDescripcion As nombre',
                'tipPisEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipPisDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_piso.tipPisDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_piso.tipPisEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_piso.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_piso.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_piso.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_piso.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_piso.updated_at", "desc");
        }

        $tiposPiso = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tiposPiso ?? [] as $tipoPiso){
            array_push($datos, $tipoPiso);
        }

        $cantidadTiposPiso = count($tiposPiso);
        $to = isset($tiposPiso) && $cantidadTiposPiso > 0 ? $tiposPiso->currentPage() * $tiposPiso->perPage() : null;
        $to = isset($to) && isset($tiposPiso) && $to > $tiposPiso->total() && $cantidadTiposPiso > 0 ? $tiposPiso->total() : $to;
        $from = isset($to) && isset($tiposPiso) && $cantidadTiposPiso > 0 ?
            ( $tiposPiso->perPage() > $to ? 1 : ($to - $cantidadTiposPiso) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tiposPiso) && $cantidadTiposPiso > 0 ? +$tiposPiso->perPage() : 0,
            'pagina_actual' => isset($tiposPiso) && $cantidadTiposPiso > 0 ? $tiposPiso->currentPage() : 1,
            'ultima_pagina' => isset($tiposPiso) && $cantidadTiposPiso > 0 ? $tiposPiso->lastPage() : 0,
            'total' => isset($tiposPiso) && $cantidadTiposPiso > 0 ? $tiposPiso->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoPiso = TipoPiso::find($id);
        return [
            'id' => $tipoPiso->id,
            'nombre' => $tipoPiso->tipPisDescripcion,
            'estado' => $tipoPiso->tipPisEstado,
            'usuario_creacion_id' => $tipoPiso->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoPiso->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoPiso->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoPiso->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoPiso->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoPiso->updated_at))->format("Y-m-d H:i:s")
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
        $tipoPiso = isset($dto['id']) ? TipoPiso::find($dto['id']) : new TipoPiso();

        // Guardar objeto original para auditoria
        $tipoPisoOriginal = $tipoPiso->toJson();

        $tipoPiso->fill($dto);
        $guardado = $tipoPiso->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoPiso);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoPiso->id,
            'nombre_recurso' => TipoPiso::class,
            'descripcion_recurso' => $tipoPiso->tipPisDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $tipoPisoOriginal : $tipoPiso->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoPiso->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoPiso::cargar($tipoPiso->id);
    }

    public static function eliminar($id)
    {
        $tipoPiso = TipoPiso::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoPiso->id,
            'nombre_recurso' => TipoPiso::class,
            'descripcion_recurso' => $tipoPiso->tipPisDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoPiso->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoPiso->delete();
    }    
    use HasFactory;
}
