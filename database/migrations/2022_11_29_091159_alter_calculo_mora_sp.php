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
    public function up(){
    $procedure1 = "DROP PROCEDURE IF EXISTS `SP_CalcularValorInteresMora`;
        CREATE PROCEDURE SP_CalcularValorInteresMora 
        (
            IN P_NUMEROPROYECTO INT,
            IN P_FECHA_EJECUCION DATE,
            IN P_REINICIARMORA TINYINT(1),
            IN P_TRANSACCION VARCHAR(30),
            IN P_USUARIOID INT,
            IN P_USUARIO VARCHAR(128)
        )
        sp: BEGIN
        
            -- VARIABLES.
            -- DECLARE P_FECHA_EJECUCION DATE DEFAULT SYSDATE();
            DECLARE V_ERROR_MENSAJE VARCHAR(512) DEFAULT '';
            DECLARE V_NUMERO_PROYECTO INT DEFAULT 0;
            DECLARE V_NUMERO_CUOTA INT DEFAULT 0;
            DECLARE V_FECHA_VENCIMIENTO_CUOTA DATETIME DEFAULT NULL;
            DECLARE V_VALOR_CAPITAL_CUOTA DECIMAL(18,5) DEFAULT 0;
            DECLARE V_DIAS_DE_MORA INT DEFAULT NULL;
            DECLARE V_VALOR_INTERES_MORA DECIMAL(18,5) DEFAULT NULL;
            DECLARE V_DIAS_GRACIA_CALCULO_MORA INT DEFAULT NULL;
            DECLARE V_INTERES_CALCULO_MORA DECIMAL(9, 5) DEFAULT NULL;
            DECLARE V_FECHA_ULTIMO_CALCULO_MORA DATE DEFAULT NULL;
            DECLARE V_FECHA_FINAL_DIAS_GRACIA DATE DEFAULT NULL;
            DECLARE V_DIAS_INTERESES_PERDIDOS INT DEFAULT NULL;
            DECLARE V_INTERES_CALCULADO DECIMAL(9, 5) DEFAULT NULL;
            DECLARE V_EXISTE_CONFIGURACION SMALLINT DEFAULT 0;
            DECLARE FINISHED INT DEFAULT 0;
        
        
            -- PROCESAR CALCULO VALOR INTERES MORA
        
            -- OBTENER PARAMETRO DE DÍAS DE GRACIA
            
            SELECT VALOR_PARAMETRO 
            INTO V_DIAS_GRACIA_CALCULO_MORA
            FROM PARAMETROS_CONSTANTES 
            WHERE CODIGO_PARAMETRO = 'DIAS_GRACIA_CALCULO_MORA';
        
            IF V_DIAS_GRACIA_CALCULO_MORA IS NULL THEN
                SET V_DIAS_GRACIA_CALCULO_MORA = 1;
            END IF;
        
            -- OBTENER PARAMETRO INTERES DE MORA
            SELECT VALOR_PARAMETRO
            INTO V_INTERES_CALCULO_MORA 
            FROM PARAMETROS_CONSTANTES 
            WHERE CODIGO_PARAMETRO = 'INTERES_CALCULO_MORA';
        
            IF V_INTERES_CALCULO_MORA IS NULL THEN
                SET V_ERROR_MENSAJE = 'El porcentaje de interés para el cálculo de mora es obligatorio';
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
                        P_TRANSACCION,
                        'ERROR',
                        NULL,
                        V_ERROR_MENSAJE,
                        P_USUARIOID,
                        P_USUARIO,
                        SYSDATE(),
                        SYSDATE()
                    ); 
                LEAVE sp;
            END IF;
        
            -- SE PASA DE PORCENTUAL A DECIMAL
            SET V_INTERES_CALCULO_MORA = V_INTERES_CALCULO_MORA / 100;
        
            -- OBTENER PARAMETRO FECHA DE ÚLTIMA EJECUCIÓN PROCESO CALCULO MORA
            SELECT CONVERT(VALOR_PARAMETRO, DATE)
            INTO V_FECHA_ULTIMO_CALCULO_MORA			
            FROM PARAMETROS_CONSTANTES 
            WHERE CODIGO_PARAMETRO = 'FECHA_ULTIMA_EJECUCION_CALCULO_MORA';
        
            IF V_FECHA_ULTIMO_CALCULO_MORA IS NOT NULL THEN
                SET V_EXISTE_CONFIGURACION = 1;
                IF DATEDIFF(V_FECHA_ULTIMO_CALCULO_MORA,P_FECHA_EJECUCION) = 0 AND P_NUMEROPROYECTO IS NULL AND P_REINICIARMORA = 1 THEN
                    SET V_ERROR_MENSAJE = 'No se puede ejecutar el cálculo del valor de interés por mora dos veces el mismo día';
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
                            P_TRANSACCION,
                            'ERROR',
                            NULL,
                            V_ERROR_MENSAJE,
                            P_USUARIOID,
                            P_USUARIO,
                            SYSDATE(),
                            SYSDATE()
                        ); 
                    LEAVE sp;
                END IF;
            ELSE
                IF V_FECHA_ULTIMO_CALCULO_MORA IS NULL THEN
                    SET V_FECHA_ULTIMO_CALCULO_MORA = P_FECHA_EJECUCION;
                END IF;
            END IF;
        
            -- SE SELECCIONAN LAS CUOTAS PENDIENTES POR PAGAR
            -- IF P_NUMEROPROYECTO IS NOT NULL AND P_NUMEROPROYECTO <> 0 THEN
            BEGIN
                DECLARE CURCUOTASPENDIENTES CURSOR FOR
                SELECT PROYECTO_ID,
                    PLAMDENUMEROCUOTA, 
                    PLAMDEFECHAVENCIMIENTOCUOTA, 
                    PLAMDEVALORCAPITALCUOTA
                FROM PLAN_AMORTIZACION_DEF
                WHERE CASE WHEN P_NUMEROPROYECTO IS NULL THEN 1 = 1 ELSE PROYECTO_ID = P_NUMEROPROYECTO END
                AND PLAMDEFECHAVENCIMIENTOCUOTA <= P_FECHA_EJECUCION
                AND PLAMDECUOTACANCELADA = 'N'
                ORDER BY PROYECTO_ID, PLAMDENUMEROCUOTA;
            -- ELSE
                -- DECLARE CURCUOTASPENDIENTES CURSOR FOR 
                -- SELECT PROYECTO_ID,
                -- 	PLAMDENUMEROCUOTA, 
                -- 	PLAMDEFECHAVENCIMIENTOCUOTA, 
                -- 	PLAMDEVALORCAPITALCUOTA
                -- FROM PLAN_AMORTIZACION_DEF
                -- WHERE PLAMDEFECHAVENCIMIENTOCUOTA <= P_FECHA_EJECUCION
                -- AND PLAMDECUOTACANCELADA = 'N'
                -- ORDER BY PROYECTO_ID, PLAMDENUMEROCUOTA;
            -- END IF;
        
                DECLARE CONTINUE HANDLER 
                FOR NOT FOUND SET FINISHED = 1;
        
                OPEN CURCUOTASPENDIENTES;
        
                GETCUOTAS: LOOP
        
                    FETCH CURCUOTASPENDIENTES INTO 
                    V_NUMERO_PROYECTO, V_NUMERO_CUOTA,
                    V_FECHA_VENCIMIENTO_CUOTA, V_VALOR_CAPITAL_CUOTA;
        
                    IF FINISHED = 1 THEN 
                        LEAVE GETCUOTAS; 
                    END IF;
        
                    -- PROCESAR CUOTAS PENDIENTES
                    -- REINICIAR EL CALCULO DE LA MORA PARA LAS NUEVAS CUOTAS, LUEGO DE REGENERAR PAGOS
                    IF P_REINICIARMORA = 1 THEN
                        SELECT DATE_ADD(V_FECHA_VENCIMIENTO_CUOTA, INTERVAL -1 DAY) INTO V_FECHA_ULTIMO_CALCULO_MORA;
                    END IF;
        
                    -- SE OBTIENE LA FECHA DEL ÚLTIMO DÍA DE GRACIA
                    SELECT DATE_ADD(V_FECHA_VENCIMIENTO_CUOTA, INTERVAL V_DIAS_GRACIA_CALCULO_MORA DAY) INTO V_FECHA_FINAL_DIAS_GRACIA;
        
                    -- SE VERIFICA QUE SE CUMPLAN LOS DÍAS DE GRACIA PARA PROCESAR
                    IF DATEDIFF(P_FECHA_EJECUCION, V_FECHA_FINAL_DIAS_GRACIA) > 0 THEN
                        -- PASADOS LOS DÍAS GRACIA, SE COBRA EL INTERES DESDE EL DÍA DE VENCIMIENTO (INCLUIDO)
                        IF DATEDIFF(P_FECHA_EJECUCION, V_FECHA_FINAL_DIAS_GRACIA) = 1 THEN
                            SET V_DIAS_DE_MORA = V_DIAS_GRACIA_CALCULO_MORA + 1;
                        ELSE
                            -- SI LA ÚLTIMA EJECUCIÓN FUE EL DÍA ANTERIOR, NO SE PERDIERON INTERESES
                            IF DATEDIFF(P_FECHA_EJECUCION, V_FECHA_ULTIMO_CALCULO_MORA) IN (0, 1) THEN
                                SET V_DIAS_DE_MORA = 1;
                            ELSE
                                -- SI LA ÚLTIMA FECHA DE EJECUCIÓN FUE DESPUES DEL PERIODO DE GRACIA
                                -- SE CALCULAN LOS DÍAS PERDIDOS CON RESPECTO AL DÍA QUE SE EJECUTO
                                -- EL PROCESO POR ÚLTIMA VEZ (DÍA NO INCLUIDO)
                                IF DATEDIFF(V_FECHA_ULTIMO_CALCULO_MORA, V_FECHA_FINAL_DIAS_GRACIA) >= 0 THEN
                                    SELECT DATEDIFF(P_FECHA_EJECUCION, V_FECHA_ULTIMO_CALCULO_MORA) - 1 INTO V_DIAS_INTERESES_PERDIDOS;
                                -- SI LA ÚLTIMA FECHA DE EJECUCIÓN FUE ANTES DEL PERIODO DE GRACIA
                                -- SE CALCULAN LOS DÍAS PERDIDOS CON RESPECTO A LA FECHA DE 
                                -- VENCIMIENTO DE LA CUOTA (DÍA NO INCLUIDO)
                                ELSE
                                    SELECT DATEDIFF(P_FECHA_EJECUCION, V_FECHA_VENCIMIENTO_CUOTA) - 1 INTO V_DIAS_INTERESES_PERDIDOS;
                                END IF;
        
                                SET V_DIAS_DE_MORA = V_DIAS_INTERESES_PERDIDOS + 1;
                            END IF;
                        END IF;
                    
                        -- CALCULO DEL VALOR DEL INTERES DIARIO
                        SET V_INTERES_CALCULADO = (V_INTERES_CALCULO_MORA / 30) * V_DIAS_DE_MORA;
                        -- SET V_VALOR_INTERES_MORA = V_VALOR_CAPITAL_CUOTA * V_INTERES_CALCULADO
                        SET V_VALOR_INTERES_MORA = ROUND((V_VALOR_CAPITAL_CUOTA * V_INTERES_CALCULADO),0);
        
                        IF V_DIAS_DE_MORA IS NOT NULL AND V_VALOR_INTERES_MORA IS NOT NULL THEN
                            IF P_REINICIARMORA = 1 THEN
                                UPDATE PLAN_AMORTIZACION_DEF
                                SET PLAMDEVALORINTERESMORA = V_VALOR_INTERES_MORA,
                                PLAMDEDIASMORA = V_DIAS_DE_MORA
                                WHERE PROYECTO_ID = V_NUMERO_PROYECTO
                                AND PLAMDENUMEROCUOTA = V_NUMERO_CUOTA;
                            ELSE
                                UPDATE PLAN_AMORTIZACION_DEF
                                SET PLAMDEVALORINTERESMORA = PLAMDEVALORINTERESMORA + V_VALOR_INTERES_MORA,
                                PLAMDEDIASMORA = PLAMDEDIASMORA + V_DIAS_DE_MORA
                                WHERE PROYECTO_ID = V_NUMERO_PROYECTO
                                AND PLAMDENUMEROCUOTA = V_NUMERO_CUOTA;
                            END IF;
                        END IF;
                    END IF;
                                
                END LOOP GETCUOTAS;
                
                CLOSE CURCUOTASPENDIENTES;
            END; 
        
            IF P_NUMEROPROYECTO IS NULL OR P_NUMEROPROYECTO = '' THEN
                IF V_EXISTE_CONFIGURACION = 1 THEN
                    UPDATE PARAMETROS_CONSTANTES 
                    SET VALOR_PARAMETRO = CONVERT(P_FECHA_EJECUCION, CHAR) 
                    WHERE CODIGO_PARAMETRO = 'FECHA_ULTIMA_EJECUCION_CALCULO_MORA';
                ELSE
                    INSERT INTO PARAMETROS_CONSTANTES 
                        (
                            CODIGO_PARAMETRO,
                            DESCRIPCION_PARAMETRO,
                            VALOR_PARAMETRO,
                            ESTADO,
                            USUARIO_CREACION_ID,
                            USUARIO_CREACION_NOMBRE,
                            USUARIO_MODIFICACION_ID,
                            USUARIO_MODIFICACION_NOMBRE,
                            CREATED_AT,
                            UPDATED_AT
                        )
                    VALUES
                        (
                            'FECHA_ULTIMA_EJECUCION_CALCULO_MORA',
                            'Fecha de la última ejecución del proceso de cálculo de mora',
                            CONVERT(P_FECHA_EJECUCION, CHAR),
                            1,
                            P_USUARIOID,
                            P_USUARIO,
                            P_USUARIOID,
                            P_USUARIO,
                            SYSDATE(),
                            SYSDATE()
                        );
                END IF;
            END IF;
        
            SET V_ERROR_MENSAJE = 'Proceso terminó correctamente - Cálculo de valor interés mora';
            
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
                    P_TRANSACCION, 
                    'PROCESO',
                    P_NUMEROPROYECTO, 
                    V_ERROR_MENSAJE,
                    P_USUARIOID, 
                    P_USUARIO, 
                    SYSDATE(), 
                    SYSDATE() 
                );
        END;";
        DB::unprepared($procedure1);
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
