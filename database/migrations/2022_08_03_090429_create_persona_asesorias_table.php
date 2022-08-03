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
        Schema::create('personas_asesorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_identificacion_id')->references('id')->on('tipos_identificacion'); 
            $table->string('numero_documento', 128); 
            $table->string('nombre'); 
            $table->string('telefono', 128)->nullable(); 
            $table->string('celular', 128)->nullable(); 
            $table->string('direccion', 128)->nullable(); 
            $table->foreignId('departamento_id')->nullable()->references('id')->on('departamentos');
            $table->foreignId('ciudad_id')->nullable()->references('id')->on('ciudades');
            $table->string('observaciones', 128)->nullable(); 
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
        Schema::dropIfExists('personas_asesorias');
    }
};
