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
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn('personasParentesco')->after('estado_civil_id');
            $table->foreignId('tipo_parentesco_id')->after('estado_civil_id')->references('id')->on('tipos_parentesco')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('personasParentesco', 2)->after('estado_civil_id');
            $table->dropForeign(['tipo_parentesco_id']);
            $table->dropColumn('tipo_parentesco_id');
        });
    }
};
