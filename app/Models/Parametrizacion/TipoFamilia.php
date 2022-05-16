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

class TipoFamilia extends Model
{
    protected $table = 'tipos_familia'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipFamDescripcion',
        'tipFamEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_familia')
            ->select(
                'id',
                'tipFamDescripcion AS nombre',
                'tipFamEstado AS estado',
            );
        $query->orderBy('tipFamDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_familia')
            ->select(
                'id',
                'tipFamDescripcion As nombre',
                'tipFamEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipFamDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_familia.tipFamDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_familia.tipFamEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_familia.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_familia.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_familia.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_familia.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_familia.updated_at", "desc");
        }

        $tipoFamilia = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tipoFamilia ?? [] as $tipofamilia){
            array_push($datos, $tipofamilia);
        }

        $cantidadTipoFamilia = count($tipoFamilia);
        $to = isset($tipoFamilia) && $cantidadTipoFamilia > 0 ? $tipoFamilia->currentPage() * $tipoFamilia->perPage() : null;
        $to = isset($to) && isset($tipoFamilia) && $to > $tipoFamilia->total() && $cantidadTipoFamilia > 0 ? $tipoFamilia->total() : $to;
        $from = isset($to) && isset($tipoFamilia) && $cantidadTipoFamilia > 0 ?
            ( $tipoFamilia->perPage() > $to ? 1 : ($to - $cantidadTipoFamilia) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tipoFamilia) && $cantidadTipoFamilia > 0 ? +$tipoFamilia->perPage() : 0,
            'pagina_actual' => isset($tipoFamilia) && $cantidadTipoFamilia > 0 ? $tipoFamilia->currentPage() : 1,
            'ultima_pagina' => isset($tipoFamilia) && $cantidadTipoFamilia > 0 ? $tipoFamilia->lastPage() : 0,
            'total' => isset($tipoFamilia) && $cantidadTipoFamilia > 0 ? $tipoFamilia->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoFamilia = TipoFamilia::find($id);
        return [
            'id' => $tipoFamilia->id,
            'nombre' => $tipoFamilia->tipFamDescripcion,
            'estado' => $tipoFamilia->tipFamEstado,
            'usuario_creacion_id' => $tipoFamilia->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoFamilia->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoFamilia->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoFamilia->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoFamilia->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoFamilia->updated_at))->format("Y-m-d H:i:s")
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
        $tipoFamilia = isset($dto['id']) ? TipoFamilia::find($dto['id']) : new TipoFamilia();

        // Guardar objeto original para auditoria
        $estCivDescripcionOriginal = $tipoFamilia->toJson();

        $tipoFamilia->fill($dto);
        $guardado = $tipoFamilia->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoFamilia);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoFamilia->id,
            'nombre_recurso' => TipoFamilia::class,
            'descripcion_recurso' => $tipoFamilia->estCivDesripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $estCivDescripcionOriginal : $tipoFamilia->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoFamilia->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoFamilia::cargar($tipoFamilia->id);
    }

    public static function eliminar($id)
    {
        $tipoFamilia = TipoFamilia::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoFamilia->id,
            'nombre_recurso' => TipoFamilia::class,
            'descripcion_recurso' => $tipoFamilia->tipFamDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoFamilia->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoFamilia->delete();
    }

    use HasFactory;    
}