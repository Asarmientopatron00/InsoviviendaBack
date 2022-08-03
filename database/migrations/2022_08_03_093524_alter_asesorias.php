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
        Schema::table('orientaciones', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropColumn('persona_id');
            $table->foreignId('persona_asesoria_id')->after('orientacionesFechaOrientacion')->references('id')->on('personas_asesorias'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orientaciones', function (Blueprint $table) {
            $table->foreignId('persona_id')->after('orientacionesFechaOrientacion')->references('id')->on('personas');
            $table->dropForeign(['persona_asesoria_id']);
            $table->dropColumn('persona_asesoria_id');
        });
    }
};
