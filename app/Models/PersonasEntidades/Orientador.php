<?php

namespace App\Models\PersonasEntidades;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Seguridad\AuditoriaTabla;

class Orientador extends Model
{
    protected $table = 'orientadores'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'orientadoresIdentificacion',
        'orientadoresNombre',
        'orientadoresEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('orientadores')
            ->select(
                'id',
                'orientadoresIdentificacion AS identificacion',
                'orientadoresNombre AS nombre',
                'orientadoresEstado AS estado',
            );
        $query->orderBy('orientadoresIdentificacion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('orientadores')
            ->select(
                'id',
                'orientadoresIdentificacion As identificacion',
                'orientadoresNombre AS nombre',
                'orientadoresEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('orientadoresNombre', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'identificacion'){
                    $query->orderBy('orientadores.orientadoresIdentificacion', $value);
                }
                if($attribute == 'nombre'){
                    $query->orderBy('orientadores.orientadoresNombre', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('orientadores.orientadoresEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('orientadores.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('orientadores.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('orientadores.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('orientadores.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("orientadores.updated_at", "desc");
        }

        $orientadores = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($orientadores ?? [] as $orientador){
            array_push($datos, $orientador);
        }

        $cantidadOrientadores = count($orientadores);
        $to = isset($orientadores) && $cantidadOrientadores > 0 ? $orientadores->currentPage() * $orientadores->perPage() : null;
        $to = isset($to) && isset($orientadores) && $to > $orientadores->total() && $cantidadOrientadores > 0 ? $orientadores->total() : $to;
        $from = isset($to) && isset($orientadores) && $cantidadOrientadores > 0 ?
            ( $orientadores->perPage() > $to ? 1 : ($to - $cantidadOrientadores) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($orientadores) && $cantidadOrientadores > 0 ? +$orientadores->perPage() : 0,
            'pagina_actual' => isset($orientadores) && $cantidadOrientadores > 0 ? $orientadores->currentPage() : 1,
            'ultima_pagina' => isset($orientadores) && $cantidadOrientadores > 0 ? $orientadores->lastPage() : 0,
            'total' => isset($orientadores) && $cantidadOrientadores > 0 ? $orientadores->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $orientador = Orientador::find($id);
        return [
            'id' => $orientador->id,
            'identificacion' => $orientador->orientadoresIdentificacion,
            'nombre' => $orientador->orientadoresNombre,
            'estado' => $orientador->orientadoresEstado,
            'usuario_creacion_id' => $orientador->usuario_creacion_id,
            'usuario_creacion_nombre' => $orientador->usuario_creacion_nombre,
            'usuario_modificacion_id' => $orientador->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $orientador->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($orientador->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($orientador->updated_at))->format("Y-m-d H:i:s")
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
        $orientador = isset($dto['id']) ? Orientador::find($dto['id']) : new Orientador();

        // Guardar objeto original para auditoria
        $orientadorOriginal = $orientador->toJson();

        $orientador->fill($dto);
        $guardado = $orientador->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $orientador);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $orientador->id,
            'nombre_recurso' => Orientador::class,
            'descripcion_recurso' => $orientador->orientadoresIdentificacion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $orientadorOriginal : $orientador->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $orientador->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return Orientador::cargar($orientador->id);
    }

    public static function eliminar($id)
    {
        $orientador = Orientador::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $orientador->id,
            'nombre_recurso' => Orientador::class,
            'descripcion_recurso' => $orientador->orientadoresIdentificacion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $orientador->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $orientador->delete();
    }

    use HasFactory;
}
