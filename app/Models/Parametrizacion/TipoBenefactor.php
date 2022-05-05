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

class TipoBenefactor extends Model
{
   protected $table = 'tipos_benefactor'; // nombre de la tabla en la base de datos

   protected $fillable = ['tipBenDescripcion',
                          'tipBenEstado',
                          'usuario_creacion_id',
                          'usuario_creacion_nombre',
                          'usuario_modificacion_id',
                          'usuario_modificacion_nombre',];

   public static function obtenerColeccionLigera($dto) {
       $query = DB::table('tipos_benefactor')->select('id',
                                                      'tipBenDescripcion AS nombre',
                                                      'tipBenEstado AS estado',);
       $query->orderBy('tipBenDescripcion', 'asc');
       return $query->get();
   }

   public static function obtenerColeccion($dto) {
       $query = DB::table('tipos_benefactor')->select('id',
                                                      'tipBenDescripcion As nombre',
                                                      'tipBenEstado As estado',
                                                      'usuario_creacion_id',
                                                      'usuario_creacion_nombre',
                                                      'usuario_modificacion_id',
                                                      'usuario_modificacion_nombre',
                                                      'created_at AS fecha_creacion',
                                                      'updated_at AS fecha_modificacion',);

       if (isset($dto['nombre']))
           $query->where('tipBenDescripcion', 'like', '%' . $dto['nombre'] . '%');

       if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0)
           foreach ($dto['ordenar_por'] as $attribute => $value){
               if($attribute == 'nombre')
                   $query->orderBy('tipos_benefactor.tipBenDescripcion', $value);
               if($attribute == 'estado')
                   $query->orderBy('tipos_benefactor.tipBenEstado', $value);
               if($attribute == 'usuario_creacion_nombre')
                   $query->orderBy('tipos_benefactor.usuario_creacion_nombre', $value);
               if($attribute == 'usuario_modificacion_nombre')
                   $query->orderBy('tipos_benefactor.usuario_modificacion_nombre', $value);
               if($attribute == 'fecha_creacion')
                   $query->orderBy('tipos_benefactor.created_at', $value);
               if($attribute == 'fecha_modificacion')
                   $query->orderBy('tipos_benefactor.updated_at', $value);
           }
       else {
           $query->orderBy("tipos_benefactor.updated_at", "desc");
       }

       $tipoBenefactorS = $query->paginate($dto['limite'] ?? 100);
       $datos = [];

       foreach ($tipoBenefactorS ?? [] as $tipoBenefactor)
           array_push($datos, $tipoBenefactor);

       $total_tipoBenefactorS = count($tipoBenefactorS);
       $to = isset($tipoBenefactorS) && $total_tipoBenefactorS > 0 ? $tipoBenefactorS->currentPage() * $tipoBenefactorS->perPage() : null;
       $to = isset($to) && isset($tipoBenefactorS) && $to > $tipoBenefactorS->total() && $total_tipoBenefactorS > 0 ? $tipoBenefactorS->total() : $to;
       $from = isset($to) && isset($tipoBenefactorS) && $total_tipoBenefactorS > 0 ? ( $tipoBenefactorS->perPage() > $to ? 1 : ($to - $total_tipoBenefactorS) + 1 ) : null;

       return ['datos' => $datos,
               'desde' => $from,
               'hasta' => $to,
               'por_pagina' => isset($tipoBenefactorS) && $total_tipoBenefactorS > 0 ? + $tipoBenefactorS->perPage() : 0,
               'pagina_actual' => isset($tipoBenefactorS) && $total_tipoBenefactorS > 0 ? $tipoBenefactorS->currentPage() : 1,
               'ultima_pagina' => isset($tipoBenefactorS) && $total_tipoBenefactorS > 0 ? $tipoBenefactorS->lastPage() : 0,
               'total' => isset($tipoBenefactorS) && $total_tipoBenefactorS > 0 ? $tipoBenefactorS->total() : 0];
   }

   public static function cargar($id)
   {
       $tipoBenefactor = TipoBenefactor::find($id);
       return ['id' => $tipoBenefactor->id,
               'nombre' => $tipoBenefactor->tipBenDescripcion,
               'estado' => $tipoBenefactor->tipBenEstado,
               'usuario_creacion_id' => $tipoBenefactor->usuario_creacion_id,
               'usuario_creacion_nombre' => $tipoBenefactor->usuario_creacion_nombre,
               'usuario_modificacion_id' => $tipoBenefactor->usuario_modificacion_id,
               'usuario_modificacion_nombre' => $tipoBenefactor->usuario_modificacion_nombre,
               'fecha_creacion' => (new Carbon($tipoBenefactor->created_at))->format("Y-m-d H:i:s"),
               'fecha_modificacion' => (new Carbon($tipoBenefactor->updated_at))->format("Y-m-d H:i:s")];
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
       $tipoBenefactor = isset($dto['id']) ? TipoBenefactor::find($dto['id']) : new TipoBenefactor();

       // Guardar objeto original para auditoria
       $tipoBenefactorOriginal = $tipoBenefactor->toJson();

       $tipoBenefactor->fill($dto);
       $guardado = $tipoBenefactor->save();
       if (!$guardado) {
           throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $tipoBenefactor);
       }

       // Guardar auditoria
       $auditoriaDto = ['id_recurso' => $tipoBenefactor->id,
                        'nombre_recurso' => TipoBenefactor::class,
                        'descripcion_recurso' => $tipoBenefactor->tipBenDescripcion,
                        'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
                        'recurso_original' => isset($dto['id']) ? $tipoBenefactorOriginal : $tipoBenefactor->toJson(),
                        'recurso_resultante' => isset($dto['id']) ? $tipoBenefactor->toJson() : null];

       AuditoriaTabla::crear($auditoriaDto);

       return TipoBenefactor::cargar($tipoBenefactor->id);
   }

   public static function eliminar($id)
   {
       $tipoBenefactor = TipoBenefactor::find($id);

       // Guardar auditoria
       $auditoriaDto = ['id_recurso' => $tipoBenefactor->id,
                        'nombre_recurso' => TipoBenefactor::class,
                        'descripcion_recurso' => $tipoBenefactor->tipBenDescripcion,
                        'accion' => AccionAuditoriaEnum::ELIMINAR,
                        'recurso_original' => $tipoBenefactor->toJson()];
       AuditoriaTabla::crear($auditoriaDto);

       return $tipoBenefactor->delete();
   }

   use HasFactory;
}
