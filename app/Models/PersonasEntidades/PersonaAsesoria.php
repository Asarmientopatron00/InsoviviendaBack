<?php

namespace App\Models\PersonasEntidades;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enum\AccionAuditoriaEnum;
use App\Models\Seguridad\AuditoriaTabla;

class PersonaAsesoria extends Model
{
   protected $table = 'personas_asesorias'; // nombre de la tabla en la base de datos

   protected $fillable = [ 
      'tipo_identificacion_id',
      'numero_documento',
      'nombre',
      'telefono',
      'celular',
      'direccion',
      'departamento_id',
      'ciudad_id',
      'observaciones',
      'estado',
      'usuario_creacion_id',
      'usuario_creacion_nombre',
      'usuario_modificacion_id',
      'usuario_modificacion_nombre', 
   ];
 
   public static function obtenerColeccionLigera($dto) 
   {
      $query = DB::table('personas_asesorias') 
         -> select(  
            'personas_asesorias.id',
            'personas_asesorias.numero_documento',
            'personas_asesorias.nombre', 
            'personas_asesorias.estado', 
         );
      $query -> orderBy('nombre', 'asc');
      return $query -> get();
   }
 
   public static function obtenerColeccion($dto) 
   {
      $query = DB::table('personas_asesorias') 
         ->join('tipos_identificacion', 'tipos_identificacion.id', '=', 'personas_asesorias.tipo_identificacion_id')
         ->leftJoin('departamentos', 'departamentos.id', '=','personas_asesorias.departamento_id')
         ->leftJoin('ciudades', 'ciudades.id', '=', 'personas_asesorias.ciudad_id')
         ->select(  
            'personas_asesorias.id',
            'tipos_identificacion.tipIdeDescripcion',
            'personas_asesorias.numero_documento',
            'personas_asesorias.nombre', 
            'personas_asesorias.telefono',
            'personas_asesorias.celular',
            'personas_asesorias.direccion',
            DB::Raw("IFNULL(departamentos.departamentosDescripcion, '') AS departamentosDescripcion"),
            DB::Raw("IFNULL(ciudades.ciudadesDescripcion, '') AS ciudadesDescripcion"),
            'personas_asesorias.observaciones',
            'personas_asesorias.estado',
            'personas_asesorias.usuario_creacion_id',
            'personas_asesorias.usuario_creacion_nombre',
            'personas_asesorias.usuario_modificacion_id',
            'personas_asesorias.usuario_modificacion_nombre',
            'personas_asesorias.created_at AS fecha_creacion',
            'personas_asesorias.updated_at AS fecha_modificacion', 
            );
      
      // Filtro por nombre
      if (isset($dto['nombre']))
         $query->where('personas_asesorias.nombre', 'like', '%' . $dto['nombre'] . '%');

      if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0)
         foreach ($dto['ordenar_por'] as $attribute => $value) {
            if ($attribute == 'tipIdeDescripcion') 
               $query->orderBy('tipos_identificacion.tipIdeDescripcion', $value); 
    
            if ($attribute == 'numero_documento') 
               $query->orderBy('personas_asesorias.numero_documento', $value); 
    
            if ($attribute == 'nombre')  
               $query->orderBy('personas_asesorias.nombre', $value); 
            
            if ($attribute == 'telefono')  
               $query->orderBy('personas_asesorias.telefono', $value); 
            
            if ($attribute == 'celular')  
               $query->orderBy('personas_asesorias.celular', $value); 
            
            if ($attribute == 'direccion')  
               $query->orderBy('personas_asesorias.direccion', $value); 
            
            if ($attribute == 'departamentosDescripcion')  
               $query->orderBy('departamentos.departamentosDescripcion', $value); 
         
            if ($attribute == 'ciudadesDescripcion')  
               $query->orderBy('ciudades.ciudadesDescripcion', $value); 
         
            if ($attribute == 'observaciones')  
               $query->orderBy('personas_asesorias.observaciones', $value); 
         
            if ($attribute == 'estado')  
               $query->orderBy('personas_asesorias.estado', $value); 
         
            if ($attribute == 'usuario_creacion_nombre')
               $query->orderBy('personas_asesorias.usuario_creacion_nombre', $value);
         
            if ($attribute == 'usuario_modificacion_nombre')
               $query->orderBy('personas_asesorias.usuario_modificacion_nombre', $value);
         
            if ($attribute == 'fecha_creacion')
               $query->orderBy('personas_asesorias.created_at', $value);
         
            if ($attribute == 'fecha_modificacion')
               $query->orderBy('personas_asesorias.updated_at', $value);
         }
      else 
         $query->orderBy("personas_asesorias.updated_at", "desc");
 
      $pag = $query->paginate($dto['limite'] ?? 100);
      $datos = [];
 
      foreach ($pag ?? [] as $pagTmp)
         array_push($datos, $pagTmp);
 
      $totReg = count($pag);
      $to = isset($pag) && $totReg > 0 ? $pag->currentPage() * $pag->perPage() : null;
      $to = isset($to) && isset($pag) && $to > $pag->total() && $totReg > 0 ? $pag->total() : $to;
      $from = isset($to) && isset($pag) && $totReg > 0 ? ( $pag->perPage() > $to ? 1 : ($to - $totReg) + 1 ) : null;
 
      return [ 
         'datos' => $datos,
         'desde' => $from,
         'hasta' => $to,
         'por_pagina' => isset($pag) && $totReg > 0 ? + $pag->perPage() : 0,
         'pagina_actual' => isset($pag) && $totReg > 0 ? $pag->currentPage() : 1,
         'ultima_pagina' => isset($pag) && $totReg > 0 ? $pag->lastPage() : 0,
         'total' => isset($pag) && $totReg > 0 ? $pag->total() : 0 
      ];
   }
 
   public static function cargar($id)
   {
      $regCargar = PersonaAsesoria::find($id);
      return [ 
         'id' => $regCargar->id,
         'tipo_identificacion_id' => $regCargar->tipo_identificacion_id, 
         'numero_documento' => $regCargar->numero_documento, 
         'nombre' => $regCargar->nombre, 
         'telefono' => $regCargar->telefono, 
         'celular' => $regCargar->celular, 
         'direccion' => $regCargar->direccion, 
         'departamento_id' => $regCargar->departamento_id, 
         'ciudad_id' => $regCargar->ciudad_id, 
         'observaciones' => $regCargar->observaciones, 
         'estado' => $regCargar->estado, 
         'usuario_creacion_id' => $regCargar->usuario_creacion_id,
         'usuario_creacion_nombre' => $regCargar->usuario_creacion_nombre,
         'usuario_modificacion_id' => $regCargar->usuario_modificacion_id,
         'usuario_modificacion_nombre' => $regCargar->usuario_modificacion_nombre,
         'fecha_creacion' => (new Carbon($regCargar->created_at))->format("Y-m-d H:i:s"),
         'fecha_modificacion' => (new Carbon($regCargar->updated_at))->format("Y-m-d H:i:s") 
      ];
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
      $reg = isset($dto['id']) ? PersonaAsesoria::find($dto['id']) : new PersonaAsesoria();
  
      // Guardar objeto original para auditoria
      $regOri = $reg->toJson();
  
      $reg->fill($dto);
      $guardado = $reg->save();
      if (!$guardado) 
         throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $reg);
  
      // Guardar auditoria
      $auditoriaDto = [ 
         'id_recurso' => $reg->id,
         'nombre_recurso' => PersonaAsesoria::class,
         'descripcion_recurso' => $reg->numero_documento,
         'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
         'recurso_original' => isset($dto['id']) ? $regOri : $reg->toJson(),
         'recurso_resultante' => isset($dto['id']) ? $reg->toJson() : null 
      ];
  
      AuditoriaTabla::crear($auditoriaDto);
  
      return PersonaAsesoria::cargar($reg->id);
   }
 
   public static function eliminar($id)
   {
      $regEli = PersonaAsesoria::find($id);
 
      // Guardar auditoria
      $auditoriaDto = [ 
         'id_recurso' => $regEli->id,
         'nombre_recurso' => PersonaAsesoria::class,
         'descripcion_recurso' => $regEli->numero_documento,
         'accion' => AccionAuditoriaEnum::ELIMINAR,
         'recurso_original' => $regEli->toJson() 
      ];
      AuditoriaTabla::crear($auditoriaDto);
 
      return $regEli->delete();
   }
  
   use HasFactory;
 }
