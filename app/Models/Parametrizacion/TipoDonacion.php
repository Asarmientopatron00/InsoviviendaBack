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

class TipoDonacion extends Model
{
   protected $table = 'tipos_donacion'; // nombre de la tabla en la base de datos

   protected $fillable = ['tipDonDescripcion',
                          'tipDonEstado',
                          'usuario_creacion_id',
                          'usuario_creacion_nombre',
                          'usuario_modificacion_id',
                          'usuario_modificacion_nombre',];

   public static function obtenerColeccionLigera($dto) {
       $query = DB::table('tipos_donacion')->select('id',
                                                    'tipDonDescripcion AS nombre',
                                                    'tipDonEstado AS estado',);
       $query->orderBy('tipDonDescripcion', 'asc');
       return $query->get();
   }

   public static function obtenerColeccion($dto) {
       $query = DB::table('tipos_donacion')->select('id',
                                                    'tipDonDescripcion As nombre',
                                                    'tipDonEstado As estado',
                                                    'usuario_creacion_id',
                                                    'usuario_creacion_nombre',
                                                    'usuario_modificacion_id',
                                                    'usuario_modificacion_nombre',
                                                    'created_at AS fecha_creacion',
                                                    'updated_at AS fecha_modificacion',);

       if (isset($dto['nombre']))
           $query->where('tipDonDescripcion', 'like', '%' . $dto['nombre'] . '%');

       if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0)
           foreach ($dto['ordenar_por'] as $attribute => $value){
               if($attribute == 'nombre')
                   $query->orderBy('tipos_donacion.tipDonDescripcion', $value);
               if($attribute == 'estado')
                   $query->orderBy('tipos_donacion.tipDonEstado', $value);
               if($attribute == 'usuario_creacion_nombre')
                   $query->orderBy('tipos_donacion.usuario_creacion_nombre', $value);
               if($attribute == 'usuario_modificacion_nombre')
                   $query->orderBy('tipos_donacion.usuario_modificacion_nombre', $value);
               if($attribute == 'fecha_creacion')
                   $query->orderBy('tipos_donacion.created_at', $value);
               if($attribute == 'fecha_modificacion')
                   $query->orderBy('tipos_donacion.updated_at', $value);
           }
       else {
           $query->orderBy("tipos_donacion.updated_at", "desc");
       }

       $tipoDonacionS = $query->paginate($dto['limite'] ?? 100);
       $datos = [];

       foreach ($tipoDonacionS ?? [] as $tipoDonacion)
           array_push($datos, $tipoDonacion);

       $total_tipoDonacionS = count($tipoDonacionS);
       $to = isset($tipoDonacionS) && $total_tipoDonacionS > 0 ? $tipoDonacionS->currentPage() * $tipoDonacionS->perPage() : null;
       $to = isset($to) && isset($tipoDonacionS) && $to > $tipoDonacionS->total() && $total_tipoDonacionS > 0 ? $tipoDonacionS->total() : $to;
       $from = isset($to) && isset($tipoDonacionS) && $total_tipoDonacionS > 0 ? ( $tipoDonacionS->perPage() > $to ? 1 : ($to - $total_tipoDonacionS) + 1 ) : null;

       return ['datos' => $datos,
               'desde' => $from,
               'hasta' => $to,
               'por_pagina' => isset($tipoDonacionS) && $total_tipoDonacionS > 0 ? + $tipoDonacionS->perPage() : 0,
               'pagina_actual' => isset($tipoDonacionS) && $total_tipoDonacionS > 0 ? $tipoDonacionS->currentPage() : 1,
               'ultima_pagina' => isset($tipoDonacionS) && $total_tipoDonacionS > 0 ? $tipoDonacionS->lastPage() : 0,
               'total' => isset($tipoDonacionS) && $total_tipoDonacionS > 0 ? $tipoDonacionS->total() : 0];
   }

   public static function cargar($id)
   {
       $tipoDonacion = TipoDonacion::find($id);
       return ['id' => $tipoDonacion->id,
               'nombre' => $tipoDonacion->tipDonDescripcion,
               'estado' => $tipoDonacion->tipDonEstado,
               'usuario_creacion_id' => $tipoDonacion->usuario_creacion_id,
               'usuario_creacion_nombre' => $tipoDonacion->usuario_creacion_nombre,
               'usuario_modificacion_id' => $tipoDonacion->usuario_modificacion_id,
               'usuario_modificacion_nombre' => $tipoDonacion->usuario_modificacion_nombre,
               'fecha_creacion' => (new Carbon($tipoDonacion->created_at))->format("Y-m-d H:i:s"),
               'fecha_modificacion' => (new Carbon($tipoDonacion->updated_at))->format("Y-m-d H:i:s")];
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

       // Consultar aplicaci�n
       $tipoDonacion = isset($dto['id']) ? TipoDonacion::find($dto['id']) : new TipoDonacion();

       // Guardar objeto original para auditoria
       $tipoDonacionOriginal = $tipoDonacion->toJson();

       $tipoDonacion->fill($dto);
       $guardado = $tipoDonacion->save();
       if (!$guardado) {
           throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoDonacion);
       }

       // Guardar auditoria
       $auditoriaDto = ['id_recurso' => $tipoDonacion->id,
                        'nombre_recurso' => TipoDonacion::class,
                        'descripcion_recurso' => $tipoDonacion->tipDonDescripcion,
                        'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
                        'recurso_original' => isset($dto['id']) ? $tipoDonacionOriginal : $tipoDonacion->toJson(),
                        'recurso_resultante' => isset($dto['id']) ? $tipoDonacion->toJson() : null];

       AuditoriaTabla::crear($auditoriaDto);

       return TipoDonacion::cargar($tipoDonacion->id);
   }

   public static function eliminar($id)
   {
       $tipoDonacion = TipoDonacion::find($id);

       // Guardar auditoria
       $auditoriaDto = ['id_recurso' => $tipoDonacion->id,
                        'nombre_recurso' => TipoDonacion::class,
                        'descripcion_recurso' => $tipoDonacion->tipDonDescripcion,
                        'accion' => AccionAuditoriaEnum::ELIMINAR,
                        'recurso_original' => $tipoDonacion->toJson()];
       AuditoriaTabla::crear($auditoriaDto);

       return $tipoDonacion->delete();
   }

   use HasFactory;
}
