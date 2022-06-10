<?php

namespace App\Models\Proyectos;

use App\Models\Proyectos\Pago;
use App\Models\Proyectos\Proyecto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PagoDetalle extends Model
{
    protected $table = 'pagos_detalle'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'proyecto_id',
        'pago_id',
        'pagDetFechaPago',
        'pagDetNumeroCuota',
        'pagDetFechaVencimientoCuota',
        'pagDetValorCapitalCuotaPagado',
        'pagDetValorSaldoCuotaPagado',
        'pagDetValorInteresCuotaPagado',
        'pagDetValorSeguroCuotaPagado',
        'pagDetValorInteresMoraPagado',
        'pagDetDiasMora',
        'pagDetSaldoCartera',
        'pagDetEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function proyecto(){
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function pago(){
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('pagos_detalle')
            ->join('proyectos', 'pagos.proyecto_id', 'proyectos.id')
            ->select(
                'pagos_detalle.id',
                'proyectos.id AS proyecto_id',
                'proyectos.proyectosFechaSolicitud AS fecha_solicitud',
                'pagos_detalle.pagDetNumeroCuota',
                'pagos_detalle.pagDetFechaPago',
                'pagos_detalle.pagDetEstado AS estado',
            );
        $query->orderBy('pagos_detalle.id', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('pagos_detalle')
            ->join('proyectos', 'pagos_detalle.proyecto_id', 'proyectos.id')
            ->join('pagos', 'pagos_detalle.pago_id', 'pagos.id')
            ->select(
                'pagos_detalle.id',
                'proyectos.id AS proyecto_id',
                'pagos_detalle.pagDetFechaPago',
                'pagos_detalle.pagDetNumeroCuota',
                'pagos_detalle.pagDetFechaVencimientoCuota',
                'pagos_detalle.pagDetValorCapitalCuotaPagado',
                'pagos_detalle.pagDetValorSaldoCuotaPagado',
                'pagos_detalle.pagDetValorInteresCuotaPagado',
                'pagos_detalle.pagDetValorSeguroCuotaPagado',
                'pagos_detalle.pagDetValorInteresMoraPagado',
                'pagos_detalle.pagDetDiasMora',
                'pagos_detalle.pagDetSaldoCartera',
                'pagos_detalle.pagDetEstado',
                'pagos_detalle.usuario_creacion_id',
                'pagos_detalle.usuario_creacion_nombre',
                'pagos_detalle.usuario_modificacion_id',
                'pagos_detalle.usuario_modificacion_nombre',
                'pagos_detalle.created_at AS fecha_creacion',
                'pagos_detalle.updated_at AS fecha_modificacion',
            );

        if(isset($dto['proyecto'])){
            $query->where('proyectos.id', '>=', $dto['proyecto']);
        }
        if(isset($dto['fechaDesde'])){
            $query->where('pagos_detalle.pagDetFechaPago', '>=', $dto['fechaDesde'].' 00:00:00');
        }
        if(isset($dto['fechaHasta'])){
            $query->where('pagos_detalle.pagDetFechaPago', '<=', $dto['fechaHasta'] . ' 23:59:59');
        }
        if(isset($dto['estado'])){
            $query->where('pagos_detalle.pagDetEstado', $dto['estado']);
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'proyecto_id'){
                    $query->orderBy('pagos_detalle.proyecto_id', $value);
                }
                if($attribute == 'pagDetFechaPago'){
                    $query->orderBy('pagos_detalle.pagDetFechaPago', $value);
                }
                if($attribute == 'pagDetNumeroCuota'){
                    $query->orderBy('pagos_detalle.pagDetNumeroCuota', $value);
                }
                if($attribute == 'pagDetFechaVencimientoCuota'){
                    $query->orderBy('pagos_detalle.pagDetFechaVencimientoCuota', $value);
                }
                if($attribute == 'pagDetValorCapitalCuotaPagado'){
                    $query->orderBy('pagos_detalle.pagDetValorCapitalCuotaPagado', $value);
                }
                if($attribute == 'pagDetValorSaldoCuotaPagado'){
                    $query->orderBy('pagos_detalle.pagDetValorSaldoCuotaPagado', $value);
                }
                if($attribute == 'pagDetValorInteresCuotaPagado'){
                    $query->orderBy('pagos_detalle.pagDetValorInteresCuotaPagado', $value);
                }
                if($attribute == 'pagDetValorSeguroCuotaPagado'){
                    $query->orderBy('pagos_detalle.pagDetValorSeguroCuotaPagado', $value);
                }
                if($attribute == 'pagDetValorInteresMoraPagado'){
                    $query->orderBy('pagos_detalle.pagDetValorInteresMoraPagado', $value);
                }
                if($attribute == 'pagDetDiasMora'){
                    $query->orderBy('pagos_detalle.pagDetDiasMora', $value);
                }
                if($attribute == 'pagDetSaldoCartera'){
                    $query->orderBy('pagos_detalle.pagDetSaldoCartera', $value);
                }
                if($attribute == 'pagDetEstado'){
                    $query->orderBy('pagos_detalle.pagDetEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('pagos_detalle.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('pagos_detalle.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('pagos_detalle.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('pagos_detalle.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("pagos_detalle.updated_at", "desc");
        }

        $pagos = $query->paginate($dto['limite'] ?? 100);
        $datos = [];

        foreach ($pagos ?? [] as $pago){
            array_push($datos, $pago);
        }

        $cantidadPagos = count($pagos);
        $to = isset($pagos) && $cantidadPagos > 0 ? $pagos->currentPage() * $pagos->perPage() : null;
        $to = isset($to) && isset($pagos) && $to > $pagos->total() && $cantidadPagos > 0 ? $pagos->total() : $to;
        $from = isset($to) && isset($pagos) && $cantidadPagos > 0 ?
            ( $pagos->perPage() > $to ? 1 : ($to - $cantidadPagos) + 1 )
            : null;

        return [
            'datos' => $datos,
            'desde' => $from,
            'hasta' => $to,
            'por_pagina' => isset($pagos) && $cantidadPagos > 0 ? +$pagos->perPage() : 0,
            'pagina_actual' => isset($pagos) && $cantidadPagos > 0 ? $pagos->currentPage() : 1,
            'ultima_pagina' => isset($pagos) && $cantidadPagos > 0 ? $pagos->lastPage() : 0,
            'total' => isset($pagos) && $cantidadPagos > 0 ? $pagos->total() : 0
        ];
    }

    public static function cargar($id)
    {
        $pago = Pago::find($id);
        $proyecto = $pago->proyecto;
        return [
            'id' => $pago->id,
            'proyecto_id' => $pago->proyecto_id,
            'pagosValorTotalPago' => $pago->pagosValorTotalPago,
            'pagosDescripcionPago' => $pago->pagosDescripcionPago,
            'pagosFechaPago' => $pago->pagosFechaPago,
            'pagosEstado' => $pago->pagosEstado,
            'usuario_creacion_id' => $pago->usuario_creacion_id,
            'usuario_creacion_nombre' => $pago->usuario_creacion_nombre,
            'usuario_modificacion_id' => $pago->usuario_modificacion_id,
            'usuario_modificacion_nombre' => $pago->usuario_modificacion_nombre,
            'fecha_creacion' => (new Carbon($pago->created_at))->format("Y-m-d H:i:s"),
            'fecha_modificacion' => (new Carbon($pago->updated_at))->format("Y-m-d H:i:s"),
            'proyecto' => isset($proyecto) ? [
                'id' => $proyecto->id,
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
        $pago = isset($dto['id']) ? Pago::find($dto['id']) : new Pago();

        // Guardar objeto original para auditoria
        $pagoOriginal = $pago->toJson();

        $pago->fill($dto);
        $guardado = $pago->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar la aplicación.", $pago);
        }

        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $pago->id,
            'nombre_recurso' => Pago::class,
            'descripcion_recurso' => $pago->pagosDescripcionPago,
            'accion' => isset($dto['id']) ? AccionAuditoriaEnum::MODIFICAR : AccionAuditoriaEnum::CREAR,
            'recurso_original' => isset($dto['id']) ? $pagoOriginal : $pago->toJson(),
            'recurso_resultante' => isset($dto['id']) ? $pago->toJson() : null
        ];
        
        AuditoriaTabla::crear($auditoriaDto);

        if(!isset($dto['id'])){
            $params['numero_proyecto'] = $dto['proyecto_id'];
            $params['pago_id'] = $pago->id;
            $params['fecha_pago'] = $dto['pagosFechaPago'];
            $params['valor_pago'] = $dto['pagosValorTotalPago'];
            $params['usuario_id'] = $usuario->id;
            $params['usuario_nombre'] = $usuario->nombre;
            Pago::pagosAplicar($params);
        } else {
            if($dto['pagosEstado'] == 0){
                $params['numero_proyecto'] = $dto['proyecto_id'];
                $params['fecha_pago'] = $dto['pagosFechaPago'];
                $params['usuario_id'] = $usuario->id;
                $params['usuario_nombre'] = $usuario->nombre;
                Pago::pagosReversar($params);
            }
        }
        
        return Pago::cargar($pago->id);
    }

    public static function eliminar($id)
    {
        $pago = Pago::find($id);
        
        // Guardar auditoria
        $auditoriaDto = [
            'id_recurso' => $pago->id,
            'nombre_recurso' => Pago::class,
            'descripcion_recurso' => $pago->pagosDescripcionPago,
            'accion' => AccionAuditoriaEnum::ELIMINAR,
            'recurso_original' => $pago->toJson()
        ];
        AuditoriaTabla::crear($auditoriaDto);

        return $pago->delete();
    }

    public static function pagosAplicar($params){
        $numeroProyecto = $params['numero_proyecto'];
        $pagoId = $params['pago_id'];
        $fechaPago = $params['fecha_pago'];
        $valorPago = $params['valor_pago'];
        $transaccion = 'AplicarPagos';
        $usuarioId = $params['usuario_id'];
        $usuario = $params['usuario_nombre'];
        $procedure = DB::select(
            'CALL SP_PagosAplicar(?,?,?,?,?,?,?)', 
            array(
                $numeroProyecto,
                $pagoId,
                $fechaPago,
                $valorPago,
                $transaccion,
                $usuarioId,
                $usuario
            ));
    }

    public static function pagosReversar($params){
        $funcion = $params['reversar']??false;
        $numeroProyecto = '';
        $fechaPago = '';
        $transaccion = 'ReversarPagos';
        $usuarioId = '';
        $usuarioNombre = '';
        if($funcion){
            $user = Auth::user();
            $usuario = $user->usuario();
            $pago = Pago::find($params['id']);
            $numeroProyecto = $pago->proyecto_id;
            $fechaPago = $pago->pagosFechaPago;
            $usuarioId = $usuario->id;
            $usuarioNombre = $usuario->nombre;
        } else {
            $numeroProyecto = $params['numero_proyecto'];
            $fechaPago = $params['fecha_pago'];
            $usuarioId = $params['usuario_id'];
            $usuarioNombre = $params['usuario_nombre'];
        }
        $procedure = DB::select(
            'CALL SP_PagosReversar(?,?,?,?,?)', 
            array(
                $numeroProyecto,
                $fechaPago,
                $transaccion,
                $usuarioId,
                $usuarioNombre
            ));

        return true;
    }

    public static function numberToWord($num){
        $number = intval($num);
        $numberAsString = strval($number);
        $long = strlen($numberAsString);
        $numberAsWord = '';
        $special = false;
        while($long>0){
            if($long>0 && $long<=3){
                $numberAsWord = $numberAsWord.' '.Pago::getNameOfThreeDigitsNumber($number, $numberAsString);
                $long = $long - 3;
                $special = false;
            }
            if($long>3 && $long<=6){
                $tempNum = intval(floor($number/1000));
                $tempNumAsString = strval($tempNum);
                $numberAsWord = $numberAsWord.' '.Pago::getNameOfThreeDigitsNumber($tempNum, $tempNumAsString).' MIL';
                $number = $number - intval($tempNum*1000);
                $numberAsString = strval($number);
                $long = $number === 0 ? 0 : strlen($numberAsString);
                $special = false;
            }
            if($long>6 && $long<=9){
                $tempNum = intval(floor($number/1000000));
                $tempNumAsString = strval($tempNum);
                $currency = $tempNum === 1 ? ' MILLÓN': ' MILLONES'; 
                $numberAsWord = $numberAsWord.' '.Pago::getNameOfThreeDigitsNumber($tempNum, $tempNumAsString).$currency;
                $number = $number - intval($tempNum*1000000);
                $numberAsString = strval($number);
                $long = $number === 0 ? 0 : strlen($numberAsString);
                $special = true;
            }

        }
        $finalWord = $special ? ' DE PESOS':' PESOS';
        return $numberAsWord.$finalWord;
    }

    public static function getNameOfThreeDigitsNumber($num, $numAsString){
        $unique = array(
            0 => '', 
            1 => 'un', 
            2 => 'dos', 
            3 => 'tres', 
            4 => 'cuatro', 
            5 => 'cinco', 
            6 => 'seis', 
            7 => 'siete', 
            8 => 'ocho', 
            9 => 'nueve',
            10 => 'diez', 
            11 => 'once', 
            12 => 'doce', 
            13 => 'trece', 
            14 => 'catorce', 
            15 => 'quince', 
            16 => 'dieciseis', 
            17 => 'diecisiete', 
            18 => 'dieciocho', 
            19 => 'diecinueve',
            20 => 'veinte', 
            21 => 'veintiun', 
            22 => 'veintidos', 
            23 => 'veintitres', 
            24 => 'veinticuatro', 
            25 => 'veinticinco', 
            26 => 'veintiseis', 
            27 => 'veintisiete', 
            28 => 'veintiocho', 
            29 => 'veintinueve'
        );
        $decens = array(
            3 => 'treinta', 
            4 => 'cuarenta', 
            5 => 'cincuenta', 
            6 => 'sesenta', 
            7 => 'setenta', 
            8 => 'ochenta', 
            9 => 'noventa'
        );
        $hundreds = array(
            1 => '', 
            2 => 'dosci', 
            3 => 'tresci', 
            4 => 'cuatroci', 
            5 => 'quini', 
            6 => 'seisci', 
            7 => 'seteci', 
            8 => 'ochoci', 
            9 => 'noveci'
        );

        $numberAsWord = '';

        while(true):
            if($num<=29){
                $numberAsWord = $numberAsWord.' '.$unique[$num];
                return strtoupper($numberAsWord);
            } else if ($num<=99) {
                $rounded = $numAsString[1]!=='0'?true:false;
                if($rounded){
                    $numberAsWord = $numberAsWord.' '.$decens[$numAsString[0]].' Y '.$unique[$numAsString[1]];
                } else {
                    $numberAsWord = $numberAsWord.' '.$decens[$numAsString[0]];
                }
                return strtoupper($numberAsWord);
            } else {
                $hundredPart = $num/100;
                $hundredPart =  intval(floor($hundredPart));
                $residual = $num-$hundredPart*100;
                if($hundredPart === 1){
                    if($residual === 0) {
                        $numberAsWord = 'cien';
                        return strtoupper($numberAsWord);
                    } else {
                        $numberAsWord = 'ciento';
                    }
                } else {
                    $numberAsWord = $hundreds[$hundredPart].'entos';
                }
                $num = intval($num - $hundredPart*100);
                $numAsString = strval($num);
            }
        endwhile;
    }

    use HasFactory;
}
