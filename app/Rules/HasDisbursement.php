<?php

namespace App\Rules;

use App\Models\Proyectos\Desembolso;
use Illuminate\Contracts\Validation\Rule;

class HasDisbursement implements Rule
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
        $desembolsos = Desembolso::where('proyecto_id', $value)
            ->where('desembolsosEstado', 1)
            ->count();
        return $desembolsos > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El proyecto seleccionado no tiene desembolsos hechos';
    }
}
