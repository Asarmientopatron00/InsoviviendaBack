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

class Banco extends Model
{
   protected $table = 'bancos'; // nombre de la tabla en la base de datos

   protected $fillable = ['bancosDescripcion',
                          'bancosEstado',
                          'usuario_creacion_id',
                          'usuario_creacion_nombre',
                          'usuario_modificacion_id',
                          'usuario_modificacion_nombre',];

   public static function obtenerColeccionLigera($dto) {
       $query = DB::table('bancos')->select('id',
                                            'bancosDescripcion AS nombre',
                                            'bancosEstado AS estado',);
       $query->orderBy('bancosDescripcion', 'asc');
       return $query->get();
   }

   public static function obtenerColeccion($dto) {
       $query = DB::table('bancos')->select('id',
                                            'bancosDescripcion As nombre',
                                            'bancosEstado As estado',
                                            'usuario_creacion_id',
                                            'usuario_creacion_nombre',
                                            'usuario_modificacion_id',
                                            'usuario_modificacion_nombre',
                                            'created_at AS fecha_creacion',
                                            'updated_at AS fecha_modificacion',);

       if (isset($dto['nombre']))
           $query->where('bancosDescripcion', 'like', '%' . $dto['nombre'] . '%');

       if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0)
           foreach ($dto['ordenar_por'] as $attribute => $value){
               if($attribute == 'nombre')
                   $query->orderBy('bancos.bancosDescripcion', $value);
               if($attribute == 'estado')
                   $query->orderBy('bancos.bancosEstado', $value);
               if($attribute == 'usuario_creacion_nombre')
                   $query->orderBy('bancos.usuario_creacion_nombre', $value);
               if($attribute == 'usuario_modificacion_nombre')
                   $query->orderBy('bancos.usuario_modificacion_nombre', $value);
               if($attribute == 'fecha_creacion')
                   $query->orderBy('bancos.created_at', $value);
               if($attribute == 'fecha_modificacion')
                   $query->orderBy('bancos.updated_at', $value);
           }
       else {
           $query->orderBy("bancos.updated_at", "desc");
       }

       $bancoS = $query->paginate($dto['limite'] ?? 100);
       $datos = [];

       foreach ($bancoS ?? [] as $banco)
           array_push($datos, $banco);

       $total_bancoS = count($bancoS);
       $to = isset($bancoS) && $total_bancoS > 0 ? $bancoS->currentPage() * $bancoS->perPage() : null;
       $to = isset($to) && isset($bancoS) && $to > $bancoS->total() && $total_bancoS > 0 ? $bancoS->total() : $to;
       $from = isset($to) && isset($bancoS) && $total_bancoS > 0 ? ( $bancoS->perPage() > $to ? 1 : ($to - $total_bancoS) + 1 ) : null;

       return ['datos' => $datos,
               'desde' => $from,
               'hasta' => $to,
               'por_pagina' => isset($bancoS) && $total_bancoS > 0 ? +$bancoS->perPage() : 0,
               'pagina_actual' => isset($bancoS) && $total_bancoS > 0 ? $bancoS->currentPage() : 1,
               'ultima_pagina' => isset($bancoS) && $total_bancoS > 0 ? $bancoS->lastPage() : 0,
               'total' => isset($bancoS) && $total_bancoS > 0 ? $bancoS->total() : 0];
   }

   public static function cargar($id)
   {
       $banco = Banco::find($id);
       return ['id' => $banco->id,
               'nombre' => $banco->bancosDescripcion,
               'estado' => $banco->bancosEstado,
               'usuario_creacion_id' => $banco->usuario_creacion_id,
               'usuario_creacion_nombre' => $banco->usuario_creacion_nombre,
               'usuario_modificacion_id' => $banco->usuario_modificacion_id,
               'usuario_modificacion_nombre' => $banco->usuario_modificacion_nombre,
               'fecha_creacion' => (new Carbon($banco->created_at))->format("Y-m-d H:i:s"),
               'fecha_modificacion' => (new Carbon($banco->updated_at))->format("Y-m-d H:i:s")];
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
       $banco = isset($dto['id']) ? Banco::find($dto['id']) : new Banco();

       // Guardar objeto original para auditoria
       $bancoOriginal = $banco->toJson();

       $banco->fill($dto);
       $guardado = $banco->save();
       if (!$guardado) {
           throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $banco);
       }

       // Guardar auditoria
       $auditoriaDto = ['id_recurso' => $banco->id,
                        'nombre_recurso' => Banco::class,
                        'descripcion_recurso' => $banco->bancosDescripcion,
                        'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
                        'recurso_original' => isset($dto['id']) ? $bancoOriginal : $banco->toJson(),
                        'recurso_resultante' => isset($dto['id']) ? $banco->toJson() : null];

       AuditoriaTabla::crear($auditoriaDto);

       return Banco::cargar($banco->id);
   }

   public static function eliminar($id)
   {
       $banco = Banco::find($id);

       // Guardar auditoria
       $auditoriaDto = ['id_recurso' => $banco->id,
                        'nombre_recurso' => Banco::class,
                        'descripcion_recurso' => $banco->bancosDescripcion,
                        'accion' => AccionAuditoriaEnum::ELIMINAR,
                        'recurso_original' => $banco->toJson()];
       AuditoriaTabla::crear($auditoriaDto);

       return $banco->delete();
   }

   use HasFactory;
}
