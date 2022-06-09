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
        Schema::create('pagos_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->references('id')->on('proyectos');
            $table->foreignId('pago_id')->references('id')->on('pagos');
            $table->date('pagDetFechaPago');
            $table->bigInteger('pagDetNumeroCuota');
            $table->date('pagDetFechaVencimientoCuota');
            $table->decimal('pagDetValorCapitalCuotaPagado', $precision = 18, $scale = 2)->default(0);
            $table->decimal('pagDetValorSaldoCuotaPagado', $precision = 18, $scale = 2)->default(0);
            $table->decimal('pagDetValorInteresCuotaPagado', $precision = 18, $scale = 2)->default(0);
            $table->decimal('pagDetValorSeguroCuotaPagado', $precision = 18, $scale = 2)->default(0);
            $table->decimal('pagDetValorInteresMoraPagado', $precision = 18, $scale = 2)->default(0);
            $table->integer('pagDetDiasMora')->default(0);
            $table->boolean('pagDetEstado')->default(true);

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
        Schema::dropIfExists('pagos_detalle');
    }
};
