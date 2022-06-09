<?php

namespace App\Models\Proyectos;

use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Parametrizacion\Banco;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use App\Models\Proyectos\PlanAmortizacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Desembolso extends Model
{
    protected $table = 'desembolsos'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'proyecto_id',
        'desembolsosFechaDesembolso',
        'desembolsosValorDesembolso',
        'desembolsosFechaNormalizacionP',
        'desembolsosDescripcionDes',
        'banco_id',
        'desembolsosTipoCuentaDes',
        'desembolsosNumeroCuentaDes',
        'desembolsosNumeroComEgreso',
        'desembolsosPlanDefinitivo',
        'desembolsosEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function proyecto(){
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function banco(){
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('desembolsos')
            ->join('proyectos', 'desembolsos.proyecto_id', 'proyectos.id')
            ->join('personas', 'proyectos.persona_id', 'personas.id')
            ->leftJoin('bancos', 'proyectos.banco_id', 'bancos.id')
            ->select(
                'desembolsos.id',
                'proyectos.id AS proyecto_id',
                'personas.personasIdentificacion AS identificacion',
                DB::Raw(
                    "CONCAT(
                        IFNULL(CONCAT(personas.personasNombres), ''),
                        IFNULL(CONCAT(' ',personas.personasPrimerApellido),''),
                        IFNULL(CONCAT(' ',personas.personasSegundoApellido), '')
                        )
                    AS solicitante"
                ),
                'bancos.bancosDescripcion',
                'desembolsos.desembolsosFechaDesembolso',
                'desembolsos.desembolsosValorDesembolso',
                'desembolsos.desembolsosFechaNormalizacionP',
                'desembolsos.desembolsosDescripcionDes',
                'desembolsos.desembolsosTipoCuentaDes',
                'desembolsos.desembolsosNumeroCuentaDes',
                'desembolsos.desembolsosNumeroComEgreso',
                'desembolsos.desembolsosPlanDefinitivo',
                'desembolsos.desembolsosEstado',
                'desembolsos.usuario_creacion_id',
                'desembolsos.usuario_creacion_nombre',
                'desembolsos.usuario_modificacion_id',
                'desembolsos.usuario_modificacion_nombre',
                'desembolsos.created_at AS fecha_creacion',
                'desembolsos.updated_at AS fecha_modificacion',
            );

        if(isset($dto['proyecto'])){
            $query->where('proyectos.id', '>=', $dto['proyecto']);
        }
        if(isset($dto['fechaDesde'])){
            $query->where('desembolsos.desembolsosFechaDesembolso', '>=', $dto['fechaDesde'].' 00:00:00');
        }
        if(isset($dto['fechaHasta'])){
            $query->where('desembolsos.desembolsosFechaDesembolso', '<=', $dto['fechaHasta'] . ' 23:59:59');
        }
        if(isset($dto['estado'])){
            $query->where('desembolsos.desembolsosEstado', $dto['estado']);
        }
        if(isset($dto['solicitante'])){
            $query->where('personas.personasIdentificacion', $dto['solicitante']);
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'proyecto_id'){
                    $query->orderBy('proyectos.id', $value);
                }
                if($attribute == 'identificacion'){
                    $query->orderBy('personas.personasIdentificacion', $value);
                }
                if($attribute == 'solicitante'){
                    $query->orderBy('personas.personasNombres', $value);
                }
                if($attribute == 'desembolsosFechaDesembolso'){
                    $query->orderBy('desembolsos.desembolsosFechaDesembolso', $value);
                }
                if($attribute == 'desembolsosValorDesembolso'){
                    $query->orderBy('desembolsos.desembolsosValorDesembolso', $value);
                }
                if($attribute == 'desembolsosFechaNormalizacionP'){
                    $query->orderBy('desembolsos.desembolsosFechaNormalizacionP', $value);
                }
                if($attribute == 'desembolsosDescripcionDes'){
                    $query->orderBy('desembolsos.desembolsosDescripcionDes', $value);
                }
                if($attribute == 'desembolsosTipoCuentaDes'){
                    $query->orderBy('desembolsos.desembolsosTipoCuentaDes', $value);
                }
                if($attribute == 'desembolsosNumeroCuentaDes'){
                    $query->orderBy('desembolsos.desembolsosNumeroCuentaDes', $value);
                }
                if($attribute == 'desembolsosNumeroComEgreso'){
                    $query->orderBy('desembolsos.desembolsosNumeroComEgreso', $value);
                }
                if($attribute == 'desembolsosPlanDefinitivo'){
                    $query->orderBy('desembolsos.desembolsosPlanDefinitivo', $value);
                }
                if($attribute == 'desembolsosEstado'){
                    $query->orderBy('desembolsos.desembolsosEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('desembolsos.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('desembolsos.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('desembolsos.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('desembolsos.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("desembolsos.updated_at", "desc");
        }

        $desembolsos = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($desembolsos ?? [] as $desembolso){
            array_push($datos, $desembolso);
        }

        $cantidadDesembolsos = count($desembolsos);
        $to = isset($desembolsos) && $cantidadDesembolsos > 0 ? $desembolsos->currentPage() * $desembolsos->perPage() : null;
        $to = isset($to) && isset($desembolsos) && $to > $desembolsos->total() && $cantidadDesembolsos > 0 ? $desembolsos->total() : $to;
        $from = isset($to) && isset($desembolsos) && $cantidadDesembolsos > 0 ?
            ( $desembolsos->perPage() > $to ? 1 : ($to - $cantidadDesembolsos) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($desembolsos) && $cantidadDesembolsos > 0 ? +$desembolsos->perPage() : 0,
            'pagina_actual' => isset($desembolsos) && $cantidadDesembolsos > 0 ? $desembolsos->currentPage() : 1,
            'ultima_pagina' => isset($desembolsos) && $cantidadDesembolsos > 0 ? $desembolsos->lastPage() : 0,
            'total' => isset($desembolsos) && $cantidadDesembolsos > 0 ? $desembolsos->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $desembolso = Desembolso::find($id);
        $proyecto = $desembolso->proyecto;
        $banco = $desembolso->banco;
        $solicitante = $proyecto->solicitante;

        return [
            'id' => $desembolso->id,
            'proyecto_id' => $desembolso->proyecto_id,
            'banco_id' => $desembolso->banco_id,
            'desembolsosFechaDesembolso' => $desembolso->desembolsosFechaDesembolso,
            'desembolsosValorDesembolso' => $desembolso->desembolsosValorDesembolso,
            'desembolsosFechaNormalizacionP' => $desembolso->desembolsosFechaNormalizacionP,
            'desembolsosDescripcionDes' => $desembolso->desembolsosDescripcionDes,
            'desembolsosTipoCuentaDes' => $desembolso->desembolsosTipoCuentaDes,
            'desembolsosNumeroCuentaDes' => $desembolso->desembolsosNumeroCuentaDes,
            'desembolsosNumeroComEgreso' => $desembolso->desembolsosNumeroComEgreso,
            'desembolsosPlanDefinitivo' => $desembolso->desembolsosPlanDefinitivo,
            'desembolsosEstado' => $desembolso->desembolsosEstado,
            'usuario_creacion_id' => $desembolso->usuario_creacion_id,
            'usuario_creacion_nombre' => $desembolso->usuario_creacion_nombre,
            'usuario_modificacion_id' => $desembolso->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $desembolso->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($desembolso->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($desembolso->updated_at))->format("Y-m-d H:i:s"),
            'solicitante' => isset($solicitante) ? [
                'id' => $solicitante->id,
                'nombre' => $solicitante->personasNombres.' '.$solicitante->personasPrimerApellido.' '.$solicitante->personasSegundoApellido,
                'identificacion' => $solicitante->personasIdentificacion
            ] : null,
            'proyecto' => isset($proyecto) ? [
                'id' => $proyecto->id,
            ] : null,
            'banco' => isset($banco) ? [
                'id' => $banco->id,
                'nombre' => $banco->bancosDescripcion
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
        $desembolso = isset($dto['id']) ? Desembolso::find($dto['id']) : new Desembolso();

        // Guardar objeto original para auditoria
        $desembolsoOriginal = $desembolso->toJson();

        $desembolso->fill($dto);
        $guardado = $desembolso->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $desembolso);
        }

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $desembolso->id,
            'nombre_recurso' => Desembolso::class,
            'descripcion_recurso' => $desembolso->desembolsosDescripcionDes,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $desembolsoOriginal : $desembolso->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $desembolso->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);

        $data['numero_proyecto'] = $dto['proyecto_id'];
        $data['tipo_plan'] = 'DES';
        $data['plan_def'] = $dto['desembolsosPlanDefinitivo'] == 1 ? 'S' : 'N';
        $data['usuario_id'] = $usuario->id;
        $data['usuario_nombre'] = $usuario->nombre;
        PlanAmortizacion::calcularPlan($data);
        
        return Desembolso::cargar($desembolso->id);
    }

    public static function eliminar($id)
    {
        $desembolso = Desembolso::find($id);

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $desembolso->id,
            'nombre_recurso' => Desembolso::class,
            'descripcion_recurso' => $desembolso->desembolsosDescripcionDes,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $desembolso->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $desembolso->delete();
    }

    use HasFactory;
}
