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
        Schema::create('familias', function (Blueprint $table) {
            $table->id();
            $table->string('identificacion_persona',128)->references('personasIdentificacion')->on('personas')->unique();
            $table->foreignId('condicion_familia_id')->references('id')->on('condiciones_familia');
            $table->dateTime('familiasFechaVisitaDomici')->nullable();
            $table->decimal('familiasAportesFormales', $precision = 18, $scale = 2);
            $table->decimal('familiasAportesInformales', $precision = 18, $scale = 2);
            $table->decimal('familiasAportesArriendo', $precision = 18, $scale = 2);
            $table->decimal('familiasAportesSubsidios', $precision = 18, $scale = 2);
            $table->decimal('familiasAportesPaternidad', $precision = 18, $scale = 2);
            $table->decimal('familiasAportesTerceros', $precision = 18, $scale = 2);
            $table->decimal('familiasAportesOtros', $precision = 18, $scale = 2);
            $table->decimal('familiasEgresosDeudas', $precision = 18, $scale = 2);
            $table->decimal('familiasEgresosEducacion', $precision = 18, $scale = 2);
            $table->decimal('familiasEgresosSalud', $precision = 18, $scale = 2);
            $table->decimal('familiasEgresosTransporte', $precision = 18, $scale = 2);
            $table->decimal('familiasEgresosSerPublicos', $precision = 18, $scale = 2);
            $table->decimal('familiasEgresosAlimentacion', $precision = 18, $scale = 2);
            $table->decimal('familiasEgresosVivienda', $precision = 18, $scale = 2);
            $table->boolean('familiasEstado')->default(true);
            // Auditoria
            $table->bigInteger('usuario_creacion_id');
            $table->string('usuario_creacion_nombre',128);
            $table->bigInteger('usuario_modificacion_id');
            $table->string('usuario_modificacion_nombre',128);
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
        Schema::dropIfExists('familias');
    }
};
