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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->references('id')->on('personas');
            $table->string('proyectosEstadoProyecto',3);
            $table->dateTime('proyectosFechaSolicitud');
            $table->string('proyectosTipoProyecto',3);
            $table->foreignId('tipo_programa_id')->references('id')->on('tipos_programa');
            $table->string('proyectosRemitido',1);
            $table->foreignId('remitido_id')->nullable()->references('id')->on('personas');
            $table->foreignId('pais_id')->nullable()->references('id')->on('paises');
            $table->foreignId('departamento_id')->nullable()->references('id')->on('departamentos');
            $table->foreignId('ciudad_id')->nullable()->references('id')->on('ciudades');
            $table->foreignId('comuna_id')->nullable()->references('id')->on('comunas');
            $table->foreignId('barrio_id')->nullable()->references('id')->on('barrios');
            $table->string('proyectosZona', 2)->nullable();
            $table->string('proyectosDireccion', 128)->nullable();
            $table->string('proyectosVisitaDomiciliaria',1)->nullable();
            $table->date('proyectosFechaVisitaDom')->nullable();
            $table->string('proyectosPagoEstudioCre',1)->nullable();
            $table->string('proyectosReqLicenciaCon',1)->nullable();
            $table->date('proyectosFechaInicioEstudio')->nullable();
            $table->date('proyectosFechaAproRec')->nullable();
            $table->date('proyectosFechaEstInicioObr')->nullable();
            $table->decimal('proyectosValorProyecto', $precision = 18, $scale = 2)->nullable();
            $table->decimal('proyectosValorSolicitud', $precision = 18, $scale = 2)->nullable();
            $table->decimal('proyectosValorRecursosSol', $precision = 18, $scale = 2)->nullable();
            $table->decimal('proyectosValorSubsidios', $precision = 18, $scale = 2)->nullable();
            $table->decimal('proyectosValorDonaciones', $precision = 18, $scale = 2)->nullable();
            $table->decimal('proyectosValorCuotaAprobada', $precision = 18, $scale = 2)->nullable();
            $table->decimal('proyectosValorCapPagoMen', $precision = 18, $scale = 2)->nullable();
            $table->decimal('proyectosValorAprobado', $precision = 18, $scale = 2)->nullable();
            $table->decimal('proyectosValorSeguroVida', $precision = 18, $scale = 2)->nullable();
            $table->decimal('proyectosTasaInteresNMV', $precision = 9, $scale = 6)->nullable();
            $table->decimal('proyectosTasaInteresEA', $precision = 9, $scale = 6)->nullable();
            $table->smallInteger('proyectosNumeroCuotas')->nullable();
            $table->foreignId('banco_id')->nullable()->references('id')->on('bancos');
            $table->string('proyectosTipoCuentaRecaudo',2)->nullable();
            $table->string('proyectosNumCuentaRecaudo',128)->nullable();
            $table->string('proyectosEstadoFormalizacion',2)->nullable();
            $table->date('proyectosFechaAutNotaria')->nullable();
            $table->date('proyectosFechaFirEscrituras')->nullable();
            $table->date('proyectosFechaIngresosReg')->nullable();
            $table->string('proyectosAutorizacionDes',1)->nullable();
            $table->date('proyectosFechaAutDes')->nullable();
            $table->date('proyectosFechaCancelacion')->nullable();
            $table->foreignId('orientador_id')->nullable()->references('id')->on('orientadores');
            $table->string('proyectosObservaciones',128)->nullable();
            // Auditoria
            $table->bigInteger('usuario_creacion_id');
            $table->string('usuario_creacion_nombre',128);
            $table->bigInteger('usuario_modificacion_id');
            $table->string('usuario_modificacion_nombre',128);
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
        Schema::dropIfExists('proyectos');
    }
};
