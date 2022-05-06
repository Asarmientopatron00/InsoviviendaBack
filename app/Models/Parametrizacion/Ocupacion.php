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

class Ocupacion extends Model
{
    protected $table = 'ocupaciones'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'ocupacionesDescripcion',
        'ocupacionesEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('ocupaciones')
            ->select(
                'id',
                'ocupacionesDescripcion AS nombre',
                'ocupacionesEstado AS estado',
            );
        $query->orderBy('ocupacionesDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('ocupaciones')
            ->select(
                'id',
                'ocupacionesDescripcion As nombre',
                'ocupacionesEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('ocupacionesDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('ocupaciones.ocupacionesDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('ocupaciones.ocupacionesEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('ocupaciones.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('ocupaciones.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('ocupaciones.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('ocupaciones.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("ocupaciones.updated_at", "desc");
        }

        $ocupaciones = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($ocupaciones ?? [] as $Ocupaciones){
            array_push($datos, $Ocupaciones);
        }

        $cantidadOcupaciones = count($ocupaciones);
        $to = isset($ocupaciones) && $cantidadOcupaciones > 0 ? $ocupaciones->currentPage() * $ocupaciones->perPage() : null;
        $to = isset($to) && isset($ocupaciones) && $to > $ocupaciones->total() && $cantidadOcupaciones > 0 ? $ocupaciones->total() : $to;
        $from = isset($to) && isset($ocupaciones) && $cantidadOcupaciones > 0 ?
            ( $ocupaciones->perPage() > $to ? 1 : ($to - $cantidadOcupaciones) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($ocupaciones) && $cantidadOcupaciones > 0 ? +$ocupaciones->perPage() : 0,
            'pagina_actual' => isset($ocupaciones) && $cantidadOcupaciones > 0 ? $ocupaciones->currentPage() : 1,
            'ultima_pagina' => isset($ocupaciones) && $cantidadOcupaciones > 0 ? $ocupaciones->lastPage() : 0,
            'total' => isset($ocupaciones) && $cantidadOcupaciones > 0 ? $ocupaciones->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $ocupaciones = Ocupacion::find($id);
        return [
            'id' => $ocupaciones->id,
            'nombre' => $ocupaciones->ocupacionesDescripcion,
            'estado' => $ocupaciones->ocupacionesEstado,
            'usuario_creacion_id' => $ocupaciones->usuario_creacion_id,
            'usuario_creacion_nombre' => $ocupaciones->usuario_creacion_nombre,
            'usuario_modificacion_id' => $ocupaciones->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $ocupaciones->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($ocupaciones->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($ocupaciones->updated_at))->format("Y-m-d H:i:s")
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
        $ocupaciones = isset($dto['id']) ? Ocupacion::find($dto['id']) : new Ocupacion();

        // Guardar objeto original para auditoria
        $ocupacionesDescripcionOriginal = $ocupaciones->toJson();

        $ocupaciones->fill($dto);
        $guardado = $ocupaciones->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $ocupaciones);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $ocupaciones->id,
            'nombre_recurso' => Ocupacion::class,
            'descripcion_recurso' => $ocupaciones->ocupacionesDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $ocupacionesDescripcionOriginal : $ocupaciones->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $ocupaciones->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return Ocupacion::cargar($ocupaciones->id);
    }

    public static function eliminar($id)
    {
        $ocupaciones = Ocupacion::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $ocupaciones->id,
            'nombre_recurso' => Ocupacion::class,
            'descripcion_recurso' => $ocupaciones->ocupacionesDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $ocupaciones->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $ocupaciones->delete();
    }

    use HasFactory;    
}
