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
        $procedure = "DROP PROCEDURE IF EXISTS `SP_FamiliasCalcularAportes`;
        CREATE PROCEDURE SP_FamiliasCalcularAportes(
            IN FAMILIAID INT,
            IN TRANSACCION VARCHAR(30),
            IN USUARIO VARCHAR(30),
            IN USUARIOID INT
        )
        BEGIN
        
            -- VARIABLES
            DECLARE V_ERROR_MENSAJE VARCHAR(512) DEFAULT '';
            DECLARE V_VALOR_APORTES_FORMALES INT DEFAULT NULL;
            DECLARE V_VALOR_APORTES_INFORMALES INT DEFAULT NULL;
            DECLARE V_VALOR_APORTES_ARRIENDO INT DEFAULT NULL;
            DECLARE V_VALOR_APORTES_SUBSIDIOS INT DEFAULT NULL;
            DECLARE V_VALOR_APORTES_PATERNIDAD INT DEFAULT NULL;
            DECLARE V_VALOR_APORTES_TERCEROS INT DEFAULT NULL;
            DECLARE V_VALOR_APORTES_OTROS INT DEFAULT NULL;
            DECLARE V_IDENTIFICACION_FAMILIA VARCHAR(32) DEFAULT NULL;
        
            -- SUMAR APORTES INTEGRANTES
            SELECT SUM(PERSONASAPORTESFORMALES), 
                SUM(PERSONASAPORTESINFORMALES),
                SUM(PERSONASAPORTESARRIENDO),
                SUM(PERSONASAPORTESSUBSIDIOS),
                SUM(PERSONASAPORTESPATERNIDAD),
                SUM(PERSONASAPORTESTERCEROS),
                SUM(PERSONASAPORTESOTROS) 
            INTO V_VALOR_APORTES_FORMALES,
                V_VALOR_APORTES_INFORMALES,
                V_VALOR_APORTES_ARRIENDO,
                V_VALOR_APORTES_SUBSIDIOS,
                V_VALOR_APORTES_PATERNIDAD,
                V_VALOR_APORTES_TERCEROS, 
                V_VALOR_APORTES_OTROS
            FROM PERSONAS 
            WHERE FAMILIA_ID = FAMILIAID
            AND (	
                PERSONASCATEGORIAAPORTES = 'SO' 
                OR 
                PERSONASCATEGORIAAPORTES = 'AG'
            );
        
            IF V_VALOR_APORTES_FORMALES IS NULL THEN
                SET V_VALOR_APORTES_FORMALES = 0;
            END IF;
        
            IF V_VALOR_APORTES_INFORMALES IS NULL THEN
                SET V_VALOR_APORTES_INFORMALES = 0;
            END IF;
        
            IF V_VALOR_APORTES_ARRIENDO IS NULL THEN
                SET V_VALOR_APORTES_ARRIENDO = 0;
            END IF;
        
            IF V_VALOR_APORTES_SUBSIDIOS IS NULL THEN
                SET V_VALOR_APORTES_SUBSIDIOS = 0;
            END IF;
        
            IF V_VALOR_APORTES_PATERNIDAD IS NULL THEN
                SET V_VALOR_APORTES_PATERNIDAD = 0;
            END IF;
        
            IF V_VALOR_APORTES_TERCEROS IS NULL THEN
                SET V_VALOR_APORTES_TERCEROS = 0;
            END IF;
            
            IF V_VALOR_APORTES_OTROS IS NULL THEN
                SET V_VALOR_APORTES_OTROS = 0;
            END IF;
        
            -- ACTUALIAR INFORMACIÓN APORTES FAMILIA
            UPDATE FAMILIAS
            SET FAMILIASAPORTESFORMALES = V_VALOR_APORTES_FORMALES,
            FAMILIASAPORTESINFORMALES = V_VALOR_APORTES_INFORMALES,
            FAMILIASAPORTESARRIENDO = V_VALOR_APORTES_ARRIENDO,
            FAMILIASAPORTESSUBSIDIOS = V_VALOR_APORTES_SUBSIDIOS,
            FAMILIASAPORTESPATERNIDAD = V_VALOR_APORTES_PATERNIDAD,
            FAMILIASAPORTESTERCEROS = V_VALOR_APORTES_TERCEROS,
            FAMILIASAPORTESOTROS = V_VALOR_APORTES_OTROS
            WHERE ID = FAMILIAID;
        
            SELECT IDENTIFICACION_PERSONA 
            INTO V_IDENTIFICACION_FAMILIA 
            FROM FAMILIAS
            WHERE ID = FAMILIAID;
        
            SET V_ERROR_MENSAJE = CONCAT('Proceso terminó correctamente - Familia : ', V_IDENTIFICACION_FAMILIA);
        
            INSERT INTO AUDITORIA_PROCESOS
                (
                    AUDPROTRANSACCION,
                    AUDPROTIPO,
                    AUDPRONUMEROPROYECTO,
                    AUDPRODESCRIPCION,
                    AUDPROUSUARIOCREACIONID,
                    AUDPROUSUARIOCREACIONNOMBRE,
                    CREATED_AT,
                    UPDATED_AT
                )
            VALUES
                (
                    TRANSACCION,
                    'PROCESO',
                    NULL,
                    V_ERROR_MENSAJE,
                    USUARIOID,
                    USUARIO,
                    SYSDATE(),
                    SYSDATE()
            );
        
        END;";
        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
