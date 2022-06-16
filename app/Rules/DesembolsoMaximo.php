<?php

namespace App\Rules;

use App\Models\Proyectos\Proyecto;
use Illuminate\Contracts\Validation\Rule;

class DesembolsoMaximo implements Rule
{
    public $proyecto_id;
    public $desembolso_id;
    public $desembolso_valor;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($proyecto_id, $desembolso_id, $desembolso_valor)
    {
        $this->proyecto_id = $proyecto_id;
        $this->desembolso_id = $desembolso_id;
        $this->desembolso_valor = floatval($desembolso_valor);
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
        $proyecto = Proyecto::find($this->proyecto_id);
        $desembolsos = $proyecto->desembolsos;
        $desembolsosHechos = 0;
        foreach($desembolsos as $desembolso){
            if($desembolso->desembolsosEstado === 1 && $desembolso->id != $this->desembolso_id){
                $desembolsosHechos = $desembolsosHechos + $desembolso->desembolsosValorDesembolso;
            }
        }
        $nuevoValor = $desembolsosHechos + $this->desembolso_valor;
        return $nuevoValor <= $proyecto->proyectosValorAprobado??0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El valor de los desembolsos excede el valor aprobado del proyecto.';
    }
}
