<?php

namespace App\Models\Parametrizacion;

use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Parametrizacion\Comuna;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barrio extends Model
{
    protected $table = 'barrios'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'barriosDescripcion',
        'comuna_id',
        'barriosEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function comuna(){
        return $this->belongsTo(Comuna::class, 'comuna_id');
    }
    
    public static function obtenerColeccionLigera($dto){
        $query = DB::table('barrios')
            ->join('comunas', 'comuna_id', '=', 'comunas.id')
            ->select(
                'barrios.id',
                'barriosDescripcion As nombre',
                'comunasDescripcion As comuna',
                'barriosEstado As estado',
            );

            if(isset($dto['comuna_id'])){
                $query->where('comuna_id', $dto['comuna_id']);
        }        

        $query->orderBy('barriosDescripcion', 'asc');
        return $query->get();
    }
    
    public static function obtenerColeccion($dto){
        $query = DB::table('barrios')
            ->join('comunas', 'barrios.comuna_id', '=', 'comunas.id')
            ->select(
                'barrios.id',
                'barriosDescripcion As nombre',
                'comunasDescripcion As comuna',
                'barriosEstado As estado',
                'barrios.usuario_creacion_id',
                'barrios.usuario_creacion_nombre',
                'barrios.usuario_modificacion_id',
                'barrios.usuario_modificacion_nombre',
                'barrios.created_at AS fecha_creacion',
                'barrios.updated_at AS fecha_modificacion',
            );
    
        if(isset($dto['nombre'])){
            $query->where('barriosDescripcion', 'like', '%' . $dto['nombre'] . '%');
        }
    
        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('barrios.barriosDescripcion', $value);
                }
                if($attribute == 'comuna'){
                    $query->orderBy('comunas.comunasDescripcion', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('barrios.barriosEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('barrios.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('barrios.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('barrios.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('barrios.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("barrios.updated_at", "desc");
        }
    
        $Barrios = $query->paginate($dto['limite'] ?? 100);
        $datos = [];
    
        foreach ($Barrios ?? [] as $barrio){
            array_push($datos, $barrio);
        }
    
        $cantidadBarrios = count($Barrios);
        $to = isset($Barrios) && $cantidadBarrios > 0 ? $Barrios->currentPage() * $Barrios->perPage() : null;
        $to = isset($to) && isset($Barrios) && $to > $Barrios->total() && $cantidadBarrios > 0 ? $Barrios->total() : $to;
        $from = isset($to) && isset($Barrios) && $cantidadBarrios > 0 ?
            ( $Barrios->perPage() > $to ? 1 : ($to - $cantidadBarrios) + 1 )
            : null;
    
        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($Barrios) && $cantidadBarrios > 0 ? +$Barrios->perPage() : 0,
            'pagina_actual' => isset($Barrios) && $cantidadBarrios > 0 ? $Barrios->currentPage() : 1,
            'ultima_pagina' => isset($Barrios) && $cantidadBarrios > 0 ? $Barrios->lastPage() : 0,
            'total' => isset($Barrios) && $cantidadBarrios > 0 ? $Barrios->total() : 0
        ];
    }
    
    public static function cargar($id)
    {
        $barrio = Barrio::find($id);
        $comuna = $barrio->comuna;
        return [
            'id' => $barrio->id,
            'nombre' => $barrio->barriosDescripcion,
            'comuna_id' => $barrio->comuna_id,
            'estado' => $barrio->barriosEstado,
            'usuario_creacion_id' => $barrio->usuario_creacion_id,
            'usuario_creacion_nombre' => $barrio->usuario_creacion_nombre,
            'usuario_modificacion_id' => $barrio->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $barrio->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($barrio->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($barrio->updated_at))->format("Y-m-d H:i:s"),
            'comuna' => isset($comuna) ? [
                'id' => $comuna->id,
                'nombre' => $comuna->comunasDescripcion
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
        $barrio = isset($dto['id']) ? Barrio::find($dto['id']) : new barrio();
    
        // Guardar objeto original para auditoria
        $barrioOriginal = $barrio->toJson();
    
        $barrio->fill($dto);
        $guardado = $barrio->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar el barrio.", $barrio);
        }
    
    
        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $barrio->id,
            'nombre_recurso' => Barrio::class,
            'descripcion_recurso' => $barrio->barriosDescripcion,
            'comuna_recurso' => $barrio->comuna_id,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $barrioOriginal : $barrio->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $barrio->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return Barrio::cargar($barrio->id);
    }
    
    public static function eliminar($id)
    {
        $barrio = Barrio::find($id);
    
        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $barrio->id,
            'nombre_recurso' => Barrio::class,
            'descripcion_recurso' => $barrio->barriosDescripcion,
            'comuna_recurso' => $barrio->comuna_id,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $barrio->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);
    
        return $barrio->delete();
    }
    
    use HasFactory;
}
