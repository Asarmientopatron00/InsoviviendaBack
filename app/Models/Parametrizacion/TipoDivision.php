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

class TipoDivision extends Model
{
    protected $table = 'tipos_division'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipDivDescripcion',
        'tipDivEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('tipos_division')
            ->select(
                'id',
                'tipDivDescripcion AS nombre',
                'tipDivEstado AS estado',
            );
        $query->orderBy('tipDivDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('tipos_division')
            ->select(
                'id',
                'tipDivDescripcion As nombre',
                'tipDivEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('tipDivDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_division.tipDivDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('tipos_division.tipDivEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('tipos_division.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('tipos_division.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('tipos_division.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('tipos_division.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("tipos_division.updated_at", "desc");
        }

        $tiposDivision = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($tiposDivision ?? [] as $tipoDivision){
            array_push($datos, $tipoDivision);
        }

        $cantidadTiposDivision = count($tiposDivision);
        $to = isset($tiposDivision) && $cantidadTiposDivision > 0 ? $tiposDivision->currentPage() * $tiposDivision->perPage() : null;
        $to = isset($to) && isset($tiposDivision) && $to > $tiposDivision->total() && $cantidadTiposDivision > 0 ? $tiposDivision->total() : $to;
        $from = isset($to) && isset($tiposDivision) && $cantidadTiposDivision > 0 ?
            ( $tiposDivision->perPage() > $to ? 1 : ($to - $cantidadTiposDivision) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($tiposDivision) && $cantidadTiposDivision > 0 ? +$tiposDivision->perPage() : 0,
            'pagina_actual' => isset($tiposDivision) && $cantidadTiposDivision > 0 ? $tiposDivision->currentPage() : 1,
            'ultima_pagina' => isset($tiposDivision) && $cantidadTiposDivision > 0 ? $tiposDivision->lastPage() : 0,
            'total' => isset($tiposDivision) && $cantidadTiposDivision > 0 ? $tiposDivision->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $tipoDivision = TipoDivision::find($id);
        return [
            'id' => $tipoDivision->id,
            'nombre' => $tipoDivision->tipDivDescripcion,
            'estado' => $tipoDivision->tipDivEstado,
            'usuario_creacion_id' => $tipoDivision->usuario_creacion_id,
            'usuario_creacion_nombre' => $tipoDivision->usuario_creacion_nombre,
            'usuario_modificacion_id' => $tipoDivision->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $tipoDivision->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($tipoDivision->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($tipoDivision->updated_at))->format("Y-m-d H:i:s")
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
        $tipoDivision = isset($dto['id']) ? TipoDivision::find($dto['id']) : new TipoDivision();

        // Guardar objeto original para auditoria
        $tipoDivisionOriginal = $tipoDivision->toJson();

        $tipoDivision->fill($dto);
        $guardado = $tipoDivision->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoDivision);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoDivision->id,
            'nombre_recurso' => TipoDivision::class,
            'descripcion_recurso' => $tipoDivision->tipDivDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $tipoDivisionOriginal : $tipoDivision->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $tipoDivision->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return TipoDivision::cargar($tipoDivision->id);
    }

    public static function eliminar($id)
    {
        $tipoDivision = TipoDivision::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $tipoDivision->id,
            'nombre_recurso' => TipoDivision::class,
            'descripcion_recurso' => $tipoDivision->tipDivDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $tipoDivision->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $tipoDivision->delete();
    }    
    use HasFactory;
}
