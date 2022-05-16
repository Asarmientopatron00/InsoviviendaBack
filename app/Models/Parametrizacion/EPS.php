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

class EPS extends Model
{
    protected $table = 'eps'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'epsDescripcion',
        'epsEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('eps')
            ->select(
                'id',
                'epsDescripcion AS nombre',
                'epsEstado AS estado',
            );
        $query->orderBy('epsDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('eps')
            ->select(
                'id',
                'epsDescripcion As nombre',
                'epsEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('epsDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('eps.epsDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('eps.epsEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('eps.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('eps.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('eps.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('eps.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("eps.updated_at", "desc");
        }

        $ePS = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($ePS ?? [] as $eps){
            array_push($datos, $eps);
        }

        $cantidadEps = count($ePS);
        $to = isset($ePS) && $cantidadEps > 0 ? $ePS->currentPage() * $ePS->perPage() : null;
        $to = isset($to) && isset($ePS) && $to > $ePS->total() && $cantidadEps > 0 ? $ePS->total() : $to;
        $from = isset($to) && isset($ePS) && $cantidadEps > 0 ?
            ( $ePS->perPage() > $to ? 1 : ($to - $cantidadEps) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($ePS) && $cantidadEps > 0 ? +$ePS->perPage() : 0,
            'pagina_actual' => isset($ePS) && $cantidadEps > 0 ? $ePS->currentPage() : 1,
            'ultima_pagina' => isset($ePS) && $cantidadEps > 0 ? $ePS->lastPage() : 0,
            'total' => isset($ePS) && $cantidadEps > 0 ? $ePS->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $ePS = EPS::find($id);
        return [
            'id' => $ePS->id,
            'nombre' => $ePS->epsDescripcion,
            'estado' => $ePS->epsEstado,
            'usuario_creacion_id' => $ePS->usuario_creacion_id,
            'usuario_creacion_nombre' => $ePS->usuario_creacion_nombre,
            'usuario_modificacion_id' => $ePS->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $ePS->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($ePS->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($ePS->updated_at))->format("Y-m-d H:i:s")
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
        $ePS = isset($dto['id']) ? EPS::find($dto['id']) : new EPS();

        // Guardar objeto original para auditoria
        $estCivDescripcionOriginal = $ePS->toJson();

        $ePS->fill($dto);
        $guardado = $ePS->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $ePS);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $ePS->id,
            'nombre_recurso' => EPS::class,
            'descripcion_recurso' => $ePS->estCivDesripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $estCivDescripcionOriginal : $ePS->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $ePS->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return EPS::cargar($ePS->id);
    }

    public static function eliminar($id)
    {
        $ePS = EPS::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $ePS->id,
            'nombre_recurso' => EPS::class,
            'descripcion_recurso' => $ePS->epsDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $ePS->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $ePS->delete();
    }

    use HasFactory;    
}
