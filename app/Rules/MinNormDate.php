<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Proyectos\PlanAmortizacionDefinitivo;

class MinNormDate implements Rule
{
    public $proyecto_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($proyecto_id)
    {
        $this->proyecto_id = $proyecto_id;
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
        $ultimaCuotaPagada = PlanAmortizacionDefinitivo::where('proyecto_id', $this->proyecto_id)
            ->where('plAmDeCuotaCancelada', 'S')
            ->orderBy('plAmDeNumeroCuota', 'desc')
            ->first()
            ??
            PlanAmortizacionDefinitivo::where('proyecto_id', $this->proyecto_id)
            ->where('plAmDeCuotaCancelada', 'N')
            ->first()
            ??
            '2099-01-01';
        
        return $ultimaCuotaPagada->plAmDeFechaVencimientoCuota <= $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'La fecha de normalización no debe ser inferior a la fecha de vencimiento de pago próxima';
    }
}
