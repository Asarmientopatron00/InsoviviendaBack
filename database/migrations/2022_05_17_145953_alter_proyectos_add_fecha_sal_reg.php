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
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn('proyectosFechaIngresosReg');
            $table->date('proyectosFechaIngresoReg')->nullable()->after('proyectosFechaFirEscrituras');
            $table->date('proyectosFechaSalidaReg')->nullable()->after('proyectosFechaIngresoReg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->date('proyectosFechaIngresosReg')->nullable()->after('proyectosFechaFirEscrituras');
            $table->dropColumn('proyectosFechaIngresoReg');
            $table->dropColumn('proyectosFechaSalidaReg');
        });
    }
};
