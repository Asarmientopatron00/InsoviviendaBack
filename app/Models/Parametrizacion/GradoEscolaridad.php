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

class GradoEscolaridad extends Model
{
    protected $table = 'grados_escolaridad'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'graEscDescripcion',
        'graEscEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('grados_escolaridad')
            ->select(
                'id',
                'graEscDescripcion AS nombre',
                'graEscEstado AS estado',
            );
        $query->orderBy('graEscDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('grados_escolaridad')
            ->select(
                'id',
                'graEscDescripcion As nombre',
                'graEscEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('graEscDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('grados_escolaridad.graEscDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('grados_escolaridad.graEscEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('grados_escolaridad.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('grados_escolaridad.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('grados_escolaridad.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('grados_escolaridad.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("grados_escolaridad.updated_at", "desc");
        }

        $gradoEscolaridad = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($gradoEscolaridad ?? [] as $gradoescolaridad){
            array_push($datos, $gradoescolaridad);
        }

        $cantidadGradoEscolaridad = count($gradoEscolaridad);
        $to = isset($gradoEscolaridad) && $cantidadGradoEscolaridad > 0 ? $gradoEscolaridad->currentPage() * $gradoEscolaridad->perPage() : null;
        $to = isset($to) && isset($gradoEscolaridad) && $to > $gradoEscolaridad->total() && $cantidadGradoEscolaridad > 0 ? $gradoEscolaridad->total() : $to;
        $from = isset($to) && isset($gradoEscolaridad) && $cantidadGradoEscolaridad > 0 ?
            ( $gradoEscolaridad->perPage() > $to ? 1 : ($to - $cantidadGradoEscolaridad) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($gradoEscolaridad) && $cantidadGradoEscolaridad > 0 ? +$gradoEscolaridad->perPage() : 0,
            'pagina_actual' => isset($gradoEscolaridad) && $cantidadGradoEscolaridad > 0 ? $gradoEscolaridad->currentPage() : 1,
            'ultima_pagina' => isset($gradoEscolaridad) && $cantidadGradoEscolaridad > 0 ? $gradoEscolaridad->lastPage() : 0,
            'total' => isset($gradoEscolaridad) && $cantidadGradoEscolaridad > 0 ? $gradoEscolaridad->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $gradoEscolaridad = GradoEscolaridad::find($id);
        return [
            'id' => $gradoEscolaridad->id,
            'nombre' => $gradoEscolaridad->graEscDescripcion,
            'estado' => $gradoEscolaridad->graEscEstado,
            'usuario_creacion_id' => $gradoEscolaridad->usuario_creacion_id,
            'usuario_creacion_nombre' => $gradoEscolaridad->usuario_creacion_nombre,
            'usuario_modificacion_id' => $gradoEscolaridad->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $gradoEscolaridad->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($gradoEscolaridad->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($gradoEscolaridad->updated_at))->format("Y-m-d H:i:s")
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
        $gradoEscolaridad = isset($dto['id']) ? GradoEscolaridad::find($dto['id']) : new GradoEscolaridad();

        // Guardar objeto original para auditoria
        $estCivDescripcionOriginal = $gradoEscolaridad->toJson();

        $gradoEscolaridad->fill($dto);
        $guardado = $gradoEscolaridad->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $gradoEscolaridad);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $gradoEscolaridad->id,
            'nombre_recurso' => GradoEscolaridad::class,
            'descripcion_recurso' => $gradoEscolaridad->estCivDesripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $estCivDescripcionOriginal : $gradoEscolaridad->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $gradoEscolaridad->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return GradoEscolaridad::cargar($gradoEscolaridad->id);
    }

    public static function eliminar($id)
    {
        $gradoEscolaridad = GradoEscolaridad::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $gradoEscolaridad->id,
            'nombre_recurso' => GradoEscolaridad::class,
            'descripcion_recurso' => $gradoEscolaridad->graEscDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $gradoEscolaridad->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $gradoEscolaridad->delete();
    }

    use HasFactory;    

}
