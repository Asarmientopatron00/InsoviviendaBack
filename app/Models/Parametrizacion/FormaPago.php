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

class FormaPago extends Model
{
   protected $table = 'formas_pago'; // nombre de la tabla en la base de datos

   protected $fillable = ['forPagDescripcion',
                          'forPagEstado',
                          'usuario_creacion_id',
                          'usuario_creacion_nombre',
                          'usuario_modificacion_id',
                          'usuario_modificacion_nombre',];

   public static function obtenerColeccionLigera($dto) {
       $query = DB::table('formas_pago')->select('id',
                                                 'forPagDescripcion AS nombre',
                                                 'forPagEstado AS estado',);
       $query->orderBy('forPagDescripcion', 'asc');
       return $query->get();
   }

   public static function obtenerColeccion($dto) {
       $query = DB::table('formas_pago')->select('id',
                                                 'forPagDescripcion As nombre',
                                                 'forPagEstado As estado',
                                                 'usuario_creacion_id',
                                                 'usuario_creacion_nombre',
                                                 'usuario_modificacion_id',
                                                 'usuario_modificacion_nombre',
                                                 'created_at AS fecha_creacion',
                                                 'updated_at AS fecha_modificacion',);

       if (isset($dto['nombre']))
           $query->where('forPagDescripcion', 'like', '%' . $dto['nombre'] . '%');

       if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0)
           foreach ($dto['ordenar_por'] as $attribute => $value){
               if($attribute == 'nombre')
                   $query->orderBy('formas_pago.forPagDescripcion', $value);
               if($attribute == 'estado')
                   $query->orderBy('formas_pago.forPagEstado', $value);
               if($attribute == 'usuario_creacion_nombre')
                   $query->orderBy('formas_pago.usuario_creacion_nombre', $value);
               if($attribute == 'usuario_modificacion_nombre')
                   $query->orderBy('formas_pago.usuario_modificacion_nombre', $value);
               if($attribute == 'fecha_creacion')
                   $query->orderBy('formas_pago.created_at', $value);
               if($attribute == 'fecha_modificacion')
                   $query->orderBy('formas_pago.updated_at', $value);
           }
       else {
           $query->orderBy("formas_pago.updated_at", "desc");
       }

       $formaPagoS = $query->paginate($dto['limite'] ?? 100);
       $datos = [];

       foreach ($formaPagoS ?? [] as $formaPago)
           array_push($datos, $formaPago);

       $total_formaPagoS = count($formaPagoS);
       $to = isset($formaPagoS) && $total_formaPagoS > 0 ? $formaPagoS->currentPage() * $formaPagoS->perPage() : null;
       $to = isset($to) && isset($formaPagoS) && $to > $formaPagoS->total() && $total_formaPagoS > 0 ? $formaPagoS->total() : $to;
       $from = isset($to) && isset($formaPagoS) && $total_formaPagoS > 0 ? ( $formaPagoS->perPage() > $to ? 1 : ($to - $total_formaPagoS) + 1 ) : null;

       return ['datos' => $datos,
               'desde' => $from,
               'hasta' => $to,
               'por_pagina' => isset($formaPagoS) && $total_formaPagoS > 0 ? + $formaPagoS->perPage() : 0,
               'pagina_actual' => isset($formaPagoS) && $total_formaPagoS > 0 ? $formaPagoS->currentPage() : 1,
               'ultima_pagina' => isset($formaPagoS) && $total_formaPagoS > 0 ? $formaPagoS->lastPage() : 0,
               'total' => isset($formaPagoS) && $total_formaPagoS > 0 ? $formaPagoS->total() : 0];
   }

   public static function cargar($id)
   {
       $formaPago = FormaPago::find($id);
       return ['id' => $formaPago->id,
               'nombre' => $formaPago->forPagDescripcion,
               'estado' => $formaPago->forPagEstado,
               'usuario_creacion_id' => $formaPago->usuario_creacion_id,
               'usuario_creacion_nombre' => $formaPago->usuario_creacion_nombre,
               'usuario_modificacion_id' => $formaPago->usuario_modificacion_id,
               'usuario_modificacion_nombre' => $formaPago->usuario_modificacion_nombre,
               'fecha_creacion' => (new Carbon($formaPago->created_at))->format("Y-m-d H:i:s"),
               'fecha_modificacion' => (new Carbon($formaPago->updated_at))->format("Y-m-d H:i:s")];
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
       $formaPago = isset($dto['id']) ? FormaPago::find($dto['id']) : new FormaPago();

       // Guardar objeto original para auditoria
       $formaPagoOriginal = $formaPago->toJson();

       $formaPago->fill($dto);
       $guardado = $formaPago->save();
       if (!$guardado) {
           throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $formaPago);
       }

       // Guardar auditoria
       $auditoriaDto = ['id_recurso' => $formaPago->id,
                        'nombre_recurso' => FormaPago::class,
                        'descripcion_recurso' => $formaPago->forPagDescripcion,
                        'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
                        'recurso_original' => isset($dto['id']) ? $formaPagoOriginal : $formaPago->toJson(),
                        'recurso_resultante' => isset($dto['id']) ? $formaPago->toJson() : null];

       AuditoriaTabla::crear($auditoriaDto);

       return FormaPago::cargar($formaPago->id);
   }

   public static function eliminar($id)
   {
       $formaPago = FormaPago::find($id);

       // Guardar auditoria
       $auditoriaDto = ['id_recurso' => $formaPago->id,
                        'nombre_recurso' => FormaPago::class,
                        'descripcion_recurso' => $formaPago->forPagDescripcion,
                        'accion' => AccionAuditoriaEnum::ELIMINAR,
                        'recurso_original' => $formaPago->toJson()];
       AuditoriaTabla::crear($auditoriaDto);

       return $formaPago->delete();
   }

   use HasFactory;
}
