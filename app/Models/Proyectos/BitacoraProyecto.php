<?php

namespace App\Models\Proyectos;

use Exception;
use Illuminate\Http\Response;
use App\Enum\AccionAuditoriaEnum;
use App\Models\Seguridad\AuditoriaTabla;
use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BitacoraProyecto extends Model
{
    protected $table = 'bitacoras'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'proyecto_id',
        'bitacorasFechaEvento',
        'bitacorasObservaciones',
        'bitacorasEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function proyecto(){
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('bitacoras')
            ->join('proyectos', 'proyectos.id', 'bitacoras.proyecto_id')
            ->join('personas', 'personas.id', 'proyectos.persona_id')
            ->select(
                'bitacoras.id',
                'proyectos.id AS proyecto_id',
                'proyectos.proyectosFechaSolicitud AS fechaSolicitud',
                'proyectos.proyectosEstadoProyecto AS estado',
                'personas.personasIdentificacion AS identificacion',
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(personas.personasNombres), ''),
                        IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
                        IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
                        )
                    AS solicitante"
                ),
                'bitacoras.bitacorasFechaEvento',
                'bitacoras.bitacorasObservaciones',
                'bitacoras.bitacorasEstado',
                'bitacoras.usuario_creacion_id',
                'bitacoras.usuario_creacion_nombre',
                'bitacoras.usuario_modificacion_id',
                'bitacoras.usuario_modificacion_nombre',
                'bitacoras.created_at AS fecha_creacion',
                'bitacoras.updated_at AS fecha_modificacion',
            )
            ->where('proyectos.id', $dto['proyecto_id']);

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'proyecto_id'){
                    $query->orderBy('proyectos.id', $value);
                }
                if($attribute == 'solicitante'){
                    $query->orderBy('personas.personasNombres', $value);
                }
                if($attribute == 'bitacorasFechaEvento'){
                    $query->orderBy('bitacoras.bitacorasFechaEvento', $value);
                }
                if($attribute == 'bitacorasObservaciones'){
                    $query->orderBy('bitacoras.bitacorasObservaciones', $value);
                }
                if($attribute == 'bitacorasEstado'){
                    $query->orderBy('bitacoras.bitacorasEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('bitacoras.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('bitacoras.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('bitacoras.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('bitacoras.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("bitacoras.updated_at", "desc");
        }

        $bitacorasProyecto = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($bitacorasProyecto ?? [] as $data){
            array_push($datos, $data);
        }

        $cantidadBitacorasProyecto = count($bitacorasProyecto);
        $to = isset($bitacorasProyecto) && $cantidadBitacorasProyecto > 0 ? $bitacorasProyecto->currentPage() * $bitacorasProyecto->perPage() : null;
        $to = isset($to) && isset($bitacorasProyecto) && $to > $bitacorasProyecto->total() && $cantidadBitacorasProyecto > 0 ? $bitacorasProyecto->total() : $to;
        $from = isset($to) && isset($bitacorasProyecto) && $cantidadBitacorasProyecto > 0 ?
            ( $bitacorasProyecto->perPage() > $to ? 1 : ($to - $cantidadBitacorasProyecto) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($bitacorasProyecto) && $cantidadBitacorasProyecto > 0 ? +$bitacorasProyecto->perPage() : 0,
            'pagina_actual' => isset($bitacorasProyecto) && $cantidadBitacorasProyecto > 0 ? $bitacorasProyecto->currentPage() : 1,
            'ultima_pagina' => isset($bitacorasProyecto) && $cantidadBitacorasProyecto > 0 ? $bitacorasProyecto->lastPage() : 0,
            'total' => isset($bitacorasProyecto) && $cantidadBitacorasProyecto > 0 ? $bitacorasProyecto->total() : 0
        ];
    }

    public static function getHeaders($id){
        $proyecto = Proyecto::find($id);
        $persona = $proyecto->solicitante;
        return $proyecto;
    }

    public static function cargar($proyecto_id, $id)
    {
        $bitacoraProyecto = BitacoraProyecto::find($id);
        $proyecto = $bitacoraProyecto->proyecto;
        
        return [
            'id' => $bitacoraProyecto->id,
            'bitacorasFechaEvento' => $bitacoraProyecto->bitacorasFechaEvento,
            'bitacorasObservaciones' => $bitacoraProyecto->bitacorasObservaciones,
            'bitacorasEstado' => $bitacoraProyecto->bitacorasEstado,
            'usuario_creacion_id' => $bitacoraProyecto->usuario_creacion_id,
            'usuario_creacion_nombre' => $bitacoraProyecto->usuario_creacion_nombre,
            'usuario_modificacion_id' => $bitacoraProyecto->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $bitacoraProyecto->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($bitacoraProyecto->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($bitacoraProyecto->updated_at))->format("Y-m-d H:i:s"),
            'proyecto' => isset($proyecto) ? [
                'id' => $proyecto->id,
            ] : null,
        ];
    }

    public static function modificarOCrear($proyecto_id, $dto)
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
        $bitacorasProyecto = isset($dto['id']) ? BitacoraProyecto::find($dto['id']) : new BitacoraProyecto();
        
        // Guardar objeto original para auditoria
        $bitacorasProyectoOriginal = $bitacorasProyecto->toJson();

        $bitacorasProyecto->fill($dto);
        $guardado = $bitacorasProyecto->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar el proyecto.", $bitacoraProyecto);
        }

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $bitacorasProyecto->id,
            'nombre_recurso' => BitacoraProyecto::class,
            'descripcion_recurso' => $bitacorasProyecto->bitacorasObservaciones,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $bitacorasProyectoOriginal : $bitacorasProyecto->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $bitacorasProyecto->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return BitacoraProyecto::cargar($proyecto_id, $bitacorasProyecto->id);
    }

    public static function eliminar($id)
    {
        $bitacorasProyecto = BitacoraProyecto::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $bitacorasProyecto->id,
            'nombre_recurso' => BitacoraProyecto::class,
            'descripcion_recurso' => $bitacorasProyecto->bitacorasObservaciones,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $bitacorasProyecto->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $bitacorasProyecto->delete();
    }
    use HasFactory;
}
