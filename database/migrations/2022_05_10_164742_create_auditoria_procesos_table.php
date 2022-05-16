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
        Schema::create('auditoria_procesos', function (Blueprint $table) {
            $table->id();
            $table->string('audProTransaccion', 128);
            $table->string('audProTipo', 128);
            $table->string('audProNumeroProyecto', 32)->nullable();
            $table->string('audProDescripcion', 512);
            $table->bigInteger('audProUsuarioCreacionId');
            $table->string('audProUsuarioCreacionNombre', 128);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auditoria_procesos');
    }
};
