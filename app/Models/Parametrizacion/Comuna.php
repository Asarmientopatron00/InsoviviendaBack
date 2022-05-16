<?php

namespace App\Models\Parametrizacion;

use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Parametrizacion\Ciudad;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comuna extends Model
{
    protected $table = 'comunas'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'comunasDescripcion',
        'ciudad_id',
        'comunasEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function ciudad(){
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }
    
    public static function obtenerColeccionLigera($dto){
        $query = DB::table('comunas')
            ->join('ciudades', 'ciudad_id', '=', 'ciudades.id')
            ->select(
                'comunas.id',
                'comunasDescripcion As nombre',
                'ciudades.id As ciudad_id',
                'ciudadesDescripcion As ciudad',
                'comunasEstado As estado',
            );
        $query->orderBy('comunasDescripcion', 'asc');
        return $query->get();
    }
    
    public static function obtenerColeccion($dto){
        $query = DB::table('comunas')
            ->join('ciudades', 'comunas.ciudad_id', '=', 'ciudades.id')
            ->select(
                'comunas.id',
                'comunasDescripcion As nombre',
                'ciudadesDescripcion As ciudad',
                'comunasEstado As estado',
                'comunas.usuario_creacion_id',
                'comunas.usuario_creacion_nombre',
                'comunas.usuario_modificacion_id',
                'comunas.usuario_modificacion_nombre',
                'comunas.created_at AS fecha_creacion',
                'comunas.updated_at AS fecha_modificacion',
            );
    
        if(isset($dto['nombre'])){
            $query->where('comunasDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }
    
        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('comunas.comunasDescripcion', $value);
                }
                if($attribute == 'ciudad'){
                    $query->orderBy('ciudades.ciudadesDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('comunas.comunasEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('comunas.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('comunas.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('comunas.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('comunas.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("comunas.updated_at", "desc");
        }
    
        $Comunas = $query->paginate($dto['limite'] ?? 100);
        $datos = [];
    
        foreach ($Comunas ?? [] as $comuna){
            array_push($datos, $comuna);
        }
    
        $cantidadComunas = count($Comunas);
        $to = isset($Comunas) && $cantidadComunas > 0 ? $Comunas->currentPage() * $Comunas->perPage() : null;
        $to = isset($to) && isset($Comunas) && $to > $Comunas->total() && $cantidadComunas > 0 ? $Comunas->total() : $to;
        $from = isset($to) && isset($Comunas) && $cantidadComunas > 0 ?
            ( $Comunas->perPage() > $to ? 1 : ($to - $cantidadComunas) + 1 )
            : null;
    
        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($Comunas) && $cantidadComunas > 0 ? +$Comunas->perPage() : 0,
            'pagina_actual' => isset($Comunas) && $cantidadComunas > 0 ? $Comunas->currentPage() : 1,
            'ultima_pagina' => isset($Comunas) && $cantidadComunas > 0 ? $Comunas->lastPage() : 0,
            'total' => isset($Comunas) && $cantidadComunas > 0 ? $Comunas->total() : 0
        ];
    }
    
    public static function cargar($id)
    {
        $comuna = Comuna::find($id);
        $ciudad = $comuna->ciudad;
        return [
            'id' => $comuna->id,
            'nombre' => $comuna->comunasDescripcion,
            'ciudad_id' => $comuna->ciudad_id,
            'estado' => $comuna->comunasEstado,
            'usuario_creacion_id' => $comuna->usuario_creacion_id,
            'usuario_creacion_nombre' => $comuna->usuario_creacion_nombre,
            'usuario_modificacion_id' => $comuna->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $comuna->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($comuna->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($comuna->updated_at))->format("Y-m-d H:i:s"),
            'ciudad' => isset($ciudad) ? [
                'id' => $ciudad->id,
                'nombre' => $ciudad->ciudadesDescripcion
            ] : null,
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
        $comuna = isset($dto['id']) ? Comuna::find($dto['id']) : new Comuna();
    
        // Guardar objeto original para auditoria
        $comunaOriginal = $comuna->toJson();
    
        $comuna->fill($dto);
        $guardado = $comuna->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar el departamento.", $comuna);
        }
    
    
        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $comuna->id,
            'nombre_recurso' => Comuna::class,
            'descripcion_recurso' => $comuna->comunasDescripcion,
            'ciudad_recurso' => $comuna->ciudad_id,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $comunaOriginal : $comuna->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $comuna->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return Comuna::cargar($comuna->id);
    }
    
    public static function eliminar($id)
    {
        $comuna = Comuna::find($id);
    
        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $comuna->id,
            'nombre_recurso' => Comuna::class,
            'descripcion_recurso' => $comuna->comunasDescripcion,
            'ciudad_recurso' => $comuna->ciudad_id,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $comuna->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);
    
        return $comuna->delete();
    }
    
    use HasFactory;
}
