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

class Pais extends Model
{
protected $table = 'paises'; // nombre de la tabla en la base de datos

protected $fillable = [ // nombres de los campos
    'paisesDescripcion',
    'paisesEstado',
    'usuario_creacion_id',
    'usuario_creacion_nombre',
    'usuario_modificacion_id',
    'usuario_modificacion_nombre',
];

public static function obtenerColeccionLigera($dto){
    $query = DB::table('Paises')
        ->select(
            'id',
            'paisesDescripcion AS nombre',
            'paisesEstado AS estado',
        );
    $query->orderBy('paisesDescripcion', 'asc');
    return $query->get();
}

public static function obtenerColeccion($dto){
    $query = DB::table('Paises')
        ->select(
            'id',
            'paisesDescripcion As nombre',
            'paisesEstado As estado',
            'usuario_creacion_id',
            'usuario_creacion_nombre',
            'usuario_modificacion_id',
            'usuario_modificacion_nombre',
            'created_at AS fecha_creacion',
            'updated_at AS fecha_modificacion',
        );

    if(isset($dto['nombre'])){
        $query->where('paisesDescripcion', 'like', '%' . $dto['nombre'] . '%');
    }

    if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
        foreach ($dto['ordenar_por'] as $attribute => $value){
            if($attribute == 'nombre'){
                $query->orderBy('Paises.paisesDescripcion', $value);
            }
            if($attribute == 'estado'){
                $query->orderBy('Paises.paisesEstado', $value);
            }
            if($attribute == 'usuario_creacion_nombre'){
                $query->orderBy('Paises.usuario_creacion_nombre', $value);
            }
            if($attribute == 'usuario_modificacion_nombre'){
                $query->orderBy('Paises.usuario_modificacion_nombre', $value);
            }
            if($attribute == 'fecha_creacion'){
                $query->orderBy('Paises.created_at', $value);
            }
            if($attribute == 'fecha_modificacion'){
                $query->orderBy('Paises.updated_at', $value);
            }
        }
    }else{
        $query->orderBy("Paises.updated_at", "desc");
    }

    $paises = $query->paginate($dto['limite'] ?? 100);
    $datos = [];

    foreach ($paises ?? [] as $pais){
        array_push($datos, $pais);
    }

    $cantidadpaises = count($paises);
    $to = isset($paises) && $cantidadpaises > 0 ? $paises->currentPage() * $paises->perPage() : null;
    $to = isset($to) && isset($paises) && $to > $paises->total() && $cantidadpaises > 0 ? $paises->total() : $to;
    $from = isset($to) && isset($paises) && $cantidadpaises > 0 ?
        ( $paises->perPage() > $to ? 1 : ($to - $cantidadpaises) + 1 )
        : null;

    return [
        'datos' => $datos,
        'desde' => $from,
        'hasta' => $to,
        'por_pagina' => isset($paises) && $cantidadpaises > 0 ? +$paises->perPage() : 0,
        'pagina_actual' => isset($paises) && $cantidadpaises > 0 ? $paises->currentPage() : 1,
        'ultima_pagina' => isset($paises) && $cantidadpaises > 0 ? $paises->lastPage() : 0,
        'total' => isset($paises) && $cantidadpaises > 0 ? $paises->total() : 0
    ];
}

public static function cargar($id)
{
    $pais = pais::find($id);
    return [
        'id' => $pais->id,
        'nombre' => $pais->paisesDescripcion,
        'estado' => $pais->paisesEstado,
        'usuario_creacion_id' => $pais->usuario_creacion_id,
        'usuario_creacion_nombre' => $pais->usuario_creacion_nombre,
        'usuario_modificacion_id' => $pais->usuario_modificacion_id,
        'usuario_modificacion_nombre' => $pais->usuario_modificacion_nombre,
        'fecha_creacion' => (new Carbon($pais->created_at))->format("Y-m-d H:i:s"),
        'fecha_modificacion' => (new Carbon($pais->updated_at))->format("Y-m-d H:i:s")
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
    $pais = isset($dto['id']) ? pais::find($dto['id']) : new pais();

    // Guardar objeto original para auditoria
    $paisOriginal = $pais->toJson();

    $pais->fill($dto);
    $guardado = $pais->save();
    if(!$guardado){
        throw new Exception("Ocurrió un error al intentar guardar el pais.", $pais);
    }


    // Guardar auditoria
    $auditoriaDto = [
        'id_recurso' => $pais->id,
        'nombre_recurso' => pais::class,
        'descripcion_recurso' => $pais->paisesDescripcion,
        'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
        'recurso_original' => isset($dto['id']) ? $paisOriginal : $pais->toJson(),
        'recurso_resultante' => isset($dto['id']) ? $pais->toJson() : null
    ];
    
    AuditoriaTabla::crear($auditoriaDto);
    
    return pais::cargar($pais->id);
}

public static function eliminar($id)
{
    $pais = pais::find($id);

    // Guardar auditoria
    $auditoriaDto = [
        'id_recurso' => $pais->id,
        'nombre_recurso' => pais::class,
        'descripcion_recurso' => $pais->paisesDescripcion,
        'accion' => AccionAuditoriaEnum::ELIMINAR,
        'recurso_original' => $pais->toJson()
    ];
    AuditoriaTabla::crear($auditoriaDto);

    return $pais->delete();
}

use HasFactory;
}
