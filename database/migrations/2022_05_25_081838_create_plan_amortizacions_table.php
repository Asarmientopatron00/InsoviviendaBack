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
        Schema::create('plan_amortizacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->references('id')->on('proyectos');
            $table->bigInteger('plaAmoNumeroCuota');
            $table->dateTime('plaAmoFechaVencimiento');
            $table->decimal('plaAmoValorSaldoCapital', $precision = 18, $scale = 2);
            $table->decimal('plaAmoValorCapitalCuota', $precision = 18, $scale = 2);
            $table->decimal('plaAmoValorInteresCuota', $precision = 18, $scale = 2);
            $table->decimal('plaAmoValorSeguroCuota', $precision = 18, $scale = 2);
            $table->decimal('plaAmoValorInteresMora', $precision = 18, $scale = 2);
            $table->integer('plaAmoDiasMora')->default(0);
            $table->dateTime('plaAmoFechaUltimoPago')->nullable();
            $table->string('plaAmoCuotaCancelada',1)->default('N');
            $table->string('plaAmoEstadoPlanAmortizacion',3)->nullable();
            $table->boolean('plaAmoEstado')->default(true);

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
        Schema::dropIfExists('plan_amortizacion');
    }
};
