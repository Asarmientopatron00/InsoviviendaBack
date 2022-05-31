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
        Schema::create('desembolsos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->references('id')->on('proyectos');
            $table->date('desembolsosFechaDesembolso');
            $table->decimal('desembolsosValorDesembolso', $precision = 18, $scale = 2);
            $table->date('desembolsosFechaNormalizacionP');
            $table->string('desembolsosDescripcionDes', 128);
            $table->foreignId('banco_id')->references('id')->on('bancos');
            $table->string('desembolsosTipoCuentaDes', 2);
            $table->string('desembolsosNumeroCuentaDes', 64);
            $table->string('desembolsosNumeroComEgreso', 64);
            $table->boolean('desembolsosPlanDefinitivo')->default(false);
            $table->boolean('desembolsosEstado')->default(true);

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
        Schema::dropIfExists('desembolsos');
    }
};
