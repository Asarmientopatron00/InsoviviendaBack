<?php

namespace App\Models\Proyectos;

use Illuminate\Http\Response;
use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Parametrizacion\TipoDocumentoProyecto;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentoProyecto extends Model
{
    protected $table = 'documentos_proyecto'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'proyecto_id',
        'tipo_documento_proyecto_id',
        'docProAplica',
        'docProEntregado',
        'docProEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function proyecto(){
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function tipoDocumentoProyecto(){
        return $this->belongsTo(TipoDocumentoProyecto::class, 'tipo_documento_proyecto_id');
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('documentos_proyecto')
            ->join('proyectos', 'proyectos.id', 'documentos_proyecto.proyecto_id')
            ->join('tipos_documentos_proyecto', 'tipos_documentos_proyecto.id', 'documentos_proyecto.tipo_documento_proyecto_id')
            ->join('personas', 'personas.id', 'proyectos.persona_id')
            ->select(
                'documentos_proyecto.id',
                'tipos_documentos_proyecto.tiDoPrDescripcion',
                'tipos_documentos_proyecto.tiDoPrRequerido',
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
                'documentos_proyecto.docProAplica',
                'documentos_proyecto.docProEntregado',
                'documentos_proyecto.docProEstado',
                'documentos_proyecto.usuario_creacion_id',
                'documentos_proyecto.usuario_creacion_nombre',
                'documentos_proyecto.usuario_modificacion_id',
                'documentos_proyecto.usuario_modificacion_nombre',
                'documentos_proyecto.created_at AS fecha_creacion',
                'documentos_proyecto.updated_at AS fecha_modificacion',
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
                if($attribute == 'tiDoPrDescripcion'){
                    $query->orderBy('tipos_documentos_proyecto.tiDoPrDescripcion', $value);
                }
                if($attribute == 'docProAplica'){
                    $query->orderBy('documentos_proyecto.docProAplica', $value);
                }
                if($attribute == 'docProEntregado'){
                    $query->orderBy('documentos_proyecto.docProEntregado', $value);
                }
                if($attribute == 'docProEstado'){
                    $query->orderBy('documentos_proyecto.docProEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('documentos_proyecto.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('documentos_proyecto.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('documentos_proyecto.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('documentos_proyecto.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("documentos_proyecto.updated_at", "desc");
        }

        $documentosProyecto = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($documentosProyecto ?? [] as $data){
            array_push($datos, $data);
        }

        $cantidadDocumentosProyecto = count($documentosProyecto);
        $to = isset($documentosProyecto) && $cantidadDocumentosProyecto > 0 ? $documentosProyecto->currentPage() * $documentosProyecto->perPage() : null;
        $to = isset($to) && isset($documentosProyecto) && $to > $documentosProyecto->total() && $cantidadDocumentosProyecto > 0 ? $documentosProyecto->total() : $to;
        $from = isset($to) && isset($documentosProyecto) && $cantidadDocumentosProyecto > 0 ?
            ( $documentosProyecto->perPage() > $to ? 1 : ($to - $cantidadDocumentosProyecto) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($documentosProyecto) && $cantidadDocumentosProyecto > 0 ? +$documentosProyecto->perPage() : 0,
            'pagina_actual' => isset($documentosProyecto) && $cantidadDocumentosProyecto > 0 ? $documentosProyecto->currentPage() : 1,
            'ultima_pagina' => isset($documentosProyecto) && $cantidadDocumentosProyecto > 0 ? $documentosProyecto->lastPage() : 0,
            'total' => isset($documentosProyecto) && $cantidadDocumentosProyecto > 0 ? $documentosProyecto->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $documentoProyecto = DocumentoProyecto::find($id);
        $proyecto = $documentoProyecto->proyecto;
        $tipoDocumentoProyecto = $documentoProyecto->tipoDocumentoProyecto;

        return [
            'id' => $documentoProyecto->id,
            'docProAplica' => $documentoProyecto->docProAplica,
            'docProEntregado' => $documentoProyecto->docProEntregado,
            'docProEstado' => $documentoProyecto->docProEstado,
            'usuario_creacion_id' => $documentoProyecto->usuario_creacion_id,
            'usuario_creacion_nombre' => $documentoProyecto->usuario_creacion_nombre,
            'usuario_modificacion_id' => $documentoProyecto->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $documentoProyecto->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($documentoProyecto->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($documentoProyecto->updated_at))->format("Y-m-d H:i:s"),
            'proyecto' => isset($proyecto) ? [
                'id' => $proyecto->id,
            ] : null,
            'tipoDocumentoProyecto' => isset($tipoDocumentoProyecto) ? [
                'id' => $tipoDocumentoProyecto->id,
                'nombre' => $tipoDocumentoProyecto->tiDoPrDescripcion,
            ] : null,
        ];
    }

    public static function modificar($dto)
    {
        $user = Auth::user();
        $usuario = $user->usuario();

        if(isset($usuario) || isset($dto['usuario_modificacion_id'])){
            $dto['usuario_modificacion_id'] = $usuario->id ?? ($dto['usuario_modificacion_id'] ?? null);
            $dto['usuario_modificacion_nombre'] = $usuario->nombre ?? ($dto['usuario_modificacion_nombre'] ?? null);
        }

        // Consultar aplicación
        $documentoProyecto = DocumentoProyecto::find($dto['id']);

        // Guardar objeto original para auditoria
        $documentoProyectoOriginal = $documentoProyecto->toJson();

        $documentoProyecto->fill($dto);
        $guardado = $documentoProyecto->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar el proyecto.", $documentoProyecto);
        }

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $documentoProyecto->id,
            'nombre_recurso' => DocumentoProyecto::class,
            'descripcion_recurso' => $documentoProyecto->tipoDocumentoProyecto->tiDoPrDescripcion,
            'accion' => AccionAuditoriaEnum::MODIFICAR,
            'recurso_original' => $documentoProyectoOriginal,
            'recurso_resultante' => $documentoProyecto->toJson()
        ];
        
        AuditoriaTabla::crear($auditoriaDto);
        
        return DocumentoProyecto::cargar($documentoProyecto->id);
    }

    public static function crearDocumentosDelProyecto($data){
        $proyecto_id = $data['proyecto_id']??1;
        $usuario_id = $data['usuario_id']??1;
        $usuario_nombre = $data['usuario_nombre']??'SuperUser';
        $tiposDocumento = TipoDocumentoProyecto::where('tiDoPrEstado', 1)->get();
        DB::beginTransaction();
        try {
            foreach($tiposDocumento ?? [] as $tipoDocumento){
                DocumentoProyecto::create([
                    'proyecto_id' => $proyecto_id,
                    'tipo_documento_proyecto_id' => $tipoDocumento->id,
                    'usuario_creacion_id' => $usuario_id,
                    'usuario_creacion_nombre' => $usuario_nombre,
                    'usuario_modificacion_id' => $usuario_id,
                    'usuario_modificacion_nombre' => $usuario_nombre,
                ]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response(get_response_body(["Ocurrió un error al intentar crear los documento del proyecto."]), Response::HTTP_CONFLICT);
        }
        return $tiposDocumento;
    }

    use HasFactory;
}
