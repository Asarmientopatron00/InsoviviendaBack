<?php

namespace App\Models\Proyectos;

use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use App\Models\PersonasEntidades\Persona;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Donacion extends Model
{
   protected $table = 'donaciones'; // nombre de la tabla en la base de datos

   protected $fillable = [ 
      'persona_id',
      'benefactor_id',
      'donacionesFechaDonacion',
      'tipo_donacion_id',
      'donacionesValorDonacion',
      'donacionesEstadoDonacion',
      'forma_pago_id',
      'donacionesNumeroCheque',
      'banco_id',
      'donacionesNumeroRecibo',
      'donacionesFechaRecibo',
      'donacionesNotas',
      'estado',
      'usuario_creacion_id',
      'usuario_creacion_nombre',
      'usuario_modificacion_id',
      'usuario_modificacion_nombre', 
   ];

   public function persona(){
      return $this->belongsTo(Persona::class, 'persona_id');
   }
 
   public static function obtenerColeccionLigera($dto) 
   {
      $query = DB::table('donaciones')
         ->join('personas', 'personas.id', '=', 'donaciones.persona_id')
         ->join('benefactores', 'benefactores.id', '=', 'donaciones.benefactor_id')
         ->select(
            'donaciones.id',
            DB::Raw("CONCAT(IFNULL(CONCAT(personas.personasNombres), ''), 
                     IFNULL(CONCAT(' ', personas.personasPrimerApellido), ''),
                     IFNULL(CONCAT(' ', personas.personasSegundoApellido), '')) AS nombre"),
            DB::Raw("CONCAT(IFNULL(CONCAT(benefactores.benefactoresNombres), ''), 
                     IFNULL(CONCAT(' ', benefactores.benefactoresPrimerApellido), ''),
                     IFNULL(CONCAT(' ', benefactores.benefactoresSegundoApellido), '')) AS benefactor"),
            'donaciones.estado', 
         );
      $query->orderBy('nombre', 'asc');
      return $query->get();
   }
 
   public static function obtenerColeccion($dto) 
   {
      $query = DB::table('donaciones') 
         ->join('personas', 'personas.id', '=', 'donaciones.persona_id')
         ->join('benefactores', 'benefactores.id', '=', 'donaciones.benefactor_id')
         ->join('tipos_donacion', 'tipos_donacion.id', '=', 'donaciones.tipo_donacion_id')
         ->join('formas_pago', 'formas_pago.id', '=', 'donaciones.forma_pago_id')
         ->leftJoin('bancos', 'bancos.id', '=', 'donaciones.banco_id')
         ->select ( 
            'donaciones.id',
            DB::Raw("CONCAT(IFNULL(CONCAT(personas.personasNombres), ''), 
                     IFNULL(CONCAT(' ', personas.personasPrimerApellido), ''),
                     IFNULL(CONCAT(' ', personas.personasSegundoApellido), '')) AS nombre"),
            DB::Raw("CONCAT(IFNULL(CONCAT(benefactores.benefactoresNombres), ''), 
                     IFNULL(CONCAT(' ', benefactores.benefactoresPrimerApellido), ''),
                     IFNULL(CONCAT(' ', benefactores.benefactoresSegundoApellido), '')) AS benefactor"),
            'donaciones.donacionesFechaDonacion',
            'tipos_donacion.tipDonDescripcion',
            'donaciones.donacionesValorDonacion',
            'donaciones.donacionesEstadoDonacion',
            'formas_pago.forPagDescripcion',
            'donaciones.donacionesNumeroCheque',
            DB::Raw("IFNULL(bancos.bancosDescripcion, '') AS bancosDescripcion"),
            'donaciones.donacionesNumeroRecibo',
            'donaciones.donacionesFechaRecibo',
            'donaciones.donacionesNotas',
            'donaciones.estado',
            'donaciones.usuario_creacion_id',
            'donaciones.usuario_creacion_nombre',
            'donaciones.usuario_modificacion_id',
            'donaciones.usuario_modificacion_nombre',
            'donaciones.created_at AS fecha_creacion',
            'donaciones.updated_at AS fecha_modificacion',
         );
 
      // Filtro por nombre
      if (isset($dto['nombre'])) {
         $arrayNames = explode(' ', $dto['nombre']);
         $long = count($arrayNames);
         if ($long === 1) {
            $query->orWhere('personas.personasNombres', 'like', '%' . $arrayNames[0] . '%');
            $query->orWhere('personas.personasPrimerApellido', 'like', '%' . $arrayNames[0] . '%');
            $query->orWhere('personas.personasSegundoApellido', 'like', '%' . $arrayNames[0] . '%');
         }
         if ($long === 2) {
            $query->orWhere('personas.personasNombres', 'like', '%'.$arrayNames[0].' '.$arrayNames[1].'%');
            $query->orWhereRaw("CONCAT(TRIM(personas.personasNombres), ' ', 
               TRIM(personas.personasPrimerApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].'%']);
            $query->orWhereRaw("CONCAT(TRIM(personas.personasPrimerApellido), ' ', 
               TRIM(personas.personasSegundoApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].'%']);
            $query->orWhereRaw("CONCAT(TRIM(personas.personasPrimerApellido), ' ', 
               TRIM(personas.personasNombres)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].'%']);
         }
         if ($long === 3) {
            $query->orWhereRaw("CONCAT(TRIM(personas.personasNombres), ' ', 
               TRIM(personas.personasPrimerApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
            $query->orWhereRaw("CONCAT(
               TRIM(personas.personasNombres), ' ', 
               TRIM(personas.personasPrimerApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
            $query->orWhereRaw("CONCAT(
               TRIM(personas.personasNombres), ' ', 
               TRIM(personas.personasPrimerApellido), ' ', 
               TRIM(personas.personasSegundoApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
            $query->orWhereRaw("CONCAT(
               TRIM(personas.personasPrimerApellido), ' ', 
               TRIM(personas.personasSegundoApellido), ' ', 
               TRIM(personas.personasNombres)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
         }
         if ($long === 4) {
            $query->orWhereRaw("CONCAT(
               TRIM(personas.personasNombres), ' ',
               TRIM(personas.personasPrimerApellido), ' ', 
               TRIM(personas.personasSegundoApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].' '.$arrayNames[3].'%']);
            $query->orWhereRaw("CONCAT(
               TRIM(personas.personasPrimerApellido), ' ', 
               TRIM(personas.personasSegundoApellido), ' ', 
               TRIM(personas.personasNombres)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].' '.$arrayNames[3].'%']);
         }
      }

      //Filtro por identificacion persona 
      if(isset($dto['identificacion'])){
         $query->where('personas.personasIdentificacion', 'like', '%' . $dto['identificacion'] . '%');
     }

      // Filtro por benefactor
      if (isset($dto['benefactor'])) {
         $arrayNames = explode(' ', $dto['benefactor']);
         $long = count($arrayNames);
         if ($long === 1) {
            $query->orWhere('benefactores.benefactoresNombres', 'like', '%' . $arrayNames[0] . '%');
            $query->orWhere('benefactores.benefactoresPrimerApellido', 'like', '%' . $arrayNames[0] . '%');
            $query->orWhere('benefactores.benefactoresSegundoApellido', 'like', '%' . $arrayNames[0] . '%');
         }
         if ($long === 2) {
            $query->orWhere('benefactores.benefactoresNombres', 'like', '%'.$arrayNames[0].' '.$arrayNames[1].'%');
            $query->orWhereRaw("CONCAT(TRIM(benefactores.benefactoresNombres), ' ', 
               TRIM(benefactores.benefactoresPrimerApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].'%']);
            $query->orWhereRaw("CONCAT(TRIM(benefactores.benefactoresPrimerApellido), ' ', 
               TRIM(benefactores.benefactoresSegundoApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].'%']);
            $query->orWhereRaw("CONCAT(TRIM(benefactores.benefactoresPrimerApellido), ' ', 
               TRIM(benefactores.benefactoresNombres)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].'%']);
         }
         if ($long === 3) {
            $query->orWhereRaw("CONCAT(TRIM(benefactores.benefactoresNombres), ' ', 
               TRIM(benefactores.benefactoresPrimerApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
            $query->orWhereRaw("CONCAT(
               TRIM(benefactores.benefactoresNombres), ' ', 
               TRIM(benefactores.benefactoresPrimerApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
            $query->orWhereRaw("CONCAT(
               TRIM(benefactores.benefactoresNombres), ' ', 
               TRIM(benefactores.benefactoresPrimerApellido), ' ', 
               TRIM(benefactores.benefactoresSegundoApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
            $query->orWhereRaw("CONCAT(
               TRIM(benefactores.benefactoresPrimerApellido), ' ', 
               TRIM(benefactores.benefactoresSegundoApellido), ' ', 
               TRIM(benefactores.benefactoresNombres)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].'%']);
         }
         if ($long === 4) {
            $query->orWhereRaw("CONCAT(
               TRIM(benefactores.benefactoresNombres), ' ',
               TRIM(benefactores.benefactoresPrimerApellido), ' ', 
               TRIM(benefactores.benefactoresSegundoApellido)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].' '.$arrayNames[3].'%']);
            $query->orWhereRaw("CONCAT(
               TRIM(benefactores.benefactoresPrimerApellido), ' ', 
               TRIM(benefactores.benefactoresSegundoApellido), ' ', 
               TRIM(benefactores.benefactoresNombres)) like ?",
               ['%'.$arrayNames[0].' '.$arrayNames[1].' '.$arrayNames[2].' '.$arrayNames[3].'%']);
         }
      }

      if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0)
         foreach ($dto['ordenar_por'] as $attribute => $value){
            if ($attribute == 'nombre') 
               $query->orderBy('nombre', $value); 

            if ($attribute == 'benefactor')  
               $query->orderBy('benefactor', $value); 

            if ($attribute == 'donacionesFechaDonacion')  
               $query->orderBy('donaciones.donacionesFechaDonacion', $value); 

            if ($attribute == 'tipDonDescripcion')  
               $query->orderBy('tipos_donacion.tipDonDescripcion', $value); 

            if ($attribute == 'donacionesValorDonacion')  
               $query->orderBy('donaciones.donacionesValorDonacion', $value); 

            if ($attribute == 'donacionesEstadoDonacion')  
               $query->orderBy('donaciones.donacionesEstadoDonacion', $value); 

            if ($attribute == 'forPagDescripcion')  
               $query->orderBy('formas_pago.forPagDescripcion', $value); 

            if ($attribute == 'donacionesNumeroCheque')  
               $query->orderBy('donaciones.donacionesNumeroCheque', $value); 

            if ($attribute == 'bancosDescripcion')  
               $query->orderBy('bancosDescripcion', $value); 

            if ($attribute == 'donacionesNumeroRecibo')  
               $query->orderBy('donaciones.donacionesNumeroRecibo', $value);

            if ($attribute == 'donacionesFechaRecibo')  
               $query->orderBy('donaciones.donacionesFechaRecibo', $value);

            if ($attribute == 'donacionesNotas')  
               $query->orderBy('donaciones.donacionesNotas', $value); 

            if ($attribute == 'estado')  
               $query->orderBy('donaciones.estado', $value); 

            if ($attribute == 'usuario_creacion_nombre')
               $query->orderBy('donaciones.usuario_creacion_nombre', $value);

            if ($attribute == 'usuario_modificacion_nombre')
               $query->orderBy('donaciones.usuario_modificacion_nombre', $value);

            if ($attribute == 'fecha_creacion')
               $query->orderBy('donaciones.created_at', $value);

            if ($attribute == 'fecha_modificacion')
               $query->orderBy('donaciones.updated_at', $value);
         }
      else 
         $query->orderBy("donaciones.updated_at", "desc");
 
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
      $regCargar = Donacion::find($id);
      $persona = $regCargar->persona;
      return [ 
         'id' => $regCargar->id,
         'persona_id' => $regCargar->persona_id,
         'benefactor_id' => $regCargar->benefactor_id,
         'donacionesFechaDonacion' => $regCargar->donacionesFechaDonacion,
         'tipo_donacion_id' => $regCargar->tipo_donacion_id,
         'donacionesValorDonacion' => $regCargar->donacionesValorDonacion,
         'donacionesEstadoDonacion' => $regCargar->donacionesEstadoDonacion,
         'forma_pago_id' => $regCargar->forma_pago_id,
         'donacionesNumeroCheque' => $regCargar->donacionesNumeroCheque,
         'banco_id' => $regCargar->banco_id,
         'donacionesNumeroRecibo' => $regCargar->donacionesNumeroRecibo,
         'donacionesFechaRecibo' => $regCargar->donacionesFechaRecibo,
         'donacionesNotas' => $regCargar->donacionesNotas,
         'estado' => $regCargar->estado,
         'usuario_creacion_id' => $regCargar->usuario_creacion_id,
         'usuario_creacion_nombre' => $regCargar->usuario_creacion_nombre,
         'usuario_modificacion_id' => $regCargar->usuario_modificacion_id,
         'usuario_modificacion_nombre' => $regCargar->usuario_modificacion_nombre,
         'fecha_creacion' => (new Carbon($regCargar->created_at))->format("Y-m-d H:i:s"),
         'fecha_modificacion' => (new Carbon($regCargar->updated_at))->format("Y-m-d H:i:s"),
         'persona' => isset($persona) ? [
            'id' => $persona->id,
            'nombre' => $persona->personasNombres.' '.$persona->personasPrimerApellido.' '.$persona->personasSegundoApellido,
            'identificacion' => $persona->personasIdentificacion
        ] : null,
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
      $reg = isset($dto['id']) ? Donacion::find($dto['id']) : new Donacion();
  
      // Guardar objeto original para auditoria
      $regOri = $reg->toJson();
  
      $reg->fill($dto);
      $guardado = $reg->save();
      if (!$guardado) 
         throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $reg);
  
      // Guardar auditoria
      $auditoriaDto = [ 
         'id_recurso' => $reg->id,
         'nombre_recurso' => Donacion::class,
         'descripcion_recurso' => $reg->persona_id,
         'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
         'recurso_original' => isset($dto['id']) ? $regOri : $reg->toJson(),
         'recurso_resultante' => isset($dto['id']) ? $reg->toJson() : null 
      ];
  
      AuditoriaTabla::crear($auditoriaDto);
  
      return Donacion::cargar($reg->id);
   }
 
   public static function eliminar($id)
   {
      $regEli = Donacion::find($id);
 
      // Guardar auditoria
      $auditoriaDto = [ 
         'id_recurso' => $regEli->id,
         'nombre_recurso' => Donacion::class,
         'descripcion_recurso' => $regEli->persona_id,
         'accion' => AccionAuditoriaEnum::ELIMINAR,
         'recurso_original' => $regEli->toJson() 
      ];
      AuditoriaTabla::crear($auditoriaDto);
 
      return $regEli->delete();
   }
  
   use HasFactory;
}
