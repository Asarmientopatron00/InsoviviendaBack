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
        Schema::create('orientaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_orientacion_id')->references('id')->on('tipos_orientacion');
            $table->foreignId('orientador_id')->references('id')->on('orientadores');
            $table->date('orientacionesFechaOrientacion');
            $table->foreignId('persona_id')->references('id')->on('personas');
            $table->string('orientacionesSolicitud', 512)->nullable();
            $table->string('orientacionesNota', 512)->nullable();
            $table->string('orientacionesRespuesta', 512)->nullable();
            $table->boolean('estado')->default(true);

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
        Schema::dropIfExists('orientaciones');
    }
};
