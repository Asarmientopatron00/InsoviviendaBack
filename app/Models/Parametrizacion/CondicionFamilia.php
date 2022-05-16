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

class CondicionFamilia extends Model
{
    protected $table = 'condiciones_familia'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'conFamDescripcion',
        'conFamEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('condiciones_familia')
            ->select(
                'id',
                'conFamDescripcion AS nombre',
                'conFamEstado AS estado',
            );
        $query->orderBy('conFamDescripcion', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('condiciones_familia')
            ->select(
                'id',
                'conFamDescripcion As nombre',
                'conFamEstado As estado',
                'usuario_creacion_id',
                'usuario_creacion_nombre',
                'usuario_modificacion_id',
                'usuario_modificacion_nombre',
                'created_at AS fecha_creacion',
                'updated_at AS fecha_modificacion',
            );

        if(isset($dto['nombre'])){
            $query->where('conFamDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('conFamDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('conFamEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('updated_at', $value);
                }
            }
        }else{
            $query->orderBy("updated_at", "desc");
        }

        $condicionesFamilia = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($condicionesFamilia ?? [] as $condicionFamilia){
            array_push($datos, $condicionFamilia);
        }

        $cantidadCondicionesFamilia = count($condicionesFamilia);
        $to = isset($condicionesFamilia) && $cantidadCondicionesFamilia > 0 ? $condicionesFamilia->currentPage() * $condicionesFamilia->perPage() : null;
        $to = isset($to) && isset($condicionesFamilia) && $to > $condicionesFamilia->total() && $cantidadCondicionesFamilia > 0 ? $condicionesFamilia->total() : $to;
        $from = isset($to) && isset($condicionesFamilia) && $cantidadCondicionesFamilia > 0 ?
            ( $condicionesFamilia->perPage() > $to ? 1 : ($to - $cantidadCondicionesFamilia) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($condicionesFamilia) && $cantidadCondicionesFamilia > 0 ? +$condicionesFamilia->perPage() : 0,
            'pagina_actual' => isset($condicionesFamilia) && $cantidadCondicionesFamilia > 0 ? $condicionesFamilia->currentPage() : 1,
            'ultima_pagina' => isset($condicionesFamilia) && $cantidadCondicionesFamilia > 0 ? $condicionesFamilia->lastPage() : 0,
            'total' => isset($condicionesFamilia) && $cantidadCondicionesFamilia > 0 ? $condicionesFamilia->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $condicionFamilia = CondicionFamilia::find($id);
        return [
            'id' => $condicionFamilia->id,
            'nombre' => $condicionFamilia->conFamDescripcion,
            'estado' => $condicionFamilia->conFamEstado,
            'usuario_creacion_id' => $condicionFamilia->usuario_creacion_id,
            'usuario_creacion_nombre' => $condicionFamilia->usuario_creacion_nombre,
            'usuario_modificacion_id' => $condicionFamilia->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $condicionFamilia->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($condicionFamilia->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($condicionFamilia->updated_at))->format("Y-m-d H:i:s")
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
        $condicionFamilia = isset($dto['id']) ? CondicionFamilia::find($dto['id']) : new CondicionFamilia();

        // Guardar objeto original para auditoria
        $condicionFamiliaOriginal = $condicionFamilia->toJson();

        $condicionFamilia->fill($dto);
        $guardado = $condicionFamilia->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $condicionFamilia);
        }


        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $condicionFamilia->id,
            'nombre_recurso' => CondicionFamilia::class,
            'descripcion_recurso' => $condicionFamilia->conFamDescripcion,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $condicionFamiliaOriginal : $condicionFamilia->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $condicionFamilia->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return CondicionFamilia::cargar($condicionFamilia->id);
    }

    public static function eliminar($id)
    {
        $condicionFamilia = CondicionFamilia::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $condicionFamilia->id,
            'nombre_recurso' => CondicionFamilia::class,
            'descripcion_recurso' => $condicionFamilia->conFamDescripcion,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $condicionFamilia->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $condicionFamilia->delete();
    }

    use HasFactory;
}
