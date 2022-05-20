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

class Benefactor extends Model
{
   protected $table = 'benefactores'; // nombre de la tabla en la base de datos

   protected $fillable = [ 'benefactoresIdentificacion',
                           'benefactoresNombres',
                           'benefactoresPrimerApellido',
                           'benefactoresSegundoApellido',
                           'tipo_benefactor_id',
                           'benefactoresNombrePerContacto',
                           'benefactor_id',
                           'pais_id',
                           'departamento_id',
                           'ciudad_id',
                           'comuna_id',
                           'barrio_id',
                           'benefactoresDireccion',
                           'benefactoresTelefonoFijo',
                           'benefactoresTelefonoCelular',
                           'benefactoresCorreo',
                           'benefactoresNotas',
                           'estado',
                           'usuario_creacion_id',
                           'usuario_creacion_nombre',
                           'usuario_modificacion_id',
                           'usuario_modificacion_nombre', ];

   public static function obtenerColeccionLigera($dto) 
   {
      $query = DB::table('benefactores')->
                  select(  'id',
                           'benefactoresIdentificacion',
                           DB::Raw("CONCAT(IFNULL(CONCAT(benefactoresNombres), ''), 
                                           IFNULL(CONCAT(' ',benefactoresPrimerApellido),''),
                                           IFNULL(CONCAT(' ',benefactoresSegundoApellido), '')) AS nombre"),
                           'estado', );
      $query->orderBy('benefactoresNombres', 'asc');
      return $query->get();
   }

   public static function obtenerColeccion($dto) 
   {
      $query = DB::table('benefactores') -> 
                  select(  'id',
                           'benefactoresIdentificacion',
                           DB::Raw("CONCAT(IFNULL(CONCAT(benefactoresNombres), ''), 
                                           IFNULL(CONCAT(' ',benefactoresPrimerApellido),''),
                                           IFNULL(CONCAT(' ',benefactoresSegundoApellido), '')) AS nombre"),
                           'tipo_benefactor_id',
                           'benefactoresNombrePerContacto',
                           'benefactor_id',
                           'pais_id',
                           'departamento_id',
                           'ciudad_id',
                           'comuna_id',
                           'barrio_id',
                           'benefactoresDireccion',
                           'benefactoresTelefonoFijo',
                           'benefactoresTelefonoCelular',
                           'benefactoresCorreo',
                           'benefactoresNotas',
                           'estado',
                           'usuario_creacion_id',
                           'usuario_creacion_nombre',
                           'usuario_modificacion_id',
                           'usuario_modificacion_nombre',
                           'created_at AS fecha_creacion',
                           'updated_at AS fecha_modificacion', );

      if (isset($dto['benefactoresNombres']))
         $query->where('benefactoresNombres', 'like', '%' . $dto['benefactoresNombres'] . '%');

      if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0)
         foreach ($dto['ordenar_por'] as $attribute => $value){
            if ($attribute == 'benefactoresIdentificacion') 
               $query->orderBy('benefactores.benefactoresIdentificacion', $value); 
            if ($attribute == 'benefactoresNombres')  
               $query->orderBy('benefactores.benefactoresNombres', $value); 
            if ($attribute == 'benefactoresPrimerApellido')  
               $query->orderBy('benefactores.benefactoresPrimerApellido', $value); 
            if ($attribute == 'benefactoresSegundoApellido')  
               $query->orderBy('benefactores.benefactoresSegundoApellido', $value); 
            if ($attribute == 'tipo_benefactor_id')  
               $query->orderBy('benefactores.tipo_benefactor_id', $value); 
            if ($attribute == 'benefactoresNombrePerContacto')  
               $query->orderBy('benefactores.benefactoresNombrePerContacto', $value); 
            if ($attribute == 'benefactor_id')  
               $query->orderBy('benefactores.benefactor_id', $value); 
            if ($attribute == 'pais_id')  
               $query->orderBy('benefactores.pais_id', $value); 
            if ($attribute == 'departamento_id')  
               $query->orderBy('benefactores.departamento_id', $value); 
            if ($attribute == 'ciudad_id')  
               $query->orderBy('benefactores.ciudad_id', $value); 
            if ($attribute == 'comuna_id')  
               $query->orderBy('benefactores.comuna_id', $value); 
            if ($attribute == 'barrio_id')  
               $query->orderBy('benefactores.barrio_id', $value); 
            if ($attribute == 'benefactoresDireccion')  
               $query->orderBy('benefactores.benefactoresDireccion', $value); 
            if ($attribute == 'benefactoresTelefonoFijo')  
               $query->orderBy('benefactores.benefactoresTelefonoFijo', $value); 
            if ($attribute == 'benefactoresTelefonoCelular')  
               $query->orderBy('benefactores.benefactoresTelefonoCelular', $value); 
            if ($attribute == 'benefactoresCorreo')  
               $query->orderBy('benefactores.benefactoresCorreo', $value); 
            if ($attribute == 'benefactoresNotas')  
               $query->orderBy('benefactores.benefactoresNotas', $value); 
            if ($attribute == 'estado')  
               $query->orderBy('benefactores.estado', $value); 
            if ($attribute == 'usuario_creacion_nombre')
               $query->orderBy('benefactores.usuario_creacion_nombre', $value);
            if ($attribute == 'usuario_modificacion_nombre')
               $query->orderBy('benefactores.usuario_modificacion_nombre', $value);
            if ($attribute == 'fecha_creacion')
               $query->orderBy('benefactores.created_at', $value);
            if ($attribute == 'fecha_modificacion')
               $query->orderBy('benefactores.updated_at', $value);
         }
      else 
         $query->orderBy("benefactores.updated_at", "desc");

      $pag = $query->paginate($dto['limite'] ?? 100);
      $datos = [];

      foreach ($pag ?? [] as $pagTmp)
         array_push($datos, $pagTmp);

      $totReg = count($pag);
      $to = isset($pag) && $totReg > 0 ? $pag->currentPage() * $pag->perPage() : null;
      $to = isset($to) && isset($pag) && $to > $pag->total() && $totReg > 0 ? $pag->total() : $to;
      $from = isset($to) && isset($pag) && $totReg > 0 ? ( $pag->perPage() > $to ? 1 : ($to - $totReg) + 1 ) : null;

      return [ 'datos' => $datos,
               'desde' => $from,
               'hasta' => $to,
               'por_pagina' => isset($pag) && $totReg > 0 ? + $pag->perPage() : 0,
               'pagina_actual' => isset($pag) && $totReg > 0 ? $pag->currentPage() : 1,
               'ultima_pagina' => isset($pag) && $totReg > 0 ? $pag->lastPage() : 0,
               'total' => isset($pag) && $totReg > 0 ? $pag->total() : 0 ];
   }

   public static function cargar($id)
   {
      $regCargar = Benefactor::find($id);
      return [ 'id' => $regCargar->id,
               'benefactoresIdentificacion' => $regCargar->benefactoresIdentificacion, 
               'benefactoresNombres' => $regCargar->benefactoresNombres, 
               'benefactoresPrimerApellido' => $regCargar->benefactoresPrimerApellido, 
               'benefactoresSegundoApellido' => $regCargar->benefactoresSegundoApellido, 
               'tipo_benefactor_id' => $regCargar->tipo_benefactor_id, 
               'benefactoresNombrePerContacto' => $regCargar->benefactoresNombrePerContacto, 
               'benefactor_id' => $regCargar->benefactor_id, 
               'pais_id' => $regCargar->pais_id, 
               'departamento_id' => $regCargar->departamento_id, 
               'ciudad_id' => $regCargar->ciudad_id, 
               'comuna_id' => $regCargar->comuna_id, 
               'barrio_id' => $regCargar->barrio_id, 
               'benefactoresDireccion' => $regCargar->benefactoresDireccion, 
               'benefactoresTelefonoFijo' => $regCargar->benefactoresTelefonoFijo, 
               'benefactoresTelefonoCelular' => $regCargar->benefactoresTelefonoCelular, 
               'benefactoresCorreo' => $regCargar->benefactoresCorreo, 
               'benefactoresNotas' => $regCargar->benefactoresNotas, 
               'estado' => $regCargar->estado, 
               'usuario_creacion_id' => $regCargar->usuario_creacion_id,
               'usuario_creacion_nombre' => $regCargar->usuario_creacion_nombre,
               'usuario_modificacion_id' => $regCargar->usuario_modificacion_id,
               'usuario_modificacion_nombre' => $regCargar->usuario_modificacion_nombre,
               'fecha_creacion' => (new Carbon($regCargar->created_at))->format("Y-m-d H:i:s"),
               'fecha_modificacion' => (new Carbon($regCargar->updated_at))->format("Y-m-d H:i:s") ];
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
      $reg = isset($dto['id']) ? Benefactor::find($dto['id']) : new Benefactor();
 
      // Guardar objeto original para auditoria
      $regOri = $reg->toJson();
 
      $reg->fill($dto);
      $guardado = $reg->save();
      if (!$guardado) 
         throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $reg);
 
      // Guardar auditoria
      $auditoriaDto = [ 'id_recurso' => $reg->id,
                        'nombre_recurso' => Benefactor::class,
                        'descripcion_recurso' => $reg->benefactoresIdentificacion,
                        'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
                        'recurso_original' => isset($dto['id']) ? $regOri : $reg->toJson(),
                        'recurso_resultante' => isset($dto['id']) ? $reg->toJson() : null ];
 
      AuditoriaTabla::crear($auditoriaDto);
 
      return Benefactor::cargar($reg->id);
   }

   public static function eliminar($id)
   {
      $regEli = Benefactor::find($id);

      // Guardar auditoria
      $auditoriaDto = [ 'id_recurso' => $regEli->id,
                        'nombre_recurso' => Benefactor::class,
                        'descripcion_recurso' => $regEli->nombre,
                        'accion' => AccionAuditoriaEnum::ELIMINAR,
                        'recurso_original' => $regEli->toJson() ];
      AuditoriaTabla::crear($auditoriaDto);

      return $regEli->delete();
   }
 
   use HasFactory;
}
