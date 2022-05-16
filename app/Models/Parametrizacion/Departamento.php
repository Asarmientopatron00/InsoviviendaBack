<?php

namespace App\Models\Parametrizacion;

use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use App\Models\Parametrizacion\Pais;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departamento extends Model
{
protected $table = 'departamentos'; // nombre de la tabla en la base de datos

protected $fillable = [ // nombres de los campos
    'departamentosDescripcion',
    'pais_id',
    'departamentosEstado',
    'usuario_creacion_id',
    'usuario_creacion_nombre',
    'usuario_modificacion_id',
    'usuario_modificacion_nombre',
];

public function pais(){
    return $this->belongsTo(Pais::class, 'pais_id');
}

public static function obtenerColeccionLigera($dto){
    $query = DB::table('departamentos')
        ->join('paises', 'pais_id', '=', 'paises.id')
        ->select(
            'departamentos.id',
            'departamentosDescripcion As nombre',
            'paises.id As pais_id',
            'paisesDescripcion As pais',
            'departamentosEstado As estado',
        );
    if(isset($dto['pais_id'])){
        $query->where('pais_id', $dto['pais_id']);
    }
        
    $query->orderBy('departamentosDescripcion', 'asc');
    return $query->get();
}

public static function obtenerColeccion($dto){
    $query = DB::table('departamentos')
        ->join('paises', 'departamentos.pais_id', '=', 'paises.id')
        ->select(
            'departamentos.id',
            'departamentosDescripcion As nombre',
            'paisesDescripcion As pais',
            'departamentosEstado As estado',
            'departamentos.usuario_creacion_id',
            'departamentos.usuario_creacion_nombre',
            'departamentos.usuario_modificacion_id',
            'departamentos.usuario_modificacion_nombre',
            'departamentos.created_at AS fecha_creacion',
            'departamentos.updated_at AS fecha_modificacion',
        );

    if(isset($dto['nombre'])){
        $query->where('departamentosDescripcion', 'like', '%' . $dto['nombre'] . '%');
    }

    if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
        foreach ($dto['ordenar_por'] as $attribute => $value){
            if($attribute == 'nombre'){
                $query->orderBy('departamentos.departamentosDescripcion', $value);
            }
            if($attribute == 'pais'){
                $query->orderBy('paises.paisesDescripcion', $value);
            }
            if($attribute == 'estado'){
                $query->orderBy('departamentos.departamentosEstado', $value);
            }
            if($attribute == 'usuario_creacion_nombre'){
                $query->orderBy('departamentos.usuario_creacion_nombre', $value);
            }
            if($attribute == 'usuario_modificacion_nombre'){
                $query->orderBy('departamentos.usuario_modificacion_nombre', $value);
            }
            if($attribute == 'fecha_creacion'){
                $query->orderBy('departamentos.created_at', $value);
            }
            if($attribute == 'fecha_modificacion'){
                $query->orderBy('departamentos.updated_at', $value);
            }
        }
    }else{
        $query->orderBy("departamentos.updated_at", "desc");
    }

    $Departamentos = $query->paginate($dto['limite'] ?? 100);
    $datos = [];

    foreach ($Departamentos ?? [] as $departamento){
        array_push($datos, $departamento);
    }

    $cantidadDepartamentos = count($Departamentos);
    $to = isset($Departamentos) && $cantidadDepartamentos > 0 ? $Departamentos->currentPage() * $Departamentos->perPage() : null;
    $to = isset($to) && isset($Departamentos) && $to > $Departamentos->total() && $cantidadDepartamentos > 0 ? $Departamentos->total() : $to;
    $from = isset($to) && isset($Departamentos) && $cantidadDepartamentos > 0 ?
        ( $Departamentos->perPage() > $to ? 1 : ($to - $cantidadDepartamentos) + 1 )
        : null;

    return [
        'datos' => $datos,
        'desde' => $from,
        'hasta' => $to,
        'por_pagina' => isset($Departamentos) && $cantidadDepartamentos > 0 ? +$Departamentos->perPage() : 0,
        'pagina_actual' => isset($Departamentos) && $cantidadDepartamentos > 0 ? $Departamentos->currentPage() : 1,
        'ultima_pagina' => isset($Departamentos) && $cantidadDepartamentos > 0 ? $Departamentos->lastPage() : 0,
        'total' => isset($Departamentos) && $cantidadDepartamentos > 0 ? $Departamentos->total() : 0
    ];
}

public static function cargar($id)
{
    $departamento = Departamento::find($id);
    $pais = $departamento->pais;
    return [
        'id' => $departamento->id,
        'nombre' => $departamento->departamentosDescripcion,
        'pais_id' => $departamento->pais_id,
        'estado' => $departamento->departamentosEstado,
        'usuario_creacion_id' => $departamento->usuario_creacion_id,
        'usuario_creacion_nombre' => $departamento->usuario_creacion_nombre,
        'usuario_modificacion_id' => $departamento->usuario_modificacion_id,
        'usuario_modificacion_nombre' => $departamento->usuario_modificacion_nombre,
        'fecha_creacion' => (new Carbon($departamento->created_at))->format("Y-m-d H:i:s"),
        'fecha_modificacion' => (new Carbon($departamento->updated_at))->format("Y-m-d H:i:s"),
        'pais' => isset($pais) ? [
            'id' => $pais->id,
            'nombre' => $pais->paisesDescripcion
        ] : null,
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
    $departamento = isset($dto['id']) ? Departamento::find($dto['id']) : new Departamento();

    // Guardar objeto original para auditoria
    $departamentoOriginal = $departamento->toJson();

    $departamento->fill($dto);
    $guardado = $departamento->save();
    if(!$guardado){
        throw new Exception("Ocurrió un error al intentar guardar el departamento.", $departamento);
    }


    // Guardar auditoria
    $auditoriaDto = [
        'id_recurso' => $departamento->id,
        'nombre_recurso' => Departamento::class,
        'descripcion_recurso' => $departamento->departamentosDescripcion,
        'pais_recurso' => $departamento->pais_id,
        'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
        'recurso_original' => isset($dto['id']) ? $departamentoOriginal : $departamento->toJson(),
        'recurso_resultante' => isset($dto['id']) ? $departamento->toJson() : null
    ];
    
    AuditoriaTabla::crear($auditoriaDto);
    
    return Departamento::cargar($departamento->id);
}

public static function eliminar($id)
{
    $departamento = Departamento::find($id);

    // Guardar auditoria
    $auditoriaDto = [
        'id_recurso' => $departamento->id,
        'nombre_recurso' => Departamento::class,
        'descripcion_recurso' => $departamento->departamentosDescripcion,
        'pais_recurso' => $departamento->pais_id,
        'accion' => AccionAuditoriaEnum::ELIMINAR,
        'recurso_original' => $departamento->toJson()
    ];
    AuditoriaTabla::crear($auditoriaDto);

    return $departamento->delete();
}

use HasFactory;
}
