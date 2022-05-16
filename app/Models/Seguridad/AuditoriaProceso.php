<?php

namespace App\Models\Seguridad;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditoriaProceso extends Model
{
    protected $fillable = [
        'audProTransaccion',
        'audProTipo',
        'audProNumeroProyecto',
        'audProDescripcion',
        'audProUsuarioCreacionId',
        'audProUsuarioCreacionNombre',
    ];

    public static function obtenerColeccion($dto){
        $query = DB::table('auditoria_procesos')
            ->select(
                'id',
                'audProTransaccion',
                'audProTipo',
                'audProNumeroProyecto',
                'audProDescripcion',
                'audProUsuarioCreacionNombre',
                'created_at AS fecha'
            );

        if(isset($dto['nombre_recurso'])){
            $query->where('audProTransaccion', 'like', "%" . $dto['nombre_recurso'] . "%");
        }
        if(isset($dto['numero_proyecto'])){
            $query->where('audProNumeroProyecto', 'like', "%" . $dto['numero_proyecto'] . "%");
        }
        if(isset($dto['nombre_responsable'])){
            $query->where('audProUsuarioCreacionNombre', 'like', "%" . $dto['nombre_responsable'] . "%");
        }
        if(isset($dto['tipo'])){
            $query->where('audProTipo', 'like', "%" . $dto['tipo'] . "%");
        }
        if(isset($dto['descripcion_recurso'])){
            $query->where('audProDescripcion', 'like', '%' . $dto['descripcion_recurso'] . '%');
        }
        if(isset($dto['fecha_desde'])){
            $query->where('created_at', '>=', $dto['fecha_desde'] . ' 00:00:00');
        }
        if(isset($dto['fecha_hasta'])){
            $query->where('created_at', '<=', $dto['fecha_hasta'] . ' 23:59:59');
        }
        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'id'){
                    $query->orderBy('id', $value);
                }
                if($attribute == 'audProTransaccion'){
                    $query->orderBy('audProTransaccion', $value);
                }
                if($attribute == 'audProDescripcion'){
                    $query->orderBy('audProDescripcion', $value);
                }
                if($attribute == 'audProTipo'){
                    $query->orderBy('audProTipo', $value);
                }
                if($attribute == 'audProUsuarioCreacionNombre'){
                    $query->orderBy('audProUsuarioCreacionNombre', $value);
                }
                if($attribute == 'audProNumeroProyecto'){
                    $query->orderBy('audProNumeroProyecto', $value);
                }
                if($attribute == 'fecha'){
                    $query->orderBy('created_at', $value);
                }
            }
        }else{
            $query->orderBy("created_at", "desc");
        }

        $auditorias = $query->paginate($dto['limite'] ?? 100);
        $datos = [];
        foreach ($auditorias ?? [] as $auditoria){
            array_push($datos, $auditoria);
        }

        $cantidadAuditorias = count($auditorias ?? []);
        $to = isset($auditorias) && $cantidadAuditorias > 0 ? $auditorias->currentPage() * $auditorias->perPage() : null;
        $to = isset($to) && isset($auditorias) && $to > $auditorias->total() && $cantidadAuditorias > 0 ? $auditorias->total() : $to;
        $from = isset($to) && isset($auditorias) && $cantidadAuditorias > 0 ?
            ( $auditorias->perPage() > $to ? 1 : ($to - $cantidadAuditorias) + 1 )
            : null;
        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($auditorias) && $cantidadAuditorias > 0 ? +$auditorias->perPage() : 0,
            'pagina_actual' => isset($auditorias) && $cantidadAuditorias > 0 ? $auditorias->currentPage() : 1,
            'ultima_pagina' => isset($auditorias) && $cantidadAuditorias > 0 ? $auditorias->lastPage() : 0,
            'total' => isset($auditorias) && $cantidadAuditorias > 0 ? $auditorias->total() : 0
        ];
    }

    use HasFactory;
}
