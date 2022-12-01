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
        Schema::table('donaciones', function (Blueprint $table) {
            $table->string('donacionesNumeroDocumentoTercero', 128)->after('persona_id');
            $table->string('donacionesNombreTercero', 128)->after('donacionesNumeroDocumentoTercero');
            $table->unsignedBigInteger('persona_id')->nullable()->change();
            $table->dropForeign(['benefactor_id']);
            $table->dropColumn('benefactor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('donaciones', function (Blueprint $table) {
            $table->foreignId('benefactor_id')->nullable()->constrained('benefactores');
            $table->dropColumn('donacionesNumeroDocumentoTercero');
            $table->dropColumn('donacionesNombreTercero');
        });
    }
};
