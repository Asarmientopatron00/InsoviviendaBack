<?php

namespace App\Models\Procesos;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcesoMora extends Model
{
    public static function calcularMora($dto){
        $user = Auth::user();
        $usuario = $user->usuario();
        
        $proyecto_id = null;
        $reiniciarMora = 1;
        $transaccion = 'CalcularInteresMora';
        $usuarioId = $usuario->id;
        $usuario = $usuario->nombre;
        $procedure = DB::select(
            'CALL SP_CalcularValorInteresMora(?,?,?,?,?)', 
            array(
                $proyecto_id, 
                $reiniciarMora, 
                $transaccion,
                $usuarioId,
                $usuario
            ));
    }

    use HasFactory;
}