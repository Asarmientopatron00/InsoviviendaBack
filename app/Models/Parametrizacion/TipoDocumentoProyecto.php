<?php

namespace App\Models\Parametrizacion;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enum\AccionAuditoriaEnum;
use App\Models\Seguridad\AuditoriaTabla;

class TipoDocumentoProyecto extends Model
{
    protected $table = 'tipos_documentos_proyecto'; // nombre de la tabla en la base de datos

    protected $fillable = ['tiDoPrDescripcion',
                           'tiDoPrEtapa',
                           'tiDoPrRequerido',
                           'tiDoPrEstado',
                           'usuario_creacion_id',
                           'usuario_creacion_nombre',
                           'usuario_modificacion_id',
                           'usuario_modificacion_nombre',];
 
    public static function obtenerColeccionLigera($dto) {
        $query = DB::table('tipos_documentos_proyecto')->select('id',
                                                               'tiDoPrDescripcion AS nombre',
                                                               'tiDoPrEtapa AS etapa',
                                                               'tiDoPrRequerido AS requerido',
                                                               'tiDoPrEstado AS estado',);
        $query->orderBy('tiDoPrDescripcion', 'asc');
        return $query->get();
    }
 
    public static function obtenerColeccion($dto) {
        $query = DB::table('tipos_documentos_proyecto')->select('id',
                                                                'tiDoPrDescripcion As nombre',
                                                                'tiDoPrEtapa AS etapa',
                                                                'tiDoPrRequerido AS requerido',
                                                                'tiDoPrEstado As estado',
                                                                'usuario_creacion_id',
                                                                'usuario_creacion_nombre',
                                                                'usuario_modificacion_id',
                                                                'usuario_modificacion_nombre',
                                                                'created_at AS fecha_creacion',
                                                                'updated_at AS fecha_modificacion',);
 
        if (isset($dto['nombre']))
            $query->where('tiDoPrDescripcion', 'like', '%' . $dto['nombre'] . '%');
 
        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0)
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre')
                    $query->orderBy('tipos_documentos_proyecto.tiDoPrDescripcion', $value);
                if($attribute == 'etapa')
                    $query->orderBy('tipos_documentos_proyecto.tiDoPrEtapa', $value);
                if($attribute == 'requerido')
                    $query->orderBy('tipos_documentos_proyecto.tiDoPrRequerido', $value);
                if($attribute == 'estado')
                    $query->orderBy('tipos_documentos_proyecto.tiDoPrEstado', $value);
                if($attribute == 'usuario_creacion_nombre')
                    $query->orderBy('tipos_documentos_proyecto.usuario_creacion_nombre', $value);
                if($attribute == 'usuario_modificacion_nombre')
                    $query->orderBy('tipos_documentos_proyecto.usuario_modificacion_nombre', $value);
                if($attribute == 'fecha_creacion')
                    $query->orderBy('tipos_documentos_proyecto.created_at', $value);
                if($attribute == 'fecha_modificacion')
                    $query->orderBy('tipos_documentos_proyecto.updated_at', $value);
            }
        else {
            $query->orderBy("tipos_documentos_proyecto.updated_at", "desc");
        }
 
        $tipoDocumentoProyectoS = $query->paginate($dto['limite'] ?? 100);
        $datos = [];
 
        foreach ($tipoDocumentoProyectoS ?? [] as $tipoDocumentoProyecto)
            array_push($datos, $tipoDocumentoProyecto);
 
        $total_tipoDocumentoProyectoS = count($tipoDocumentoProyectoS);
        $to = isset($tipoDocumentoProyectoS) && $total_tipoDocumentoProyectoS > 0 ? $tipoDocumentoProyectoS->currentPage() * $tipoDocumentoProyectoS->perPage() : null;
        $to = isset($to) && isset($tipoDocumentoProyectoS) && $to > $tipoDocumentoProyectoS->total() && $total_tipoDocumentoProyectoS > 0 ? $tipoDocumentoProyectoS->total() : $to;
        $from = isset($to) && isset($tipoDocumentoProyectoS) && $total_tipoDocumentoProyectoS > 0 ? ( $tipoDocumentoProyectoS->perPage() > $to ? 1 : ($to - $total_tipoDocumentoProyectoS) + 1 ) : null;
 
        return ['datos' => $datos,
                'desde' => $from,
                'hasta' => $to,
                'por_pagina' => isset($tipoDocumentoProyectoS) && $total_tipoDocumentoProyectoS > 0 ? + $tipoDocumentoProyectoS->perPage() : 0,
                'pagina_actual' => isset($tipoDocumentoProyectoS) && $total_tipoDocumentoProyectoS > 0 ? $tipoDocumentoProyectoS->currentPage() : 1,
                'ultima_pagina' => isset($tipoDocumentoProyectoS) && $total_tipoDocumentoProyectoS > 0 ? $tipoDocumentoProyectoS->lastPage() : 0,
                'total' => isset($tipoDocumentoProyectoS) && $total_tipoDocumentoProyectoS > 0 ? $tipoDocumentoProyectoS->total() : 0];
    }
 
    public static function cargar($id)
    {
        $tipoDocumentoProyecto = TipoDocumentoProyecto::find($id);
        return ['id' => $tipoDocumentoProyecto->id,
                'nombre' => $tipoDocumentoProyecto->tiDoPrDescripcion,
                'etapa' => $tipoDocumentoProyecto->tiDoPrEtapa,
                'requerido' => $tipoDocumentoProyecto->tiDoPrRequerido,
                'estado' => $tipoDocumentoProyecto->tiDoPrEstado,
                'usuario_creacion_id' => $tipoDocumentoProyecto->usuario_creacion_id,
                'usuario_creacion_nombre' => $tipoDocumentoProyecto->usuario_creacion_nombre,
                'usuario_modificacion_id' => $tipoDocumentoProyecto->usuario_modificacion_id,
                'usuario_modificacion_nombre' => $tipoDocumentoProyecto->usuario_modificacion_nombre,
                'fecha_creacion' => (new Carbon($tipoDocumentoProyecto->created_at))->format("Y-m-d H:i:s"),
                'fecha_modificacion' => (new Carbon($tipoDocumentoProyecto->updated_at))->format("Y-m-d H:i:s")];
    }
 
    public static function modificarOCrear($dto)
    {
        $user = Auth::user();
        $usuario = $user->usuario();
 
        if (!isset($dto['id'])) {
            $dto['usuario_creacion_id'] = $usuario->id ?? ($dto['usuario_creacion_id'] ?? null);
            $dto['usuario_creacion_nombre'] = $usuario->nombre ?? ($dto['usuario_creacion_nombre'] ?? null);
        }
        if (isset($usuario) || isset($dto['usuario_modificacion_id'])) {
            $dto['usuario_modificacion_id'] = $usuario->id ?? ($dto['usuario_modificacion_id'] ?? null);
            $dto['usuario_modificacion_nombre'] = $usuario->nombre ?? ($dto['usuario_modificacion_nombre'] ?? null);
        }
 
        // Consultar aplicación
        $tipoDocumentoProyecto = isset($dto['id']) ? TipoDocumentoProyecto::find($dto['id']) : new TipoDocumentoProyecto();
 
        // Guardar objeto original para auditoria
        $tipoDocumentoProyectoOriginal = $tipoDocumentoProyecto->toJson();
 
        $tipoDocumentoProyecto->fill($dto);
        $guardado = $tipoDocumentoProyecto->save();
        if (!$guardado) {
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoDocumentoProyecto);
        }
 
        // Guardar auditoria
        $auditoriaDto = ['id_recurso' => $tipoDocumentoProyecto->id,
                         'nombre_recurso' => TipoDocumentoProyecto::class,
                         'descripcion_recurso' => $tipoDocumentoProyecto->tiDoPrDescripcion,
                         'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
                         'recurso_original' => isset($dto['id']) ? $tipoDocumentoProyectoOriginal : $tipoDocumentoProyecto->toJson(),
                         'recurso_resultante' => isset($dto['id']) ? $tipoDocumentoProyecto->toJson() : null];
 
        AuditoriaTabla::crear($auditoriaDto);
 
        return TipoDocumentoProyecto::cargar($tipoDocumentoProyecto->id);
    }
 
    public static function eliminar($id)
    {
        $tipoDocumentoProyecto = TipoDocumentoProyecto::find($id);
 
        // Guardar auditoria
        $auditoriaDto = ['id_recurso' => $tipoDocumentoProyecto->id,
                         'nombre_recurso' => TipoDocumentoProyecto::class,
                         'descripcion_recurso' => $tipoDocumentoProyecto->tiDoPrDescripcion,
                         'accion' => AccionAuditoriaEnum::ELIMINAR,
                         'recurso_original' => $tipoDocumentoProyecto->toJson()];
        AuditoriaTabla::crear($auditoriaDto);
 
        return $tipoDocumentoProyecto->delete();
    }
 
    use HasFactory;
 }
