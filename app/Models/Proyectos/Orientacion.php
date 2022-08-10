<?php

namespace App\Models\Proyectos;

use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use App\Models\Parametrizacion\TipoAsesoria;
use App\Models\PersonasEntidades\Orientador;
use App\Models\PersonasEntidades\PersonaAsesoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Orientacion extends Model

{
    protected $table = 'orientaciones'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'tipo_orientacion_id',
        'orientador_id',
        'orientacionesFechaOrientacion',
        'persona_asesoria_id',
        'orientacionesSolicitud',
        'orientacionesNota',
        'orientacionesRespuesta',
        'estado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];
    
    public function tipoAsesoria(){
        return $this->belongsTo(TipoAsesoria::class, 'tipo_orientacion_id');
    }

    public function orientador(){
        return $this->belongsTo(Orientador::class, 'orientador_id');
    }
    
    public function personaAsesoria(){
        return $this->belongsTo(PersonaAsesoria::class, 'persona_asesoria_id');
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('orientaciones')
            ->join('tipos_orientacion','tipos_orientacion.id','=','orientaciones.tipo_orientacion_id')
            ->join('orientadores','orientadores.id','=','orientaciones.orientador_id')
            ->join('personas_asesorias','personas_asesorias.id','=','orientaciones.persona_asesoria_id')
            ->select(
                'orientaciones.id',
                'tipos_orientacion.tipOriDescripcion as nombre',
                'orientadores.orientadoresNombre as nombreOrientador',
                'orientaciones.orientacionesFechaOrientacion as fechaOrientacion',
                'personas_asesorias.nombre as nombrePersona',
                'orientaciones.orientacionesSolicitud',
                'orientaciones.orientacionesNota',
                'orientaciones.orientacionesRespuesta',
                'orientaciones.estado',
                'orientaciones.usuario_creacion_id',
                'orientaciones.usuario_creacion_nombre',
                'orientaciones.usuario_modificacion_id',
                'orientaciones.usuario_modificacion_nombre',
                'orientaciones.created_at AS fecha_creacion',
                'orientaciones.updated_at AS fecha_modificacion',
            );

        if(isset($dto['tipoAsesoria'])){
            $query->where('tipos_orientacion.id', '=', $dto['tipoAsesoria']);
        }
        if(isset($dto['identificacionOrientador'])){
            $query->where('orientadores.orientadoresIdentificacion', '=', $dto['identificacionOrientador']);
        }
        if(isset($dto['fechaOrientacion'])){
            $query->where('orientaciones.orientacionesFechaOrientacion', 'like', '%' . $dto['fechaOrientacion'] . '%');
        }
        if(isset($dto['identificacionPersona'])){
            $query->where('personas_asesorias.numero_documento', '=', $dto['identificacionPersona']);
        }
        if(isset($dto['estado'])){
            $query->where('orientaciones.estado', 'like', '%' . $dto['estado'] . '%');
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'nombre'){
                    $query->orderBy('tipos_orientacion.orientadoresNombre', $value);
                }
                if($attribute == 'nombreOrientador'){
                    $query->orderBy('orientadores.orientadoresNombre', $value);
                }
                if($attribute == 'fechaOrientacion'){
                    $query->orderBy('orientaciones.orientacionesFechaOrientacion', $value);
                }
                if($attribute == 'nombrePersona'){
                    $query->orderBy('personas_asesorias.nombre', $value);
                }
                if($attribute == 'orientacionesSolicitud'){
                    $query->orderBy('orientaciones.orientacionesSolicitud', $value);
                }
                if($attribute == 'orientacionesNota'){
                    $query->orderBy('orientaciones.orientacionesNota', $value);
                }
                if($attribute == 'orientacionesRespuesta'){
                    $query->orderBy('orientaciones.orientacionesRespuesta', $value);
                }
                if($attribute == 'estado'){
                    $query->orderBy('orientaciones.estado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('orientaciones.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('orientaciones.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('orientaciones.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('orientaciones.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("orientaciones.updated_at", "desc");
        }

        $orientaciones = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($orientaciones ?? [] as $orientacion){
            array_push($datos, $orientacion);
        }

        $cantidadOrientacion = count($orientaciones);
        $to = isset($orientaciones) && $cantidadOrientacion > 0 ? $orientaciones->currentPage() * $orientaciones->perPage() : null;
        $to = isset($to) && isset($orientaciones) && $to > $orientaciones->total() && $cantidadOrientacion > 0 ? $orientaciones->total() : $to;
        $from = isset($to) && isset($orientaciones) && $cantidadOrientacion > 0 ?
            ( $orientaciones->perPage() > $to ? 1 : ($to - $cantidadOrientacion) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($orientaciones) && $cantidadOrientacion > 0 ? +$orientaciones->perPage() : 0,
            'pagina_actual' => isset($orientaciones) && $cantidadOrientacion > 0 ? $orientaciones->currentPage() : 1,
            'ultima_pagina' => isset($orientaciones) && $cantidadOrientacion > 0 ? $orientaciones->lastPage() : 0,
            'total' => isset($orientaciones) && $cantidadOrientacion > 0 ? $orientaciones->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $orientaciones = Orientacion::find($id);
        $tipoAsesoria = $orientaciones->tipoAsesoria;
        $orientador = $orientaciones->orientador;
        $personaAsesoria = $orientaciones->personaAsesoria;

        return [
            'id' => $orientaciones->id,
            'tipo_orientacion_id' => $orientaciones->tipo_orientacion_id,
            'orientador_id' => $orientaciones->orientador_id,
            'orientacionesFechaOrientacion' => $orientaciones->orientacionesFechaOrientacion,
            'persona_asesoria_id' => $orientaciones->persona_asesoria_id,
            'orientacionesSolicitud' => $orientaciones->orientacionesSolicitud,
            'orientacionesNota' => $orientaciones->orientacionesNota,
            'orientacionesRespuesta' => $orientaciones->orientacionesRespuesta,
            'estado' => $orientaciones->estado,
            'usuario_creacion_id' => $orientaciones->usuario_creacion_id,
            'usuario_creacion_nombre' => $orientaciones->usuario_creacion_nombre,
            'usuario_modificacion_id' => $orientaciones->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $orientaciones->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($orientaciones->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($orientaciones->updated_at))->format("Y-m-d H:i:s"),
            'tipoAsesoria' => isset($tipoAsesoria) ? [
                'id' => $tipoAsesoria->id,
                'nombre' => $tipoAsesoria->tipOriDescripcion
            ] : null,
            'orientador' => isset($orientador) ? [
                'id' => $orientador->id,
                'nombre' => $orientador->orientadoresNombre,
                'identificacion' => $orientador->orientadoresIdentificacion
            ] : null,
            'personaAsesoria' => isset($personaAsesoria) ? [
                'id' => $personaAsesoria->id,
                'nombre' => $personaAsesoria->nombre,
                'identificacion' => $personaAsesoria->numero_documento
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
        $orientaciones = isset($dto['id']) ? Orientacion::find($dto['id']) : new Orientacion();

        // Guardar objeto original para auditoria
        $orientacionesOriginal = $orientaciones->toJson();

        $orientaciones->fill($dto);
        $guardado = $orientaciones->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $orientaciones);
        }

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $orientaciones->id,
            'nombre_recurso' => Orientacion::class,
            'descripcion_recurso' => $orientaciones->orientacionesRespuesta,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $orientacionesOriginal : $orientaciones->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $orientaciones->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return Orientacion::cargar($orientaciones->id);
    }

    public static function eliminar($id)
    {
        $orientaciones = Orientacion::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $orientaciones->id,
            'nombre_recurso' => Orientacion::class,
            'descripcion_recurso' => $orientaciones->orientacionesRespuesta,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $orientaciones->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $orientaciones->delete();
    }

    use HasFactory;
}
