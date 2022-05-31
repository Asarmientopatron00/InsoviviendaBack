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
        Schema::create('plan_amortizacion_def', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->references('id')->on('proyectos');
            $table->bigInteger('plAmDeNumeroCuota');
            $table->dateTime('plAmDeFechaVencimiento');
            $table->decimal('plAmDeValorSaldoCapital', $precision = 18, $scale = 2);
            $table->decimal('plAmDeValorCapitalCuota', $precision = 18, $scale = 2);
            $table->decimal('plAmDeValorInteresCuota', $precision = 18, $scale = 2);
            $table->decimal('plAmDeValorSeguroCuota', $precision = 18, $scale = 2);
            $table->decimal('plAmDeValorInteresMora', $precision = 18, $scale = 2);
            $table->integer('plAmDeDiasMora')->default(0);
            $table->dateTime('plAmDeFechaUltimoPago')->nullable();
            $table->string('plAmDeCuotaCancelada',1)->default('N');
            $table->string('plAmDeEstadoPlanAmortizacion',3)->nullable();
            $table->boolean('plAmDeEstado')->default(true);

            // Auditoria datos
            $table->bigInteger('usuario_creacion_id');
            $table->string('usuario_creacion_nombre');
            $table->bigInteger('usuario_modificacion_id');
            $table->string('usuario_modificacion_nombre');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_amortizacion_def');
    }
};
