<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->unsignedBigInteger('comuna_id')->nullable()->change();
            $table->unsignedBigInteger('barrio_id')->nullable()->change();
            $table->string('personasDireccion', 128)->nullable(false)->change();
            $table->string('personasNumeroEscritura', 128)->nullable()->change();
            $table->string('personasNotariaEscritura', 128)->nullable()->change();
            $table->dateTime('personasFechaEscritura')->nullable()->change();
            $table->string('personasIndicativoPC', 2)->nullable()->change();
            $table->unsignedBigInteger('eps_id')->nullable()->change();
            $table->string('personasVehiculo', 1)->nullable()->change();
            $table->string('personasTipoTrabajo', 2)->nullable(false)->change();
            $table->string('personasTipoContrato', 2)->nullable()->change();
            $table->unsignedBigInteger('ocupacion_id')->nullable(false)->change();
            $table->smallInteger('personasPuntajeProcredito')->nullable(false)->change();
            $table->smallInteger('personasPuntajeDatacredito')->nullable(false)->change();
            $table->unsignedBigInteger('departamento_correspondencia_id')->nullable()->change();
            $table->unsignedBigInteger('ciudad_correspondencia_id')->nullable()->change();
            $table->unsignedBigInteger('comuna_correspondencia_id')->nullable()->change();
            $table->unsignedBigInteger('barrio_correspondencia_id')->nullable()->change();
            $table->decimal('personasIngresosArriendo', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasIngresosSubsidios', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasIngresosPaternidad', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasIngresosTerceros', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasIngresosOtros', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasAportesArriendo', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasAportesSubsidios', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasAportesPaternidad', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasAportesTerceros', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasAportesOtros', $precision = 18, $scale = 2)->nullable()->change();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->unsignedBigInteger('comuna_id')->nullable(false)->change();
            $table->unsignedBigInteger('barrio_id')->nullable(false)->change();
            $table->string('personasDireccion', 128)->nullable()->change();
            $table->string('personasNumeroEscritura', 128)->nullable()->change();
            $table->string('personasNotariaEscritura', 128)->nullable()->change();
            $table->dateTime('personasFechaEscritura')->nullable()->change();
            $table->string('personasIndicativoPC', 2)->nullable()->change();
            $table->unsignedBigInteger('eps_id')->nullable()->change();
            $table->string('personasVehiculo', 1)->nullable()->change();
            $table->string('personasTipoTrabajo', 2)->nullable()->change();
            $table->string('personasTipoContrato', 2)->nullable()->change();
            $table->unsignedBigInteger('ocupacion_id')->nullable()->change();
            $table->smallInteger('personasPuntajeProcredito')->nullable(false)->change();
            $table->smallInteger('personasPuntajeDatacredito')->nullable(false)->change();
            $table->unsignedBigInteger('departamento_correspondencia_id')->nullable()->change();
            $table->unsignedBigInteger('ciudad_correspondencia_id')->nullable()->change();
            $table->unsignedBigInteger('comuna_correspondencia_id')->nullable()->change();
            $table->unsignedBigInteger('barrio_correspondencia_id')->nullable()->change();
            $table->decimal('personasIngresosArriendo', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasIngresosSubsidios', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasIngresosPaternidad', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasIngresosTerceros', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasIngresosOtros', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasAportesArriendo', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasAportesSubsidios', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasAportesPaternidad', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasAportesTerceros', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('personasAportesOtros', $precision = 18, $scale = 2)->nullable()->change();
        });
        
    }
};
