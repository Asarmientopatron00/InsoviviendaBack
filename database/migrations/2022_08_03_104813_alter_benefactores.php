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
        Schema::table('benefactores', function (Blueprint $table) {
            $table->unsignedBigInteger('pais_id')->nullable()->change();
            $table->unsignedBigInteger('departamento_id')->nullable()->change();
            $table->unsignedBigInteger('ciudad_id')->nullable()->change();
            $table->string('benefactoresDireccion', 128)->nullable()->change();
            $table->string('benefactoresNotas', 128)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('benefactores', function (Blueprint $table) {
            $table->unsignedBigInteger('pais_id')->nullable(false)->change();
            $table->unsignedBigInteger('departamento_id')->nullable(false)->change();
            $table->unsignedBigInteger('ciudad_id')->nullable(false)->change();
            $table->string('benefactoresDireccion', 128)->nullable(false)->change();
            $table->string('benefactoresNotas', 128)->nullable(false)->change();
        });
    }
};
