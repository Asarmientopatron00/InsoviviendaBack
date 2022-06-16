<?php

namespace App\Models\Proyectos;

use Exception;
use Carbon\Carbon;
use App\Enum\AccionAuditoriaEnum;
use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Proyectos\PagoDetalle;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\AuditoriaTabla;
use App\Models\Parametrizacion\ParametroConstante;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pago extends Model
{
    protected $table = 'pagos'; // nombre de la tabla en la base de datos

    protected $fillable = [ // nombres de los campos
        'proyecto_id',
        'pagosFechaPago',
        'pagosValorTotalPago',
        'pagosDescripcionPago',
        'pagosConsecutivo',
        'pagosSaldoDespPago',
        'pagosEstado',
        'usuario_creacion_id',
        'usuario_creacion_nombre',
        'usuario_modificacion_id',
        'usuario_modificacion_nombre',
    ];

    public function proyecto(){
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function pagosDetalle(){
        return $this->hasMany(PagoDetalle::class, 'pago_id');
    }

    public static function obtenerColeccionLigera($dto){
        $query = DB::table('pagos')
            ->join('proyectos', 'pagos.proyecto_id', 'proyectos.id')
            ->select(
                'pagos.id',
                'proyectos.id AS proyecto_id',
                'proyectos.proyectosFechaSolicitud AS fecha_solicitud',
                'pagos.pagosValorTotalPago',
                'pagos.pagosFechaPago',
                'pagos.pagosEstado AS estado',
            );
        $query->orderBy('pagos.id', 'asc');
        return $query->get();
    }

    public static function obtenerColeccion($dto){
        $query = DB::table('pagos')
            ->join('proyectos', 'pagos.proyecto_id', 'proyectos.id')
            ->select(
                'pagos.id',
                'proyectos.id AS proyecto_id',
                'pagos.pagosValorTotalPago',
                'pagos.pagosFechaPago',
                'pagos.pagosDescripcionPago',
                'pagos.pagosConsecutivo',
                'pagos.pagosEstado',
                'pagos.usuario_creacion_id',
                'pagos.usuario_creacion_nombre',
                'pagos.usuario_modificacion_id',
                'pagos.usuario_modificacion_nombre',
                'pagos.created_at AS fecha_creacion',
                'pagos.updated_at AS fecha_modificacion',
            );

        if(isset($dto['proyecto'])){
            $query->where('proyectos.id', '>=', $dto['proyecto']);
        }
        if(isset($dto['fechaDesde'])){
            $query->where('pagos.pagosFechaPago', '>=', $dto['fechaDesde'].' 00:00:00');
        }
        if(isset($dto['fechaHasta'])){
            $query->where('pagos.pagosFechaPago', '<=', $dto['fechaHasta'] . ' 23:59:59');
        }
        if(isset($dto['estado'])){
            $query->where('pagos.pagosEstado', $dto['estado']);
        }

        if (isset($dto['ordenar_por']) && count($dto['ordenar_por']) > 0){
            foreach ($dto['ordenar_por'] as $attribute => $value){
                if($attribute == 'proyecto_id'){
                    $query->orderBy('pagos.proyecto_id', $value);
                }
                if($attribute == 'pagosValorTotalPago'){
                    $query->orderBy('pagos.pagosValorTotalPago', $value);
                }
                if($attribute == 'pagosFechaPago'){
                    $query->orderBy('pagos.pagosFechaPago', $value);
                }
                if($attribute == 'pagosDescripcionPago'){
                    $query->orderBy('pagos.pagosDescripcionPago', $value);
                }
                if($attribute == 'pagosConsecutivo'){
                    $query->orderBy('pagos.pagosConsecutivo', $value);
                }
                if($attribute == 'pagosEstado'){
                    $query->orderBy('pagos.pagosEstado', $value);
                }
                if($attribute == 'usuario_creacion_nombre'){
                    $query->orderBy('pagos.usuario_creacion_nombre', $value);
                }
                if($attribute == 'usuario_modificacion_nombre'){
                    $query->orderBy('pagos.usuario_modificacion_nombre', $value);
                }
                if($attribute == 'fecha_creacion'){
                    $query->orderBy('pagos.created_at', $value);
                }
                if($attribute == 'fecha_modificacion'){
                    $query->orderBy('pagos.updated_at', $value);
                }
            }
        }else{
            $query->orderBy("pagos.updated_at", "desc");
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
            'pagosConsecutivo' => $pago->pagosConsecutivo,
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
        
        $ultimoConsecutivo = 0;
        if(!isset($dto['id'])){
            $lastPago = Pago::where('proyecto_id', '>', 0)->max('pagosConsecutivo');
            $ultimoConsecutivo = $lastPago??0;
            $dto['pagosConsecutivo'] = $ultimoConsecutivo+1;
        }

        $pago->fill($dto);
        $guardado = $pago->save();
        if(!$guardado){
            throw new Exception("Ocurrió un error al intentar guardar el pago.", $pago);
        }

        if(!isset($dto['id'])){
            $parametro = ParametroConstante::where('codigo_parametro', 'CONSECUTIVO_RECIBO_CAJA')->first();
            if(isset($parametro)){
                $parametro->valor_parametro = $ultimoConsecutivo+1;
                $parametro->save();
            } else {
                $create = ParametroConstante::create([
                    'codigo_parametro' => 'CONSECUTIVO_RECIBO_CAJA',
                    'descripcion_parametro' => 'Último valor del consecutivo del recibo de caja',
                    'valor_parametro' => $ultimoConsecutivo+1,
                    'estado' => 1,
                    'usuario_creacion_id' => $usuario->id,
                    'usuario_creacion_nombre' => $usuario->nombre,
                    'usuario_modificacion_id' => $usuario->id,
                    'usuario_modificacion_nombre' => $usuario->nombre,
                ]);
            }
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

        $registroInicial = json_decode($pagoOriginal);
        $params['numero_proyecto'] = $dto['proyecto_id'];
        $params['pago_id'] = $pago->id;
        $params['fecha_pago'] = $dto['pagosFechaPago'];
        $params['valor_pago'] = $dto['pagosValorTotalPago'];
        $params['usuario_id'] = $usuario->id;
        $params['usuario_nombre'] = $usuario->nombre;

        if(!isset($dto['id'])){
            Pago::pagosAplicar($params);
        } else {
            if($dto['pagosEstado'] == 0){
                Pago::pagosReversar($params);
            } else {
                if($registroInicial->pagosValorTotalPago !== $pago->pagosValorTotalPago){
                    Pago::pagosReversar($params);
                    $pagosDetalle = $pago->pagosDetalle;
                    foreach($pagosDetalle as $pagoDetalle){
                       $delete = PagoDetalle::destroy($pagoDetalle->id);
                       if(!$delete){
                            throw new Exception("Ocurrió un error al intentar eliminar los pagos detalle.", $pagoDetalle);
                       }
                    }
                    $newPago = Pago::find($pago->id);
                    $newPago->pagosEstado = 1;
                    $save = $newPago->save();
                    if($save){
                        Pago::pagosAplicar($params);
                    } else {
                        throw new Exception("Ocurrió un error al intentar guardar el pago.", $newPago);
                    }
                }
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
