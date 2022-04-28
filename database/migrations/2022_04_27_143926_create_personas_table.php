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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('personasIdentificacion', 128)->unique();;
            $table->foreignId('tipo_identificacion_id')->references('id')->on('tipos_identificacion');
            $table->string('personasCategoriaAportes', 2);
            $table->string('personasNombres', 128);
            $table->string('personasPrimerApellido', 128);
            $table->string('personasSegundoApellido', 128)->nullable();
            $table->date('personasFechaNacimiento');
            $table->foreignId('pais_nacimiento_id')->references('id')->on('paises');
            $table->foreignId('departamento_nacimiento_id')->references('id')->on('departamentos');
            $table->foreignId('ciudad_nacimiento_id')->references('id')->on('ciudades');
            $table->string('personasGenero', 2);
            $table->foreignId('estado_civil_id')->references('id')->on('estados_civil');
            $table->foreignId('tipo_poblacion_id')->references('id')->on('tipos_poblacion');
            $table->foreignId('tipo_discapacidad_id')->references('id')->on('tipos_discapacidad');
            $table->string('personasSeguridadSocial', 2);
            $table->foreignId('eps_id')->references('id')->on('eps');
            $table->foreignId('grado_escolaridad_id')->references('id')->on('grados_escolaridad');
            $table->string('personasVehiculo', 1);
            $table->string('personasCorreo', 128)->nullable();
            $table->dateTime('personasFechaVinculacion');
            $table->foreignId('departamento_id')->references('id')->on('departamentos');
            $table->foreignId('ciudad_id')->references('id')->on('ciudades');
            $table->foreignId('comuna_id')->references('id')->on('comunas');
            $table->foreignId('barrio_id')->references('id')->on('barrios');
            $table->string('personasDireccion', 128)->nullable();
            $table->string('personasZona', 2);
            $table->string('personasEstrato', 1);
            $table->string('personasTelefonoCasa', 128)->nullable();
            $table->string('personasTelefonoCelular', 128)->nullable();
            $table->foreignId('tipo_vivienda_id')->references('id')->on('tipos_vivienda');
            $table->string('personasTipoPropiedad', 2);
            $table->string('personasNumeroEscritura', 128);
            $table->string('personasNotariaEscritura', 128);
            $table->dateTime('personasFechaEscritura');
            $table->string('personasIndicativoPC', 2);
            $table->smallInteger('personasNumeroHabitaciones');
            $table->smallInteger('personasNumeroBanos');
            $table->foreignId('tipo_techo_id')->references('id')->on('tipos_techo');
            $table->foreignId('tipo_piso_id')->references('id')->on('tipos_piso');
            $table->foreignId('tipo_division_id')->references('id')->on('tipos_division');
            $table->string('personasSala', 1);
            $table->string('personasComedor', 1);
            $table->string('personasCocina', 1);
            $table->string('personasPatio', 1);
            $table->string('personasTerraza', 1);
            $table->foreignId('ocupacion_id')->references('id')->on('ocupaciones');
            $table->string('personasTipoTrabajo', 2);
            $table->string('personasTipoContrato', 2);
            $table->string('personasNombreEmpresa', 128)->nullable();
            $table->string('personasTelefonoEmpresa', 128)->nullable();
            $table->smallInteger('personasPuntajeProcredito')->nullable();
            $table->smallInteger('personasPuntajeDatacredito')->nullable();
            $table->foreignId('departamento_correspondencia_id')->references('id')->on('departamentos');
            $table->foreignId('ciudad_correspondencia_id')->references('id')->on('ciudades');
            $table->foreignId('comuna_correspondencia_id')->references('id')->on('comunas');
            $table->foreignId('barrio_correspondencia_id')->references('id')->on('barrios');
            $table->string('personasCorDireccion', 128)->nullable();
            $table->string('personasCorTelefono', 128)->nullable();
            $table->decimal('personasIngresosFormales', $precision = 18, $scale = 2);
            $table->decimal('personasIngresosInformales', $precision = 18, $scale = 2);
            $table->decimal('personasIngresosArriendo', $precision = 18, $scale = 2);
            $table->decimal('personasIngresosSubsidios', $precision = 18, $scale = 2);
            $table->decimal('personasIngresosPaternidad', $precision = 18, $scale = 2);
            $table->decimal('personasIngresosTerceros', $precision = 18, $scale = 2);
            $table->decimal('personasIngresosOtros', $precision = 18, $scale = 2);
            $table->decimal('personasAportesFormales', $precision = 18, $scale = 2);
            $table->decimal('personasAportesInformales', $precision = 18, $scale = 2);
            $table->decimal('personasAportesArriendo', $precision = 18, $scale = 2);
            $table->decimal('personasAportesSubsidios', $precision = 18, $scale = 2);
            $table->decimal('personasAportesPaternidad', $precision = 18, $scale = 2);
            $table->decimal('personasAportesTerceros', $precision = 18, $scale = 2);
            $table->decimal('personasAportesOtros', $precision = 18, $scale = 2);
            $table->string('personasRefNombre1', 128)->nullable();
            $table->string('personasRefTelefono1', 128)->nullable();
            $table->string('personasRefNombre2', 128)->nullable();
            $table->string('personasRefTelefono2', 128)->nullable();
            $table->string('personasObservaciones', 128)->nullable();
            $table->string('personasEstadoTramite', 2);
            $table->string('personasEstadoRegistro', 2);
            // $table->foreignId('familia_id')->nullable()->references('id')->on('familias');
            
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
        Schema::dropIfExists('personas');
    }
};
