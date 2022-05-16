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
        Schema::create('donaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->references('id')->on('personas');
            $table->foreignId('benefactor_id')->references('id')->on('benefactores');
            $table->date('donacionesFechaDonacion');
            $table->foreignId('tipo_donacion_id')->references('id')->on('tipos_donacion');
            $table->decimal('donacionesValorDonacion', $precision = 18, $scale = 2);
            $table->string('donacionesEstadoDonacion',2);
            $table->foreignId('forma_pago_id')->references('id')->on('formas_pago');
            $table->string('donacionesNumeroCheque',128)->nullable();
            $table->foreignId('banco_id')->nullable()->references('id')->on('bancos');
            $table->string('donacionesNumeroRecibo',128);
            $table->date('donacionesFechaRecibo');
            $table->string('donacionesNotas',512);
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
        Schema::dropIfExists('donaciones');
    }
};
