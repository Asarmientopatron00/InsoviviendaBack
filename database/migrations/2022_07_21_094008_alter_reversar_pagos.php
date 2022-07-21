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
        $procedure = "DROP PROCEDURE IF EXISTS `SP_PagosReversar`;
            CREATE PROCEDURE SP_PagosReversar(
                IN P_NUMEROPROYECTO INT,
                IN P_FECHAPAGO DATE,
                IN P_TRANSACCION VARCHAR(30),
                IN P_USUARIOID INT,
                IN P_USUARIO VARCHAR(10)
            )
            SP: BEGIN
                -- VARIABLES.
                DECLARE V_ERROR_MENSAJE VARCHAR(512) DEFAULT '';
            
                DECLARE V_NUMEROCUOTA DECIMAL(10,0) DEFAULT 0;
                DECLARE V_INDREVERSAR VARCHAR(1) DEFAULT '';
            
                DECLARE REGISTROS INT DEFAULT 0;
                DECLARE FINISHED INT DEFAULT 0;
            
                -- VALIDAR EL INGRESO DE PARAMETROS OBLIGATORIOS.
                IF P_NUMEROPROYECTO = 0 OR P_NUMEROPROYECTO IS NULL THEN
                    SET V_ERROR_MENSAJE = 'Número de proyecto es obligatorio.';
                    INSERT INTO AUDITORIA_PROCESOS (
                        AUDPROTRANSACCION,
                        AUDPROTIPO,
                        AUDPRONUMEROPROYECTO,
                        AUDPRODESCRIPCION,
                        AUDPROUSUARIOCREACIONID,
                        AUDPROUSUARIOCREACIONNOMBRE,
                        CREATED_AT,
                        UPDATED_AT
                    ) VALUES (
                        P_TRANSACCION,
                        'ERROR',
                        P_NUMEROPROYECTO,
                        V_ERROR_MENSAJE,
                        P_USUARIOID,
                        P_USUARIO,
                        SYSDATE(),
                        SYSDATE()
                    );
                    LEAVE SP;
                END IF;
            
                IF P_FECHAPAGO = ' ' OR P_FECHAPAGO IS NULL THEN
                    SET V_ERROR_MENSAJE = 'Fecha de pago es obligatorio.';
                    INSERT INTO AUDITORIA_PROCESOS (
                        AUDPROTRANSACCION,
                        AUDPROTIPO,
                        AUDPRONUMEROPROYECTO,
                        AUDPRODESCRIPCION,
                        AUDPROUSUARIOCREACIONID,
                        AUDPROUSUARIOCREACIONNOMBRE,
                        CREATED_AT,
                        UPDATED_AT
                    ) VALUES (
                        P_TRANSACCION,
                        'ERROR',
                        P_NUMEROPROYECTO,
                        V_ERROR_MENSAJE,
                        P_USUARIOID,
                        P_USUARIO,
                        SYSDATE(),
                        SYSDATE()
                    );
                    LEAVE SP;
                END IF;
            
                SELECT COUNT(1) 
                INTO REGISTROS
                FROM PAGOS 
                WHERE PROYECTO_ID = P_NUMEROPROYECTO 
                AND PAGOSFECHAPAGO > P_FECHAPAGO
                AND PAGOSESTADO = 1;
            
                IF REGISTROS > 0 THEN
                    SET V_ERROR_MENSAJE = 'Existen pagos con fecha posterior al que se va a reversar.';
                    INSERT INTO AUDITORIA_PROCESOS (
                        AUDPROTRANSACCION,
                        AUDPROTIPO,
                        AUDPRONUMEROPROYECTO,
                        AUDPRODESCRIPCION,
                        AUDPROUSUARIOCREACIONID,
                        AUDPROUSUARIOCREACIONNOMBRE,
                        CREATED_AT,
                        UPDATED_AT
                    ) VALUES (
                        P_TRANSACCION,
                        'ERROR',
                        P_NUMEROPROYECTO,
                        V_ERROR_MENSAJE,
                        P_USUARIOID,
                        P_USUARIO,
                        SYSDATE(),
                        SYSDATE()
                    );
                    LEAVE SP;
                END IF;	
            
                -- PROCESAR PAGOS A REVERSAR.
                -- INACTIVA EL PAGO
                UPDATE PAGOS
                SET PAGOSESTADO = '0',
                UPDATED_AT = SYSDATE()
                WHERE PROYECTO_ID = P_NUMEROPROYECTO
                AND PAGOSFECHAPAGO = P_FECHAPAGO;
            
                -- INACTIVA EL DETALLE DEL PAGO
                UPDATE PAGOS_DETALLE
                SET PAGDETESTADO = '0',
                UPDATED_AT = SYSDATE()
                WHERE PROYECTO_ID = P_NUMEROPROYECTO
                AND PAGDETFECHAPAGO = P_FECHAPAGO;
            
                -- BUSCA LAS CUOTAS QUE SE CANCELARON CON EL PAGO A REVERSAR PARA CAMBIARLE EL ESTADO COMO NO PAGADAS
                BEGIN
                    DECLARE CURCUOTASAREVERSAR CURSOR FOR 
                    SELECT PAGDETNUMEROCUOTA
                    FROM PAGOS_DETALLE
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO
                    AND PAGDETFECHAPAGO = P_FECHAPAGO
                    ORDER BY PROYECTO_ID, PAGDETFECHAPAGO;
            
                    DECLARE CONTINUE HANDLER FOR NOT FOUND SET FINISHED = 1;
            
                    OPEN CURCUOTASAREVERSAR;
            
                    CURCUOTASAREVERSARLOOP: LOOP
            
                        FETCH NEXT FROM CURCUOTASAREVERSAR INTO V_NUMEROCUOTA;
                        
                        IF FINISHED = 1 THEN 
                            LEAVE CURCUOTASAREVERSARLOOP; 
                        END IF;
            
                        -- PROCESAR CUOTAS
                        -- SE CAMBIA EL ESTADO A LAS CUOTAS A REVERSAR
                        UPDATE PLAN_AMORTIZACION_DEF
                        SET PLAMDECUOTACANCELADA = 'N',
                        UPDATED_AT = SYSDATE()
                        WHERE PROYECTO_ID = P_NUMEROPROYECTO
                        AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA
                        AND PLAMDEFECHAULTIMOPAGOCUOTA = P_FECHAPAGO;
            
                        SET V_INDREVERSAR = 'S';
            
                    END LOOP CURCUOTASAREVERSARLOOP;
                    CLOSE CURCUOTASAREVERSAR;
                END;
            
                -- SI SE REVERSARON PAGOS, REGENERA PLAN DE PAGOS
                IF V_INDREVERSAR = 'S' THEN
                    BEGIN
                        -- REGENERA PLAN DE PAGOS
                        CALL SP_PlanAmortizacionGenerar(
                            P_NUMEROPROYECTO,
                    'REG',
                            'N',
                            'CalcularPlanAmortizacion',
                            P_USUARIOID,
                            P_USUARIO
                        );
            
                        -- RECALCULAR MORA
                        CALL SP_CalcularValorInteresMora(
                            P_NUMEROPROYECTO, 
                            1, 
                            'CalculoValorInteresMora', 
                            P_USUARIOID,
                            P_USUARIO
                        );
                    END;
                END IF;
            
                SELECT CONCAT('Proceso terminó correctamente - Proyecto : ', CAST(P_NUMEROPROYECTO AS CHAR)) INTO V_ERROR_MENSAJE;
                INSERT INTO AUDITORIA_PROCESOS (
                    AUDPROTRANSACCION,
                    AUDPROTIPO,
                    AUDPRONUMEROPROYECTO,
                    AUDPRODESCRIPCION,
                    AUDPROUSUARIOCREACIONID,
                    AUDPROUSUARIOCREACIONNOMBRE,
                    CREATED_AT,
                    UPDATED_AT
                ) VALUES (
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
            DB::unprepared($procedure);
        }
    
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            
        }
};
