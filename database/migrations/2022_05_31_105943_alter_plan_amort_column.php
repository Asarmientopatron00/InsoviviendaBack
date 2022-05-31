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
        Schema::table('plan_amortizacion', function (Blueprint $table) {
            $table->renameColumn('plaAmoFechaVencimiento', 'plaAmoFechaVencimientoCuota');
            $table->renameColumn('plaAmoFechaUltimoPago', 'plaAmoFechaUltimoPagoCuota');
        });
        Schema::table('plan_amortizacion_def', function (Blueprint $table) {
            $table->renameColumn('plAmDeFechaVencimiento', 'plAmDeFechaVencimientoCuota');
            $table->renameColumn('plAmDeFechaUltimoPago', 'plAmDeFechaUltimoPagoCuota');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plan_amortizacion', function (Blueprint $table) {
            $table->renameColumn('plaAmoFechaVencimientoCuota', 'plaAmoFechaVencimiento');
            $table->renameColumn('plaAmoFechaUltimoPagoCuota', 'plaAmoFechaUltimoPago');
        });
        Schema::table('plan_amortizacion_def', function (Blueprint $table) {
            $table->renameColumn('plAmDeFechaVencimientoCuota', 'plAmDeFechaVencimiento');
            $table->renameColumn('plAmDeFechaUltimoPagoCuota', 'plAmDeFechaUltimoPago');
        });
    }
};
