<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use App\Models\Proyectos\PlanAmortizacionDefinitivo;

class UpToDateProject implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $proximaCuotaAPagar = PlanAmortizacionDefinitivo::where('proyecto_id', $value)
            ->where('plAmDeCuotaCancelada', 'N')
            ->first();
        
        if(!$proximaCuotaAPagar) return false;

        $today = Carbon::now()->format('Y-m-d');
        
        return $proximaCuotaAPagar->plAmDeFechaVencimientoCuota >= $today;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'No es posible reajustar fecha de pagos a proyectos en mora';
    }
}
