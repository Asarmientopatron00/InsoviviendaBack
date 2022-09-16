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
        Schema::table('desembolsos', function (Blueprint $table) {
            $table->unsignedBigInteger('banco_id')->nullable()->change();
            $table->string('desembolsosTipoCuentaDes', 2)->nullable()->change();
            $table->string('desembolsosNumeroCuentaDes', 64)->nullable()->change();
            $table->string('desembolsosNumeroComEgreso', 64)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('desembolsos', function (Blueprint $table) {
            $table->unsignedBigInteger('banco_id')->nullable()->change();
            $table->string('desembolsosTipoCuentaDes', 2)->nullable()->change();
            $table->string('desembolsosNumeroCuentaDes', 64)->nullable()->change();
            $table->string('desembolsosNumeroComEgreso', 64)->nullable()->change();
        });
    }
};
