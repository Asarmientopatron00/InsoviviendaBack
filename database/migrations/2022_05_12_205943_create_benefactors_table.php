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
        Schema::create('benefactores', function (Blueprint $table) {
            $table->id();
            $table->string('benefactoresIdentificacion', 128)->unique();
            $table->string('benefactoresNombres', 128);
            $table->string('benefactoresPrimerApellido', 128);
            $table->string('benefactoresSegundoApellido', 128)->nullable();
            $table->foreignId('tipo_benefactor_id')->references('id')->on('tipos_benefactor');
            $table->string('benefactoresNombrePerContacto', 128)->nullable();
            $table->foreignId('benefactor_id')->nullable()->references('id')->on('benefactores');
            $table->foreignId('pais_id')->references('id')->on('paises');
            $table->foreignId('departamento_id')->references('id')->on('departamentos');
            $table->foreignId('ciudad_id')->references('id')->on('ciudades');
            $table->foreignId('comuna_id')->nullable()->references('id')->on('comunas');
            $table->foreignId('barrio_id')->nullable()->references('id')->on('barrios');
            $table->string('benefactoresDireccion', 128);
            $table->string('benefactoresTelefonoFijo', 128)->nullable();
            $table->string('benefactoresTelefonoCelular', 128)->nullable();
            $table->string('benefactoresCorreo', 128);
            $table->string('benefactoresNotas', 512);
            $table->boolean('estado')->default(true);

            // Auditoria datos
            $table->bigInteger('usuario_creacion_id');
            $table->string('usuario_creacion_nombre');
            $table->bigInteger('usuario_modificacion_id');
            $table->string('usuario_modificacion_nombre');
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
        Schema::dropIfExists('benefactores');
    }
};
