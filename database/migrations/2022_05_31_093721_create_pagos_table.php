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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->references('id')->on('proyectos');
            $table->date('pagosFechaPago');
            $table->decimal('pagosValorTotalPago', $precision = 18, $scale = 2);
            $table->string('pagosDescripcionPago', 64);
            $table->boolean('pagosEstado')->default(true);

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
        Schema::dropIfExists('pagos');
    }
};
