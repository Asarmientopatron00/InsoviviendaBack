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
                                UPDATE PLAN_AMORTIZACION_DEF
                                SET PLAMDEVALORINTERESMORA = V_VALOR_INTERES_MORA,
                                PLAMDEDIASMORA = V_DIAS_DE_MORA
                                WHERE PROYECTO_ID = V_NUMERO_PROYECTO
                                AND PLAMDENUMEROCUOTA = V_NUMERO_CUOTA;
                            END IF;
                        END IF;
                                    
                    END LOOP GETCUOTAS;
                    
                    CLOSE CURCUOTASPENDIENTES;
                END; 

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
        $procedure2 = "DROP PROCEDURE IF EXISTS `SP_PagosAplicarAbonoExtra`;
            CREATE PROCEDURE SP_PagosAplicarAbonoExtra(
                IN P_NUMEROPROYECTO INT,
                IN P_PAGOID INT,
                IN P_FECHAPAGO DATE,
                IN P_VALORPAGO DECIMAL(18,2),
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
                DECLARE V_CUOTACANCELADA VARCHAR(1) DEFAULT NULL;
                DECLARE V_VALORABONOSTOTAL DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORCREDITO INT DEFAULT 0;
                DECLARE V_PAGOINSUFICIENTE VARCHAR(1) DEFAULT NULL;
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
                        SET V_ERRORMENSAJE = 'El saldo del crédito es menor al abono a aplicar.';
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
                            -- SE SELECCIONA LOS PAGOS APLICADOS A LA CUOT
            
                            -- APLICA CAPITAL
                            SET V_CUOTACANCELADA = 'S';
            
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
            
                            -- SE CAMBIA ESTADO DE LA CUOTA
                            UPDATE PLAN_AMORTIZACION_DEF 
                            SET PLAMDEFECHAULTIMOPAGOCUOTA = P_FECHAPAGO,
                            -- PLAMDECUOTACANCELADA = V_CUOTACANCELADA,
                            UPDATED_AT = SYSDATE() 
                            WHERE PROYECTO_ID = P_NUMEROPROYECTO
                            AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA;
            
                            LEAVE CURCUOTASPENDIENTESLOOP;
            
                        END LOOP CURCUOTASPENDIENTESLOOP;
                        CLOSE CURCUOTASPENDIENTES;
                    END;
                END IF;
            
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
                        SYSDATE(), 
                        1, 
                        'CalculoValorInteresMora', 
                        P_USUARIOID,
                        P_USUARIO
                    );
                END;
            
                -- IF V_PAGOINSUFICIENTE = 'S' THEN
                SELECT PLAMDEVALORSALDOCAPITAL 
                INTO V_VALORSALDOCARTERA
                FROM PLAN_AMORTIZACION_DEF
                WHERE PROYECTO_ID = P_NUMEROPROYECTO
                AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA;
                -- ELSE 
                -- 	SELECT PLAMDEVALORSALDOCAPITAL 
                -- 	INTO V_VALORSALDOCARTERA
                -- 	FROM PLAN_AMORTIZACION_DEF
                -- 	WHERE PROYECTO_ID = P_NUMEROPROYECTO
                -- 	AND PLAMDENUMEROCUOTA = V_NUMEROCUOTA+1;
                -- END IF;
            
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
        DB::unprepared($procedure2);
        $procedure3 = "DROP PROCEDURE IF EXISTS `SP_PagosAplicarEspecial`;
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
                            SYSDATE(), 
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
        DB::unprepared($procedure3);
        $procedure4 = "DROP PROCEDURE IF EXISTS `SP_PagosAplicar`;
            CREATE PROCEDURE SP_PagosAplicar(
                IN P_NUMEROPROYECTO INT,
                IN P_PAGOID INT,
                IN P_FECHAPAGO DATE,
                IN P_VALORPAGO DECIMAL(18,2),
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
                            SYSDATE(), 
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
        DB::unprepared($procedure4);
        $procedure5 = "DROP PROCEDURE IF EXISTS `SP_PagosReversar`;
            CREATE PROCEDURE SP_PagosReversar(
                IN P_NUMEROPROYECTO INT,
                IN P_FECHAPAGO DATE,
                IN P_TRANSACCION VARCHAR(30),
                IN P_USUARIOID INT,
                IN P_USUARIO VARCHAR(128)
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
                            SYSDATE(), 
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
        DB::unprepared($procedure5);
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
