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

class EstadoCivil extends Model
{
    protected $table = 'estados_civil'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'estCivDescripcion',
        'estCivEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('estados_civil')
            ->select(
                'id',
                'estCivDescripcion AS nombre',
                'estCivEstado AS estado',
            );
        $query->orderBy('estCivDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('estados_civil')
            ->select(
                'id',
                'estCivDescripcion As nombre',
                'estCivEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('estCivDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('estados_civil.estCivDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('estados_civil.estCivEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('estados_civil.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('estados_civil.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('estados_civil.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('estados_civil.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("estados_civil.updated_at", "desc");
        }

        $estadoCivil = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($estadoCivil ?? [] as $estadocivil){
            array_push($datos, $estadocivil);
        }

        $cantidadEstadoCivil = count($estadoCivil);
        $to = isset($estadoCivil) && $cantidadEstadoCivil > 0 ? $estadoCivil->currentPage() * $estadoCivil->perPage() : null;
        $to = isset($to) && isset($estadoCivil) && $to > $estadoCivil->total() && $cantidadEstadoCivil > 0 ? $estadoCivil->total() : $to;
        $from = isset($to) && isset($estadoCivil) && $cantidadEstadoCivil > 0 ?
            ( $estadoCivil->perPage() > $to ? 1 : ($to - $cantidadEstadoCivil) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($estadoCivil) && $cantidadEstadoCivil > 0 ? +$estadoCivil->perPage() : 0,
            'pagina_actual' => isset($estadoCivil) && $cantidadEstadoCivil > 0 ? $estadoCivil->currentPage() : 1,
            'ultima_pagina' => isset($estadoCivil) && $cantidadEstadoCivil > 0 ? $estadoCivil->lastPage() : 0,
            'total' => isset($estadoCivil) && $cantidadEstadoCivil > 0 ? $estadoCivil->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $estadoCivil = EstadoCivil::find($id);
        return [
            'id' => $estadoCivil->id,
            'nombre' => $estadoCivil->estCivDescripcion,
            'estado' => $estadoCivil->estCivEstado,
            'usuario_creacion_id' => $estadoCivil->usuario_creacion_id,
            'usuario_creacion_nombre' => $estadoCivil->usuario_creacion_nombre,
            'usuario_modificacion_id' => $estadoCivil->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $estadoCivil->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($estadoCivil->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($estadoCivil->updated_at))->format("Y-m-d H:i:s")
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
        $estadoCivil = isset($dto['id']) ? EstadoCivil::find($dto['id']) : new EstadoCivil();

        // Guardar objeto original para auditoria
        $estCivDescripcionOriginal = $estadoCivil->toJson();

        $estadoCivil->fill($dto);
        $guardado = $estadoCivil->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $estadoCivil);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $estadoCivil->id,
            'nombre_recurso' => EstadoCivil::class,
            'descripcion_recurso' => $estadoCivil->estCivDesripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $estCivDescripcionOriginal : $estadoCivil->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $estadoCivil->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return EstadoCivil::cargar($estadoCivil->id);
    }

    public static function eliminar($id)
    {
        $estadoCivil = EstadoCivil::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $estadoCivil->id,
            'nombre_recurso' => EstadoCivil::class,
            'descripcion_recurso' => $estadoCivil->estCivDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $estadoCivil->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $estadoCivil->delete();
    }

    use HasFactory;
}
