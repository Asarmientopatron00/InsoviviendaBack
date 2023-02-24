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
        $procedure1 = "DROP PROCEDURE IF EXISTS `SP_PagosAplicarEspecial`;
        CREATE PROCEDURE SP_PagosAplicarEspecial(
            IN P_NUMEROPROYECTO INT,
            IN P_PAGOID INT,
            IN P_FECHAPAGO DATE,
            IN P_CUOTAINICIAL INT,
            IN P_CUOTAFINAL INT,
            IN P_VALORCAPITALPAGO DECIMAL(18,2),
            IN P_VALORINTERESPAGO DECIMAL(18,2),
            IN P_VALORSEGUROPAGO DECIMAL(18,2),
            IN P_VALORMORAPAGO DECIMAL(18,2),
            IN P_CONDONAR_SEGURO TINYINT(1),
            IN P_CONDONAR_MORA TINYINT(1),
            IN P_CONDONAR_INTERES TINYINT(1),
            IN P_TRANSACCION VARCHAR(30),
            IN P_USUARIOID INT,
            IN P_USUARIO VARCHAR(128)
        )
        SP: BEGIN
            -- VARIABLES.
            DECLARE V_ERRORMENSAJE VARCHAR(512) DEFAULT '';
            --
            DECLARE V_NUMEROCUOTA DECIMAL(10,0) DEFAULT 0;
            DECLARE V_FECHAVENCIMIENTO DATE DEFAULT NULL;
            DECLARE V_VALORCAPITALCUOTA DECIMAL(18,2) DEFAULT 0;
            DECLARE V_VALORINTERESCUOTA DECIMAL(18,2) DEFAULT 0;
            DECLARE V_VALORSEGUROCUOTA DECIMAL(18,2) DEFAULT 0;
            DECLARE V_VALORINTERESMORA DECIMAL(18,2) DEFAULT 0;
            DECLARE V_DIASMORA DECIMAL(10,0) DEFAULT 0;
            --
            DECLARE V_REGENERAR VARCHAR(1) DEFAULT 'N';
            --
            DECLARE V_CUOTACANCELADA VARCHAR(1) DEFAULT NULL;
            DECLARE V_VALORABONOSTOTAL DECIMAL(18,2) DEFAULT 0;
            DECLARE V_VALORCREDITO INT DEFAULT 0;
            DECLARE V_VALORSALDOCARTERA DECIMAL(18,2) DEFAULT 0;
            -- 
            DECLARE FINISHED INT DEFAULT 0;
            --
            DECLARE V_VALORMORACONDONADO DECIMAL(18,2) DEFAULT 0;
            DECLARE V_VALORINTERESCONDONADO DECIMAL(18,2) DEFAULT 0;
            DECLARE V_VALORSEGUROCONDONADO DECIMAL(18,2) DEFAULT 0;
        
            DECLARE ID_PROYECTO_UNIFICADO INT DEFAULT 0;
            DECLARE SALDO_UNIFICADO DECIMAL(18,2) DEFAULT 0;
                
            -- VALIDAR EL INGRESO DE PARAMETROS OBLIGATORIOS.
                    
            IF P_NUMEROPROYECTO = 0 OR P_NUMEROPROYECTO IS NULL THEN  
                SET V_ERRORMENSAJE = 'Número de proyecto es obligatorio.';
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
                    V_ERRORMENSAJE,
                    P_USUARIOID,
                    P_USUARIO,
                    SYSDATE(),
                    SYSDATE()
                );
                LEAVE SP;
            END IF;
        
            IF P_CUOTAINICIAL = 0 OR P_CUOTAFINAL = 0 OR P_CUOTAINICIAL IS NULL OR P_CUOTAFINAL IS NULL THEN  
                SET V_ERRORMENSAJE = 'Se deben especificar cuota inicial y cuota final';
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
                    V_ERRORMENSAJE,
                    P_USUARIOID,
                    P_USUARIO,
                    SYSDATE(),
                    SYSDATE()
                );
                LEAVE SP;
            END IF;
        
            IF P_FECHAPAGO IS NULL THEN
                SET V_ERRORMENSAJE = 'Fecha de pago es obligatorio.';
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
                    V_ERRORMENSAJE,
                    P_USUARIOID,
                    P_USUARIO,
                    SYSDATE(),
                    SYSDATE()
                );
                LEAVE SP;
            END IF;
        
            -- VERIFICA SI EL PROYECTO TIENE UNO UNIFICADO
            SELECT PROYECTO_UNIFICADO_ID, PROYECTOSVALORSALDOUNIFICADO
            INTO ID_PROYECTO_UNIFICADO, SALDO_UNIFICADO
            FROM PROYECTOS
            WHERE ID = P_NUMEROPROYECTO
            LIMIT 1;
        
            IF ID_PROYECTO_UNIFICADO IS NULL THEN
                SET ID_PROYECTO_UNIFICADO = 0;
                SET SALDO_UNIFICADO = 0;
            END IF;
        
            -- PROCESAR PAGOS.
        
            -- SE VELIDA EL SALDO DEL CREDITO ANTES DE APLICAR
            -- SE BUSCA EL VALOR DEL DESEMBOLSO
            SELECT SUM(DESEMBOLSOSVALORDESEMBOLSO) 
            INTO V_VALORCREDITO
            FROM DESEMBOLSOS 
            WHERE PROYECTO_ID = P_NUMEROPROYECTO
            AND DESEMBOLSOSESTADO = '1'
            LIMIT 1;
        
            -- CALCULAR TOTAL ABONOS A CAPITAL
            SELECT SUM(PAGDETVALORCAPITALCUOTAPAGADO)+SUM(IFNULL(PAGDETVALORSALDOCUOTAPAGADO,0)) 
            INTO V_VALORABONOSTOTAL
            FROM PAGOS_DETALLE 
            WHERE PROYECTO_ID = P_NUMEROPROYECTO
            AND PAGDETESTADO = 1
            LIMIT 1;
                
            IF V_VALORCREDITO+SALDO_UNIFICADO < V_VALORABONOSTOTAL THEN
                SET V_ERRORMENSAJE = 'El saldo del crédito es menor al pago a aplicar.';
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
                    V_ERRORMENSAJE,
                    P_USUARIOID,
                    P_USUARIO,
                    SYSDATE(),
                    SYSDATE()
                );
                LEAVE SP;
            END IF;
        
            -- SE SELECCIONA LAS CUOTAS PENDIENTES DE PAGAR 
            BEGIN 
                DECLARE CURCUOTASPENDIENTES CURSOR FOR 
                SELECT PLAMDENUMEROCUOTA, 
                PLAMDEFECHAVENCIMIENTOCUOTA, 
                PLAMDEVALORCAPITALCUOTA,
                PLAMDEVALORINTERESCUOTA, 
                PLAMDEVALORSEGUROCUOTA, 
                PLAMDEVALORINTERESMORA, 
                PLAMDEDIASMORA
                FROM PLAN_AMORTIZACION_DEF
                WHERE PROYECTO_ID = P_NUMEROPROYECTO
                -- AND PLAMDEFECHAVENCIMIENTOCUOTA <= P_FECHAPAGO -- PERMITE QUE EL CURSOR APUNTE TAMBIÉN A CUOTA NO VENCIDAS
                AND PLAMDENUMEROCUOTA >= P_CUOTAINICIAL
                AND PLAMDENUMEROCUOTA <= P_CUOTAFINAL
                AND PLAMDECUOTACANCELADA = 'N'
                ORDER BY PROYECTO_ID, PLAMDENUMEROCUOTA;
        
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET FINISHED = 1;
        
                OPEN CURCUOTASPENDIENTES;
        
                CURCUOTASPENDIENTESLOOP: LOOP
        
                    FETCH NEXT 
                    FROM CURCUOTASPENDIENTES 
                    INTO V_NUMEROCUOTA, V_FECHAVENCIMIENTO, V_VALORCAPITALCUOTA,
                    V_VALORINTERESCUOTA, V_VALORSEGUROCUOTA, V_VALORINTERESMORA,
                    V_DIASMORA;
        
                    IF FINISHED = 1 THEN 
                        LEAVE CURCUOTASPENDIENTESLOOP; 
                    END IF;
        
                    -- PROCESAR CUOTAS
                    -- APLICA PAGOS
                    -- APLICA MORA
                    IF V_VALORINTERESMORA > P_VALORMORAPAGO THEN
                        SET V_VALORMORACONDONADO = V_VALORINTERESMORA - P_VALORMORAPAGO;
                        SET V_VALORINTERESMORA = P_VALORMORAPAGO;
                        SET P_VALORMORAPAGO = 0;
                    ELSE
                        SET P_VALORMORAPAGO = P_VALORMORAPAGO - V_VALORINTERESMORA;
                    END IF;
        
                    -- APLICA INTERES
                    IF V_VALORINTERESCUOTA > P_VALORINTERESPAGO THEN
                        SET V_VALORINTERESCONDONADO = V_VALORINTERESCUOTA - P_VALORINTERESPAGO;
                        SET V_VALORINTERESCUOTA = P_VALORINTERESPAGO;
                        SET P_VALORINTERESPAGO = 0;
                    ELSE
                        SET P_VALORINTERESPAGO = P_VALORINTERESPAGO - V_VALORINTERESCUOTA;
                    END IF;
        
                        -- APLICA SEGURO
                    IF V_VALORSEGUROCUOTA > P_VALORSEGUROPAGO THEN
                        SET V_VALORSEGUROCONDONADO = V_VALORSEGUROCUOTA - P_VALORSEGUROPAGO;
                        SET V_VALORSEGUROCUOTA = P_VALORSEGUROPAGO;
                        SET P_VALORSEGUROPAGO = 0;
                    ELSE
                        SET P_VALORSEGUROPAGO = P_VALORSEGUROPAGO - V_VALORSEGUROCUOTA;
                    END IF;
        
                    -- APLICA CAPITAL
                    SET V_CUOTACANCELADA = 'S';
        
                    IF V_VALORCAPITALCUOTA > P_VALORCAPITALPAGO THEN
                        SET V_REGENERAR = 'S';
                        SET V_VALORCAPITALCUOTA = P_VALORCAPITALPAGO;
                        SET P_VALORCAPITALPAGO = 0;
                    ELSE
                        SET P_VALORCAPITALPAGO = P_VALORCAPITALPAGO - V_VALORCAPITALCUOTA;
                    END IF;
        
                    IF P_CONDONAR_SEGURO = 0 THEN
                        SET V_VALORSEGUROCONDONADO = 0;
                    END IF;
                    
                    IF P_CONDONAR_MORA = 0 THEN
                        SET V_VALORMORACONDONADO = 0;
                    END IF;
                    
                    IF P_CONDONAR_INTERES = 0 THEN
                        SET V_VALORINTERESCONDONADO = 0;
                    END IF;
        
                    -- GRABA REGISTRO DE PAGO
                    INSERT INTO PAGOS_DETALLE (
                        PROYECTO_ID,
                        PAGO_ID,
                        PAGDETFECHAPAGO,
                        PAGDETNUMEROCUOTA,
                        PAGDETFECHAVENCIMIENTOCUOTA,
                        PAGDETVALORCAPITALCUOTAPAGADO,
                        PAGDETVALORSALDOCUOTAPAGADO,
                        PAGDETVALORINTERESCUOTAPAGADO,
                        PAGDETVALORSEGUROCUOTAPAGADO,
                        PAGDETVALORINTERESMORAPAGADO,
                        PAGDETDIASMORA,
                        PAGDETVALORINTERESMORACONDONADO,
                        PAGDETVALORSEGUROCUOTACONDONADO,
                        PAGDETVALORINTERESCUOTACONDONADO,
                        PAGDETESTADO,
                        USUARIO_CREACION_ID,
                        USUARIO_CREACION_NOMBRE,
                        USUARIO_MODIFICACION_ID,
                        USUARIO_MODIFICACION_NOMBRE,
                        CREATED_AT,
                        UPDATED_AT
                    ) VALUES (
                        P_NUMEROPROYECTO,
                        P_PAGOID,
                        P_FECHAPAGO,
                        V_NUMEROCUOTA,
                        V_FECHAVENCIMIENTO,
                        V_VALORCAPITALCUOTA,
                        0,
                        V_VALORINTERESCUOTA,
                        V_VALORSEGUROCUOTA,
                        V_VALORINTERESMORA,
                        V_DIASMORA,
                        V_VALORMORACONDONADO,
                        V_VALORSEGUROCONDONADO,
                        V_VALORINTERESCONDONADO,
                        1,
                        P_USUARIOID,
                        P_USUARIO,
                        P_USUARIOID,
                        P_USUARIO,
                        SYSDATE(),
                        SYSDATE()
                    );
        
                    -- SE CAMBIA ESTADO DE LA CUOTA
                    UPDATE PLAN_AMORTIZACION_DEF 
                    SET PLAMDEFECHAULTIMOPAGOCUOTA = P_FECHAPAGO,
                    PLAMDECUOTACANCELADA = V_CUOTACANCELADA,
                    UPDATED_AT = SYSDATE() 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO
                    AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA;
        
                    SET V_VALORMORACONDONADO = 0;
                    SET V_VALORINTERESCONDONADO = 0;
                    SET V_VALORSEGUROCONDONADO = 0;
        
                END LOOP CURCUOTASPENDIENTESLOOP;
                CLOSE CURCUOTASPENDIENTES;
            END;
        
            -- APLICA VALOR EXTRA A CAPITAL Y REGENERA PLAN DE PAGOS
            IF V_REGENERAR = 'S' THEN
                -- REGENERA PLAN DE PAGOS
                BEGIN
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
                        P_FECHAPAGO, 
                        1, 
                        'CalculoValorInteresMora', 
                        P_USUARIOID,
                        P_USUARIO
                    );
                END;
            END IF;
        
            SELECT PLAMDEVALORSALDOCAPITAL 
            INTO V_VALORSALDOCARTERA
            FROM PLAN_AMORTIZACION_DEF
            WHERE PROYECTO_ID = P_NUMEROPROYECTO
            AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA+1;
        
            IF V_VALORSALDOCARTERA IS NULL THEN
                SET V_VALORSALDOCARTERA = 0;
            END IF;
        
            UPDATE PAGOS 
            SET PAGOSSALDODESPPAGO = V_VALORSALDOCARTERA,
            UPDATED_AT = SYSDATE()
            WHERE PROYECTO_ID = P_NUMEROPROYECTO
            AND ID = P_PAGOID;
        
            SELECT CONCAT('Proceso terminó correctamente - Proyecto : ', CAST(P_NUMEROPROYECTO AS CHAR)) INTO V_ERRORMENSAJE;
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
                V_ERRORMENSAJE,
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
