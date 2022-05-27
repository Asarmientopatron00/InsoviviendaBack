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

   protected $fillable = [ 
      'benefactoresIdentificacion',
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
      'usuario_modificacion_nombre', 
   ];

   public static function obtenerColeccionLigera($dto) 
   {
      $query = DB::table('benefactores') 
         -> select(  
            'benefactores.id',
            'benefactores.benefactoresIdentificacion',
            DB::Raw("CONCAT(IFNULL(CONCAT(benefactoresNombres), ''), 
                     IFNULL(CONCAT(' ', benefactoresPrimerApellido), ''),
                     IFNULL(CONCAT(' ', benefactoresSegundoApellido), '')) AS nombre"),
            'benefactores.estado', 
      );
      $query -> orderBy('benefactoresNombres', 'asc');
      return $query -> get();
   }

   public static function obtenerColeccion($dto) 
   {
      $query = DB::table('benefactores') 
         ->join('tipos_benefactor', 'tipos_benefactor.id', '=', 'benefactores.tipo_benefactor_id')
         ->join('paises', 'paises.id', '=', 'benefactores.pais_id')
         ->join('departamentos', 'departamentos.id', '=','benefactores.departamento_id')
         ->join('ciudades', 'ciudades.id', '=', 'benefactores.ciudad_id')
         ->leftJoin('comunas', 'comunas.id', '=', 'benefactores.comuna_id')
         ->leftJoin('barrios', 'barrios.id', '=', 'benefactores.barrio_id')
         ->leftJoin('benefactores AS benefactoresRef', 'benefactoresRef.id', '=', 'benefactores.benefactor_id')
         ->select(  
            'benefactores.id',
            'benefactores.benefactoresIdentificacion',
            DB::Raw("CONCAT(IFNULL(CONCAT(benefactores.benefactoresNombres), ''), 
                     IFNULL(CONCAT(' ', benefactores.benefactoresPrimerApellido), ''),
                     IFNULL(CONCAT(' ', benefactores.benefactoresSegundoApellido), '')) AS nombre"),
            'tipos_benefactor.tipBenDescripcion',
            'benefactores.benefactoresNombrePerContacto',
            DB::Raw("CONCAT(IFNULL(CONCAT(benefactoresRef.benefactoresNombres), ''), 
                     IFNULL(CONCAT(' ', benefactoresRef.benefactoresPrimerApellido), ''),
                     IFNULL(CONCAT(' ', benefactoresRef.benefactoresSegundoApellido), '')) AS benefactorRef"),
            'paises.paisesDescripcion',
            'departamentos.departamentosDescripcion',
            'ciudades.ciudadesDescripcion',
            DB::Raw("IFNULL(comunas.comunasDescripcion, '') AS comunasDescripcion"),
            DB::Raw("IFNULL(barrios.barriosDescripcion, '') AS barriosDescripcion"),
            'benefactores.benefactoresDireccion',
            'benefactores.benefactoresTelefonoFijo',
            'benefactores.benefactoresTelefonoCelular',
            'benefactores.benefactoresCorreo',
            'benefactores.benefactoresNotas',
            'benefactores.estado',
            'benefactores.usuario_creacion_id',
            'benefactores.usuario_creacion_nombre',
            'benefactores.usuario_modificacion_id',
            'benefactores.usuario_modificacion_nombre',
            'benefactores.created_at AS fecha_creacion',
            'benefactores.updated_at AS fecha_modificacion', 
         );
      
      // Filtro por nombre
      if (isset($dto['nombre'])) {
         $arrayNames = explode(' ', $dto['nombre']);
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
         foreach ($dto['ordenar_por'] as $attribute => $value) {
            if ($attribute == 'benefactoresIdentificacion') 
               $query->orderBy('benefactores.benefactoresIdentificacion', $value); 
            
            if ($attribute == 'nombre')  
               $query->orderBy('nombre', $value); 
            
            if ($attribute == 'tipBenDescripcion')  
               $query->orderBy('tipos_benefactor.tipBenDescripcion', $value); 
            
            if ($attribute == 'benefactoresNombrePerContacto')  
               $query->orderBy('benefactores.benefactoresNombrePerContacto', $value); 
         
            if ($attribute == 'benefactorRef')  
               $query->orderBy('benefactorRef', $value); 

            if ($attribute == 'paisesDescripcion')  
               $query->orderBy('paises.paisesDescripcion', $value); 

            if ($attribute == 'departamentosDescripcion')  
               $query->orderBy('departamentos.departamentosDescripcion', $value); 

            if ($attribute == 'ciudadesDescripcion')  
               $query->orderBy('ciudades.ciudadesDescripcion', $value); 

            if ($attribute == 'comunasDescripcion')  
               $query->orderBy('comunas.comunasDescripcion', $value); 

            if ($attribute == 'barriosDescripcion')  
               $query->orderBy('barrios.barriosDescripcion', $value); 

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
      $regCargar = Benefactor::find($id);
      return [ 
         'id' => $regCargar->id,
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
      $reg = isset($dto['id']) ? Benefactor::find($dto['id']) : new Benefactor();
 
      // Guardar objeto original para auditoria
      $regOri = $reg->toJson();
 
      $reg->fill($dto);
      $guardado = $reg->save();
      if (!$guardado) 
         throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $reg);
 
      // Guardar auditoria
      $auditoriaDto = [ 
         'id_recurso' => $reg->id,
         'nombre_recurso' => Benefactor::class,
         'descripcion_recurso' => $reg->benefactoresIdentificacion,
         'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
         'recurso_original' => isset($dto['id']) ? $regOri : $reg->toJson(),
         'recurso_resultante' => isset($dto['id']) ? $reg->toJson() : null 
      ];
 
      AuditoriaTabla::crear($auditoriaDto);
 
      return Benefactor::cargar($reg->id);
   }

   public static function eliminar($id)
   {
      $regEli = Benefactor::find($id);

      // Guardar auditoria
      $auditoriaDto = [ 
         'id_recurso' => $regEli->id,
         'nombre_recurso' => Benefactor::class,
         'descripcion_recurso' => $regEli->benefactoresIdentificacion,
         'accion' => AccionAuditoriaEnum::ELIMINAR,
         'recurso_original' => $regEli->toJson() 
      ];
      AuditoriaTabla::crear($auditoriaDto);

      return $regEli->delete();
   }
 
   use HasFactory;
}
