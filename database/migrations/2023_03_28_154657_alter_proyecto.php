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
            $table->foreignId('asesor_gestion_cartera_id')->after('proyectosObservaciones')->nullable()->constrained('orientadores')->cascadeOnDelete();
            $table->string('proyectosObservacionesGestionC', 500)->after('asesor_gestion_cartera_id')->nullable();
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
            $table->dropForeign(['asesor_gestion_cartera_id']);
            $table->dropColumn('asesor_gestion_cartera_id');
            $table->dropColumn('proyectosObservacionesGestionC');
        });
    }
};
