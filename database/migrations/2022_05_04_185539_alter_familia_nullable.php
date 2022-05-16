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
        Schema::table('familias', function (Blueprint $table) {
            $table->unsignedBigInteger('condicion_familia_id')->nullable()->change();
            $table->decimal('familiasAportesArriendo', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('familiasAportesSubsidios', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('familiasAportesPaternidad', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('familiasAportesTerceros', $precision = 18, $scale = 2)->nullable()->change();
            $table->decimal('familiasAportesOtros', $precision = 18, $scale = 2)->nullable()->change();
            $table->string('familiasObservaciones', 512)->nullable()->after('familiasEstado');
            $table->foreignId('tipo_familia_id')->after('identificacion_persona')->references('id')->on('tipos_familia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('familias', function (Blueprint $table) {
            $table->unsignedBigInteger('condicion_familia_id')->nullable(false)->change();
            $table->decimal('familiasAportesArriendo', $precision = 18, $scale = 2)->nullable(false)->change();
            $table->decimal('familiasAportesSubsidios', $precision = 18, $scale = 2)->nullable(false)->change();
            $table->decimal('familiasAportesPaternidad', $precision = 18, $scale = 2)->nullable(false)->change();
            $table->decimal('familiasAportesTerceros', $precision = 18, $scale = 2)->nullable(false)->change();
            $table->decimal('familiasAportesOtros', $precision = 18, $scale = 2)->nullable(false)->change();
            $table->dropColumn('familiasObservaciones');
            $table->dropForeign(['tipo_familia_id']);
            $table->dropColumn('tipo_familia_id');
        });
    }
};
