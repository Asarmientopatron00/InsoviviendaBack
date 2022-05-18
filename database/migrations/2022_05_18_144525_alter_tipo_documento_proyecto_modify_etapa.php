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
        Schema::table('tipos_documentos_proyecto', function (Blueprint $table) {
            $table->string('tiDoPrEtapa',3)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tipos_documentos_proyecto', function (Blueprint $table) {
            $table->string('tiDoPrEtapa',128)->change();
        });
    }
};
