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
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('pagosObservacionesAnulacion', 128)->nullable()->after('pagosEstado');
            $table->string('pagosObservacionesPagoEspecial', 128)->nullable()->after('pagosObservacionesAnulacion');
            $table->string('pagosTipo', 1)->after('pagosObservacionesPagoEspecial')->default('N');
        });
        Schema::table('pagos_detalle', function (Blueprint $table) {
            $table->decimal('pagDetValorInteresMoraCondonado', $precision = 18, $scale = 2)->default(0)->after('pagDetDiasMora');
            $table->decimal('pagDetValorSeguroCuotaCondonado', $precision = 18, $scale = 2)->default(0)->after('pagDetValorInteresMoraCondonado');
            $table->decimal('pagDetValorInteresCuotaCondonado', $precision = 18, $scale = 2)->default(0)->after('pagDetValorSeguroCuotaCondonado');
            $table->decimal('pagDetValorCapitalCuotaCondonado', $precision = 18, $scale = 2)->default(0)->after('pagDetValorInteresCuotaCondonado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('pagosObservacionesAnulacion');
            $table->dropColumn('pagosObservacionesPagoEspecial');
            $table->dropColumn('pagosTipo');
        });

        Schema::table('pagos_detalle', function (Blueprint $table) {
            $table->dropColumn('pagDetValorInteresMoraCondonado');
            $table->dropColumn('pagDetValorSeguroCuotaCondonado');
            $table->dropColumn('pagDetValorInteresCuotaCondonado');
            $table->dropColumn('pagDetValorCapitalCuotaCondonado');
        });
    }
};
