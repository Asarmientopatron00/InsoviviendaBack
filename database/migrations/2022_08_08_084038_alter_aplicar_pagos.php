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
        $procedure = "DROP PROCEDURE IF EXISTS `SP_PagosAplicar`;
            CREATE PROCEDURE SP_PagosAplicar(
                IN P_NUMEROPROYECTO INT,
                IN P_PAGOID INT,
                IN P_FECHAPAGO DATE,
                IN P_VALORPAGO DECIMAL(18,2),
                IN P_TRANSACCION VARCHAR(30),
                IN P_USUARIOID INT,
                IN P_USUARIO VARCHAR(10)
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
                DECLARE V_VALORCAPITALPAGO DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORCAPITALASALDAR DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORINTERESPAGO DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORSEGUROPAGO DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORINTERESMORAPAGO DECIMAL(18,2) DEFAULT 0;
                --
                DECLARE V_APLICAEXTRA VARCHAR(64) DEFAULT NULL;
                DECLARE V_ULTIMACUOTAPAGADA DECIMAL(10,0) DEFAULT 0;
                DECLARE V_ULTIMAFECHAPAGADA DATE DEFAULT NULL;
                DECLARE V_APLICOPAGOS VARCHAR(64) DEFAULT NULL;
                --
                DECLARE V_CUOTACANCELADA VARCHAR(1) DEFAULT NULL;
                DECLARE V_VALORABONOSTOTAL DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORCREDITO INT DEFAULT 0;
                DECLARE V_VALORINTERESES_SEGURO INT DEFAULT 0;
                DECLARE V_PAGOINSUFICIENTE VARCHAR(1) DEFAULT NULL;
                DECLARE V_ESULTIMACUOTA INT DEFAULT 0;
                DECLARE V_TASANMV DECIMAL(8,6) DEFAULT 0;
                -- 
                DECLARE FINISHED INT DEFAULT 0;
                DECLARE V_VALORSALDOCARTERA DECIMAL(18,2) DEFAULT 0;
                    
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
            
                IF P_VALORPAGO = 0 OR P_VALORPAGO IS NULL THEN
                    SET V_ERRORMENSAJE = 'Valor de pago es obligatorio.';
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
            
                -- PROCESAR PAGOS.
            
                IF P_VALORPAGO <> 0 THEN
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
                        
                    IF V_VALORCREDITO < V_VALORABONOSTOTAL THEN
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
                            -- SE SELECCIONA LOS PAGOS APLICADOS A LA CUOTA
                            SELECT SUM(PAGDETVALORCAPITALCUOTAPAGADO)
                            INTO V_VALORCAPITALPAGO
                            FROM PAGOS_DETALLE
                            WHERE PROYECTO_ID = P_NUMEROPROYECTO
                            AND PAGDETNUMEROCUOTA = V_NUMEROCUOTA
                            AND PAGDETESTADO = 1;
            
                            SELECT SUM(PAGDETVALORINTERESCUOTAPAGADO)
                            INTO V_VALORINTERESPAGO
                            FROM PAGOS_DETALLE
                            WHERE PROYECTO_ID = P_NUMEROPROYECTO
                            AND PAGDETNUMEROCUOTA = V_NUMEROCUOTA
                            AND PAGDETESTADO = 1;
            
                            SELECT SUM(PAGDETVALORSEGUROCUOTAPAGADO)
                            INTO V_VALORSEGUROPAGO
                            FROM PAGOS_DETALLE
                            WHERE PROYECTO_ID = P_NUMEROPROYECTO
                            AND PAGDETNUMEROCUOTA = V_NUMEROCUOTA
                            AND PAGDETESTADO = 1;
                            
                            SELECT SUM(PAGDETVALORINTERESMORAPAGADO)
                            INTO V_VALORINTERESMORAPAGO
                            FROM PAGOS_DETALLE
                            WHERE PROYECTO_ID = P_NUMEROPROYECTO
                            AND PAGDETNUMEROCUOTA = V_NUMEROCUOTA
                            AND PAGDETESTADO = 1;
            
                            -- VERIFICA SI EXITEN PAGOS PARA LA CUOTA
                            IF V_VALORCAPITALPAGO <> 0 THEN 
                                SET V_VALORCAPITALCUOTA = V_VALORCAPITALCUOTA - V_VALORCAPITALPAGO;
                            END IF;
                            
                            IF V_VALORINTERESPAGO <> 0 THEN 
                                SET V_VALORINTERESCUOTA = V_VALORINTERESCUOTA - V_VALORINTERESPAGO;
                            END IF;
                            
                            IF V_VALORSEGUROPAGO <> 0 THEN
                                SET V_VALORSEGUROCUOTA = V_VALORSEGUROCUOTA - V_VALORSEGUROPAGO;
                            END IF;
            
                            IF V_VALORINTERESMORAPAGO <> 0 THEN
                                SET V_VALORINTERESMORA = V_VALORINTERESMORA - V_VALORINTERESMORAPAGO;
                            END IF;
            
                            -- ######
                            -- VERIFICA SI EL VALOR PAGADO ES INFERIOR A INTERESES + SEGURO
                            SET V_VALORINTERESES_SEGURO = V_VALORINTERESMORA + V_VALORINTERESCUOTA + V_VALORSEGUROCUOTA;
            
                            IF P_VALORPAGO < V_VALORINTERESES_SEGURO THEN
                                SET V_PAGOINSUFICIENTE = 'S';
                                -- SE MODIFICA TEMPORALMENTE LA CUOTA ACTUAL COMO PAGADA
                                -- UPDATE PLAN_AMORTIZACION_DEF 
                                -- SET PLAMDECUOTACANCELADA = 'S',
                                -- UPDATED_AT = SYSDATE() 
                                -- WHERE PROYECTO_ID = P_NUMEROPROYECTO
                                -- AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA;
            
                                -- SE REGISTRA UN PAGODETALLE PARA MODIFICACION DEL PLAN DE AMORTIZACION Y SE ASIGNA A EL ABONO A LA CUOTA ACTUAL
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
                                    0,
                                    P_VALORPAGO,
                                    0,
                                    0,
                                    0,
                                    V_DIASMORA,
                                    1,
                                    P_USUARIOID,
                                    P_USUARIO,
                                    P_USUARIOID,
                                    P_USUARIO,
                                    SYSDATE(),
                                    SYSDATE()
                                );
                                LEAVE CURCUOTASPENDIENTESLOOP;
                            END IF;
            
                            -- APLICA PAGOS
                            -- APLICA MORA
                            IF V_VALORINTERESMORA > P_VALORPAGO THEN
                                SET V_VALORINTERESMORA = P_VALORPAGO;
                                SET P_VALORPAGO = 0;
                            ELSE
                                SET P_VALORPAGO = P_VALORPAGO - V_VALORINTERESMORA;
                            END IF;
            
                            -- APLICA INTERES
                            IF V_VALORINTERESCUOTA > P_VALORPAGO THEN
                                SET V_VALORINTERESCUOTA = P_VALORPAGO;
                                SET P_VALORPAGO = 0;
                            ELSE
                                SET P_VALORPAGO = P_VALORPAGO - V_VALORINTERESCUOTA;
                            END IF;
            
                                -- APLICA SEGURO
                            IF V_VALORSEGUROCUOTA > P_VALORPAGO THEN
                                SET V_VALORSEGUROCUOTA = P_VALORPAGO;
                                SET P_VALORPAGO = 0;
                            ELSE
                                SET P_VALORPAGO = P_VALORPAGO - V_VALORSEGUROCUOTA;
                            END IF;
            
                            -- APLICA CAPITAL
                            SET V_CUOTACANCELADA = 'S';
            
                            IF V_VALORCAPITALCUOTA > P_VALORPAGO THEN
                                -- SE CALCULA LA DIFERENCIA DE CAPITAL QUE FALTA POR PAGAR PARA ASIGNAR A SIGUIENTE CUOTA
                                SET V_VALORCAPITALASALDAR = 0;
                                SET V_VALORCAPITALASALDAR = V_VALORCAPITALCUOTA - P_VALORPAGO;
                                SET V_VALORCAPITALCUOTA = P_VALORPAGO;
                                SET P_VALORPAGO = 0;
                            ELSE
                                SET P_VALORPAGO = P_VALORPAGO - V_VALORCAPITALCUOTA;
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
            
                            -- VALIDA SI QUEDA SALDO A APLICAR
                            IF P_VALORPAGO = 0 THEN
                                LEAVE CURCUOTASPENDIENTESLOOP;
                            END IF;
            
                        END LOOP CURCUOTASPENDIENTESLOOP;
                        CLOSE CURCUOTASPENDIENTES;
                    END;
            
                    -- APLICA EXTRA SI DESPUES DE PAGAR CUOTAS VENCIDAS QUEDA SALDO 
                    -- MOD: Y SI LA VARIABLE DE PAGO INSUFICIENTE NO FUE MARCADA COMO S
                    IF P_VALORPAGO <> 0 AND V_PAGOINSUFICIENTE <> 'S' THEN
                        -- SE BUSCA LA ULTIMA CUOTA PAGADA
                        SELECT MAX(PLAMDENUMEROCUOTA) 
                        INTO V_NUMEROCUOTA
                        FROM PLAN_AMORTIZACION_DEF
                        WHERE PROYECTO_ID = P_NUMEROPROYECTO
                        AND PLAMDECUOTACANCELADA = 'S'
                        LIMIT 1;
                            
                        -- CONTROLA SI NO EXISTEN CUOTAS PAGADAS EL VALOR SE LE ASIGNA A LA PRIMERA CUOTA
                        IF V_NUMEROCUOTA = 0 OR V_NUMEROCUOTA IS NULL THEN
                            SET V_NUMEROCUOTA = 1;
                        ELSE
                            -- SE BUSCA LA FECHA DE VENCIMIENTO DE LA PROXIMA CUOTA A PAGAR
                            SELECT PLAMDEFECHAULTIMOPAGOCUOTA
                            INTO V_ULTIMAFECHAPAGADA 
                            FROM PLAN_AMORTIZACION_DEF
                            WHERE PLAMDENUMEROCUOTA = V_NUMEROCUOTA
                            LIMIT 1;
                        END IF;
            
                        -- SE BUSCA LA FECHA DE VENCIMIENTO DE LA CUOTA QUE SE PROCESA
                        SELECT PLAMDEFECHAVENCIMIENTOCUOTA 
                        INTO V_FECHAVENCIMIENTO
                        FROM PLAN_AMORTIZACION_DEF
                        WHERE PLAMDENUMEROCUOTA = V_NUMEROCUOTA
                        LIMIT 1;
                        
                        -- INICIALIZA VARIABLES DE PROCESAMIENTO
                        SET V_APLICAEXTRA = 'S';
                        SET V_CUOTACANCELADA = 'S';
                        SET V_VALORINTERESCUOTA = 0;
                        SET V_VALORSEGUROCUOTA = 0;
                        SET V_VALORCAPITALCUOTA = P_VALORPAGO;
                        SET P_VALORPAGO = 0;
            
                        -- SE VERIFICA SI LA FECHA DE PAGO ES IGUAL A LA ULTIMA CUOTA PAGADA
                        IF P_FECHAPAGO = V_ULTIMAFECHAPAGADA THEN
                            -- ACTUALIZA REGISTRO DE PAGO
                            UPDATE PAGOS_DETALLE 
                            SET PAGDETVALORCAPITALCUOTAPAGADO = PAGDETVALORCAPITALCUOTAPAGADO + V_VALORCAPITALCUOTA
                            WHERE PROYECTO_ID = P_NUMEROPROYECTO
                            AND PAGDETNUMEROCUOTA = V_NUMEROCUOTA;
                        ELSE
                            -- INSERTA REGISTRO DE PAGO
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
                                1,
                                P_USUARIOID,
                                P_USUARIO,
                                P_USUARIOID,
                                P_USUARIO,
                                SYSDATE(),
                                SYSDATE()
                            );
                        END IF;
            
                        -- SE CAMBIA ESTADO DE LA CUOTA
                        IF V_NUMEROCUOTA = 1 THEN 
                            UPDATE PLAN_AMORTIZACION_DEF 
                            SET PLAMDEFECHAULTIMOPAGOCUOTA = P_FECHAPAGO,
                            PLAMDECUOTACANCELADA = V_CUOTACANCELADA,
                            UPDATED_AT = SYSDATE() 
                            WHERE PROYECTO_ID = P_NUMEROPROYECTO
                            AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA;
                        END IF;
                    END IF;
                END IF;
            
                -- APLICA VALOR EXTRA A CAPITAL Y REGENERA PLAN DE PAGOS
                IF V_APLICAEXTRA = 'S' OR V_VALORCAPITALASALDAR > 0 OR V_PAGOINSUFICIENTE = 'S' THEN
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
                            1, 
                            'CalculoValorInteresMora', 
                            P_USUARIOID,
                            P_USUARIO
                        );
                    END;
                END IF;
            
                IF V_PAGOINSUFICIENTE = 'S' THEN
                    -- ELIMINA LAS MODIFICACIONES TEMPORALES DE ######
                    -- SE DEJA LA CUOTA DE PAGO INSUFICIENTE COMO NO PAGADA Y SE REDUCE EL VALOR DEL SALDO
                    -- UPDATE PLAN_AMORTIZACION_DEF 
                    -- SET PLAMDECUOTACANCELADA = 'N',
                    -- PLAMDEVALORSALDOCAPITAL = PLAMDEVALORSALDOCAPITAL - P_VALORPAGO,
                    -- UPDATED_AT = SYSDATE()
                    -- WHERE PROYECTO_ID = P_NUMEROPROYECTO
                    -- AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA;
            
                    -- SE ACTUALIZAR EL VALOR DEL ABONO A CAPITAL EN PAGOS_DETALLE
                    -- UPDATE PAGOS_DETALLE 
                    -- SET PAGDETVALORSALDOCUOTAPAGADO = P_VALORPAGO,
                    -- UPDATED_AT = SYSDATE()
                    -- WHERE PROYECTO_ID = P_NUMEROPROYECTO
                    -- AND PAGDETNUMEROCUOTA = V_NUMEROCUOTA
                    -- AND PAGDETFECHAPAGO = P_FECHAPAGO;
            
                    -- SE VERIFICA SI LA REGENERACION CAUSÓ QUE LA ULTIMA CUOTA FUESE LA ABONADA
                    SELECT COUNT(1)
                    INTO V_ESULTIMACUOTA 	
                    FROM PLAN_AMORTIZACION_DEF
                    WHERE PLAMDENUMEROCUOTA > V_NUMEROCUOTA
                    AND PROYECTO_ID = P_NUMEROPROYECTO;
            
                    -- SE CAMBIA EL VALOR A DE LA CUOTA CAPITAL Y EL PAGO PARA QUE TENGAN SENTIDO EN CASO DE QUE ASÍ FUESE
                    IF V_ESULTIMACUOTA = 0 OR V_ESULTIMACUOTA IS NULL THEN
                        SELECT PROYECTOSTASAINTERESNMV
                        INTO V_TASANMV 
                        FROM PROYECTOS 
                        WHERE ID = P_NUMEROPROYECTO
                        LIMIT 1;
            
                        UPDATE PLAN_AMORTIZACION_DEF 
                        SET PLAMDEVALORCAPITALCUOTA = PLAMDEVALORSALDOCAPITAL,
                        PLAMDEVALORINTERESCUOTA = ROUND(PLAMDEVALORSALDOCAPITAL*V_TASANMV/100,0),
                        UPDATED_AT = SYSDATE()
                        WHERE PROYECTO_ID = P_NUMEROPROYECTO
                        AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA;
                    END IF;
                END IF;
            
                IF V_PAGOINSUFICIENTE = 'S' THEN
                    SELECT PLAMDEVALORSALDOCAPITAL 
                    INTO V_VALORSALDOCARTERA
                    FROM PLAN_AMORTIZACION_DEF
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO
                    AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA;
                ELSE 
                    SELECT PLAMDEVALORSALDOCAPITAL 
                    INTO V_VALORSALDOCARTERA
                    FROM PLAN_AMORTIZACION_DEF
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO
                    AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA+1;
                END IF;
            
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
