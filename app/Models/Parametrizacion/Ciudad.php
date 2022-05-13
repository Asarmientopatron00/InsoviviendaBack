<?php

namespace App\Models\Parametrizacion;

use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use App\Models\Parametrizacion\Departamento;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ciudad extends Model
{
protected $table = 'ciudades'; // nombre de la tabla en la base de datos

protected $fillable = [ // nombres de los campos
    'ciudadesDescripcion',
    'departamento_id',
    'ciudadesEstado',
    'usuario_creacion_id',
    'usuario_creacion_nombre',
    'usuario_modificacion_id',
    'usuario_modificacion_nombre',
];

public function departamento(){
    return $this->belongsTo(Departamento::class, 'departamento_id');
}

public static function obtenerColeccionLigera($dto){
    $query = DB::table('ciudades')
        ->join('departamentos', 'departamento_id', '=', 'departamentos.id')
        ->select(
            'ciudades.id',
            'ciudadesDescripcion As nombre',
            'departamentosDescripcion As departamento',
            'ciudadesEstado As estado',
        );

    if(isset($dto['departamento_id'])){
            $query->where('departamento_id', $dto['departamento_id']);
    }        

    $query->orderBy('ciudadesDescripcion', 'asc');
    return $query->get();
}

public static function obtenerColeccion($dto){
    $query = DB::table('ciudades')
        ->join('departamentos', 'departamento_id', '=', 'departamentos.id')
        ->select(
            'ciudades.id',
            'ciudadesDescripcion As nombre',
            'departamentosDescripcion As departamento',
            'ciudadesEstado As estado',
            'ciudades.usuario_creacion_id',
            'ciudades.usuario_creacion_nombre',
            'ciudades.usuario_modificacion_id',
            'ciudades.usuario_modificacion_nombre',
            'ciudades.created_at AS fecha_creacion',
            'ciudades.updated_at AS fecha_modificacion',
        );

    if(isset($dto['nombre'])){
        $query->where('ciudadesDescripcion', 'like', '%' . $dto['nombre'] . '%');
    }

    if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
        foreach ($dto['ordenar_por'] as $attribute => $value){
            if($attribute == 'nombre'){
                $query->orderBy('ciudades.ciudadesDescripcion', $value);
            }
            if($attribute == 'departamento'){
                $query->orderBy('departamento.departamentosDescripcion', $value);
            }
            if($attribute == 'estado'){
                $query->orderBy('ciudades.ciudadesEstado', $value);
            }
            if($attribute == 'usuario_creacion_nombre'){
                $query->orderBy('ciudades.usuario_creacion_nombre', $value);
            }
            if($attribute == 'usuario_modificacion_nombre'){
                $query->orderBy('ciudades.usuario_modificacion_nombre', $value);
            }
            if($attribute == 'fecha_creacion'){
                $query->orderBy('ciudades.created_at', $value);
            }
            if($attribute == 'fecha_modificacion'){
                $query->orderBy('ciudades.updated_at', $value);
            }
        }
    }else{
        $query->orderBy("ciudades.updated_at", "desc");
    }

    $Ciudades = $query->paginate($dto['limite'] ?? 100);
    $datos = [];

    foreach ($Ciudades ?? [] as $ciudad){
        array_push($datos, $ciudad);
    }

    $cantidadCiudades = count($Ciudades);
    $to = isset($Ciudades) && $cantidadCiudades > 0 ? $Ciudades->currentPage() * $Ciudades->perPage() : null;
    $to = isset($to) && isset($Ciudades) && $to > $Ciudades->total() && $cantidadCiudades > 0 ? $Ciudades->total() : $to;
    $from = isset($to) && isset($Ciudades) && $cantidadCiudades > 0 ?
        ( $Ciudades->perPage() > $to ? 1 : ($to - $cantidadCiudades) + 1 )
        : null;

    return [
        'datos' => $datos,
        'desde' => $from,
        'hasta' => $to,
        'por_pagina' => isset($Ciudades) && $cantidadCiudades > 0 ? +$Ciudades->perPage() : 0,
        'pagina_actual' => isset($Ciudades) && $cantidadCiudades > 0 ? $Ciudades->currentPage() : 1,
        'ultima_pagina' => isset($Ciudades) && $cantidadCiudades > 0 ? $Ciudades->lastPage() : 0,
        'total' => isset($Ciudades) && $cantidadCiudades > 0 ? $Ciudades->total() : 0
    ];
}

public static function cargar($id)
{
    $ciudad = Ciudad::find($id);
    $departamento = $ciudad->departamento;
    return [
        'id' => $ciudad->id,
        'nombre' => $ciudad->ciudadesDescripcion,
        'departamento_id' => $ciudad->departamento_id,
        'estado' => $ciudad->ciudadesEstado,
        'usuario_creacion_id' => $ciudad->usuario_creacion_id,
        'usuario_creacion_nombre' => $ciudad->usuario_creacion_nombre,
        'usuario_modificacion_id' => $ciudad->usuario_modificacion_id,
        'usuario_modificacion_nombre' => $ciudad->usuario_modificacion_nombre,
        'fecha_creacion' => (new Carbon($ciudad->created_at))->format("Y-m-d H:i:s"),
        'fecha_modificacion' => (new Carbon($ciudad->updated_at))->format("Y-m-d H:i:s"),
        'departamento' => isset($departamento) ? [
            'id' => $departamento->id,
            'nombre' => $departamento->departamentosDescripcion
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
    $ciudad = isset($dto['id']) ? Ciudad::find($dto['id']) : new Ciudad();

    // Guardar objeto original para auditoria
    $ciudadOriginal = $ciudad->toJson();

    $ciudad->fill($dto);
    $guardado = $ciudad->save();
    if(!$guardado){
        throw new Exception("Ocurrió un error al intentar guardar la ciudad.", $ciudad);
    }


    // Guardar auditoria
    $auditoriaDto = [
        'id_recurso' => $ciudad->id,
        'nombre_recurso' => Ciudad::class,
        'descripcion_recurso' => $ciudad->ciudadesDescripcion,
        'departamento_recurso' => $ciudad->departamento_id,
        'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
        'recurso_original' => isset($dto['id']) ? $ciudadOriginal : $ciudad->toJson(),
        'recurso_resultante' => isset($dto['id']) ? $ciudad->toJson() : null
    ];
    
    AuditoriaTabla::crear($auditoriaDto);
    
    return Ciudad::cargar($ciudad->id);
}

public static function eliminar($id)
{
    $ciudad = Ciudad::find($id);

    // Guardar auditoria
    $auditoriaDto = [
        'id_recurso' => $ciudad->id,
        'nombre_recurso' => Ciudad::class,
        'descripcion_recurso' => $ciudad->ciudadesDescripcion,
        'departamento_recurso' => $ciudad->departamento_id,
        'accion' => AccionAuditoriaEnum::ELIMINAR,
        'recurso_original' => $ciudad->toJson()
    ];
    AuditoriaTabla::crear($auditoriaDto);

    return $ciudad->delete();
}

use HasFactory;
}
