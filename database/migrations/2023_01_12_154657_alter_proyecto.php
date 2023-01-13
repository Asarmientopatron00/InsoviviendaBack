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
            $table->foreignId('proyecto_unificado_id')->after('proyectosObservaciones')->nullable()->constrained('proyectos')->cascadeOnDelete();
            $table->decimal('proyectosValorSaldoUnificado', $precision = 18, $scale = 2)->after('proyecto_unificado_id')->nullable();
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
            $table->dropForeign(['proyecto_unificado_id']);
            $table->dropColumn('proyecto_unificado_id');
            $table->dropColumn('proyectosValorSaldoUnificado');
        });
    }
};
