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
        $procedure2 = "DROP PROCEDURE IF EXISTS `SP_PlanAmortizacionGenerar`;
            CREATE PROCEDURE SP_PlanAmortizacionGenerar
            (
                P_NUMEROPROYECTO INT,
                P_TIPOPLAN VARCHAR(3),
                P_PLANDEF VARCHAR(1),
                P_TRANSACCION VARCHAR(30),
                P_USUARIOID INT,
                P_USUARIO VARCHAR(128)
            )
            SP: BEGIN
                -- VARIABLES.
                DECLARE V_ERRORMENSAJE VARCHAR(512) DEFAULT '';
                DECLARE V_VALORCREDITO INT DEFAULT 0;
                DECLARE V_FECHAVENCIMIENTO DATE DEFAULT NULL;
                DECLARE V_FECHANORMALIZACION DATE DEFAULT NULL;
                DECLARE V_TASANMV DECIMAL(8,6) DEFAULT 0;
                DECLARE V_VALORSEGURO DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORCUOTAAPROBADA DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORCUOTASEGURO DECIMAL(18,2) DEFAULT 0;
                DECLARE V_NCUOTAMES INT DEFAULT 1;
            
                -- VARIABLES PARA CALCULAR PLAN APROXIMADO CON SEGURO.
                DECLARE V_NCUOTAMESAPR INT DEFAULT 1;
                DECLARE V_VALORCUOTASEGUROAPR DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORCUOTACREDITOAPR DECIMAL(18,2) DEFAULT 0;
                DECLARE V_CREDITOSALDOMESAPR INT DEFAULT 0;
                DECLARE V_CREDITOCAPITALMESAPR INT DEFAULT 0;
                DECLARE V_CREDITOINTERESMESAPR INT DEFAULT 0;
                
                -- VARIABLES PARA CALCULAR NORMALIZACION DE PAGOS.
                DECLARE V_FECHADESEMBOLSO_PRO DATE DEFAULT NULL;
                DECLARE V_VALORDESEMBOLSO_PRO DECIMAL(18,2) DEFAULT 0;
                DECLARE V_DIASNORMALIZACION INT DEFAULT 0;
                DECLARE V_VALORINTERESNORMALIZACION DECIMAL(18,2) DEFAULT 0;
                DECLARE V_TOTALINTERESNORMALIZACION DECIMAL(18,2) DEFAULT 0;
                
                -- VARIABLES PARA CALCULAR PLAN DEFINITIVO CON SEGURO.	
                DECLARE V_VALORSALDOMES INT DEFAULT 0;
                DECLARE V_VALORCAPITALMES INT DEFAULT 0;
                DECLARE V_VALORINTERESMES INT DEFAULT 0;
                DECLARE V_VALORSEGUROMES INT DEFAULT 0;
                DECLARE V_SEGUROSALDOMES INT DEFAULT 0;
                DECLARE V_SEGUROCAPITALMES INT DEFAULT 0;
                DECLARE V_SEGUROINTERESMES INT DEFAULT 0;
            
                -- VARIABLES PARA REGENERAR PLAN DE PAGOS POR ABONO EXTRA.
                DECLARE V_VALORABONOSTOTAL DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORSALDOTOTAL DECIMAL(18,2) DEFAULT 0;
                DECLARE V_VALORSALDONUEVO DECIMAL(18,2) DEFAULT 0;
                DECLARE V_ULTIMACUOTA INT DEFAULT 0;	
            
                DECLARE FINISHED INT DEFAULT 0;
                DECLARE REGISTROS INT DEFAULT 0;
                DECLARE LASTDAY INT DEFAULT NULL;
                DECLARE ISLASTDAY TINYINT DEFAULT 0;
                DECLARE DIADELMES INT DEFAULT NULL;
                DECLARE FECHA_VECIMIENTO DATE DEFAULT NULL;
                DECLARE ES_DESEMBOLSO TINYINT DEFAULT 0;
                DECLARE DIAS_HASTA_FIN INT DEFAULT 0;
                DECLARE DIAS_FIN INT DEFAULT 0;
                DECLARE MESES_DIF INT DEFAULT 0;
                DECLARE ANNOS_DIF INT DEFAULT 0;
                DECLARE E_NORMALIZACION INT DEFAULT 0;
            
            
                -- VALIDAR EL INGRESO DE PARAMETROS OBLIGATORIOS.
            
                IF P_TIPOPLAN = 'APR' THEN
                    SELECT COUNT(1)
                    INTO REGISTROS 
                    FROM PLAN_AMORTIZACION 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO 
                    AND (PLAAMOESTADOPLANAMORTIZACION = 'DES' 
                    OR PLAAMOESTADOPLANAMORTIZACION = 'DEF'
                    OR PLAAMOESTADOPLANAMORTIZACION = 'REG');
            
                    IF REGISTROS IS NOT NULL AND REGISTROS > 0 THEN
                        SET V_ERRORMENSAJE = 'Ya existe un plan de amortización de desembolso, definitivo o regenerado.';
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
                                P_NUMEROPROYECTO,
                                V_ERRORMENSAJE,
                                P_USUARIOID,
                                P_USUARIO,
                                SYSDATE(),
                                SYSDATE()
                            );
                        LEAVE SP;
                    END IF;	         
                END IF;
            
                -- PROCESAR PLAN AMORTIZACION.
                -- SE VALIDA SI EL PLAN ES APROBACIÓN
            
                IF P_TIPOPLAN = 'APR' THEN
                    -- SE ELIMINA EL PLAN ACTUAL PARA EL PROYECTO
                    DELETE FROM PLAN_AMORTIZACION WHERE PROYECTO_ID = P_NUMEROPROYECTO;
            
                    -- SE BUSCA EL VALOR SOLICITUD
                    SELECT PROYECTOSVALORAPROBADO 
                    INTO V_VALORCREDITO 
                    FROM PROYECTOS 
                    WHERE ID = P_NUMEROPROYECTO 
                    LIMIT 1;
                    
                    -- SE BUSCA LA FECHA APROBACION PARA PRIMER VENCIMIENTO
                    SELECT PROYECTOSFECHAAPROREC 
                    INTO V_FECHAVENCIMIENTO 
                    FROM PROYECTOS 
                    WHERE ID = P_NUMEROPROYECTO 
                    LIMIT 1;
                END IF;
            
                -- SE VALIDA SI EL PLAN ES DESEMBOLSO
            
                IF P_TIPOPLAN = 'DES' THEN
                    -- SE ELIMINA EL PLAN ACTUAL PARA EL PROYECTO
                    DELETE FROM PLAN_AMORTIZACION WHERE PROYECTO_ID = P_NUMEROPROYECTO;
            
                    -- SE BUSCA EL VALOR DEL DESEMBOLSO
                    SELECT SUM(DESEMBOLSOSVALORDESEMBOLSO) 
                    INTO V_VALORCREDITO 
                    FROM DESEMBOLSOS 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO 
                    LIMIT 1;
                    
                    -- SE BUSCA LA FECHA DESEMBOLSO
                    SELECT MAX(DESEMBOLSOSFECHADESEMBOLSO) 
                    INTO V_FECHAVENCIMIENTO 
                    FROM DESEMBOLSOS 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO 
                    LIMIT 1;
            
                    -- SE BUSCA LA FECHA NORMALIZACION
                    SELECT MAX(DESEMBOLSOSFECHANORMALIZACIONP) 
                    INTO V_FECHANORMALIZACION 
                    FROM DESEMBOLSOS 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO 
                    LIMIT 1;
                END IF;
            
                -- SE VALIDA SI EL PLAN ES REGENERADO
            
                IF P_TIPOPLAN = 'REG' THEN
                    -- SE BUSCA EL VALOR DEL DESEMBOLSO
                    SELECT SUM(DESEMBOLSOSVALORDESEMBOLSO) 
                    INTO V_VALORCREDITO
                    FROM DESEMBOLSOS 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO
                    LIMIT 1;
            
                    -- SE BUSCA LA FECHA NORMALIZACION
                    SELECT MAX(DESEMBOLSOSFECHANORMALIZACIONP) 
                    INTO V_FECHANORMALIZACION 
                    FROM DESEMBOLSOS 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO 
                    LIMIT 1;
                    
                    -- SE BUSCA ULTIMA CUOTA PAGADA
                    SELECT MAX(PLAMDENUMEROCUOTA) 
                    INTO V_ULTIMACUOTA
                    FROM PLAN_AMORTIZACION_DEF 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO
                    AND PLAMDECUOTACANCELADA = 'S'
                    LIMIT 1;
            
                    IF V_ULTIMACUOTA IS NULL THEN
                        SET V_ULTIMACUOTA = 0;
            
                        SELECT MAX(DESEMBOLSOSFECHADESEMBOLSO) 
                        INTO V_FECHAVENCIMIENTO
                        FROM DESEMBOLSOS 
                        WHERE PROYECTO_ID = P_NUMEROPROYECTO
                        LIMIT 1;
                    ELSE
                        -- SE BUSCA LA FECHA DESEMBOLSO
                        SELECT MAX(PLAMDEFECHAVENCIMIENTOCUOTA) 
                        INTO V_FECHAVENCIMIENTO
                        FROM PLAN_AMORTIZACION_DEF
                        WHERE PROYECTO_ID = P_NUMEROPROYECTO
                        AND PLAMDENUMEROCUOTA = V_ULTIMACUOTA
                        LIMIT 1;
                    END IF;
            
                    -- CALCULAR TOTAL ABONOS A CAPITAL
                    SELECT IFNULL(SUM(PAGDETVALORCAPITALCUOTAPAGADO) + SUM(IFNULL(PAGDETVALORSALDOCUOTAPAGADO,0)),0) 
                    INTO V_VALORABONOSTOTAL
                    FROM PAGOS_DETALLE 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO
                    AND PAGDETESTADO = 1
                    LIMIT 1;
            
                    -- CALCULA NUEVO SALDO DEL CREDITO
                    SET V_VALORCREDITO = V_VALORCREDITO - V_VALORABONOSTOTAL;
                    
                    -- SE ELIMINA EL PLAN ACTUAL PARA EL PROYECTO
                    DELETE FROM PLAN_AMORTIZACION_DEF 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO	
                    AND PLAMDENUMEROCUOTA > V_ULTIMACUOTA;
            
                    SET V_ULTIMACUOTA = V_ULTIMACUOTA + 1;
                END IF;
            
                -- SE BUSCA EL VALOR DE LA TASA NMV
                SELECT PROYECTOSTASAINTERESNMV 
                INTO V_TASANMV 
                FROM PROYECTOS 
                WHERE ID = P_NUMEROPROYECTO 
                LIMIT 1;
            
                SET REGISTROS = 0;
            
                SELECT COUNT(1)
                INTO REGISTROS 
                FROM PLAN_AMORTIZACION_DEF 
                WHERE PROYECTO_ID = P_NUMEROPROYECTO 
                AND PLAMDECUOTACANCELADA = 'S';
            
                IF REGISTROS = 0 THEN
                    -- SI EXISTE FECHA DE NORMALIZACION SE CALCULA LOS DIAS DE DIFERENCIA CON LA FECHA DE VENCIMIENTO
                    IF V_FECHANORMALIZACION IS NOT NULL AND V_FECHANORMALIZACION > V_FECHAVENCIMIENTO THEN
                        BEGIN
                            DECLARE CURDESEMBOLSOS CURSOR FOR 
                            SELECT DESEMBOLSOSFECHADESEMBOLSO, DESEMBOLSOSVALORDESEMBOLSO 
                            FROM DESEMBOLSOS 
                            WHERE PROYECTO_ID = P_NUMEROPROYECTO 
                            ORDER BY PROYECTO_ID, DESEMBOLSOSFECHADESEMBOLSO;
            
                            DECLARE CONTINUE HANDLER FOR NOT FOUND SET FINISHED = 1;
            
                            OPEN CURDESEMBOLSOS;
            
                            CURDESEMBOLSOSLOOP: LOOP
            
                                FETCH CURDESEMBOLSOS INTO V_FECHADESEMBOLSO_PRO, V_VALORDESEMBOLSO_PRO;
                                
                                IF FINISHED = 1 THEN 
                                    LEAVE CURDESEMBOLSOSLOOP; 
                                END IF;
            
                                -- SELECT DATEDIFF(V_FECHANORMALIZACION, V_FECHADESEMBOLSO_PRO) INTO V_DIASNORMALIZACION;
                                SELECT 30-DAY(V_FECHADESEMBOLSO_PRO) INTO DIAS_HASTA_FIN;
                                SELECT DAY(V_FECHANORMALIZACION) INTO DIAS_FIN;
                                SELECT YEAR(V_FECHANORMALIZACION) - YEAR(V_FECHADESEMBOLSO_PRO) - 1 INTO ANNOS_DIF;
                                IF ANNOS_DIF >= 0 THEN
                                    SELECT MONTH(V_FECHANORMALIZACION) - MONTH(V_FECHADESEMBOLSO_PRO) + 11 INTO MESES_DIF;
                                ELSE 
                                    SELECT MONTH(V_FECHANORMALIZACION) - MONTH(V_FECHADESEMBOLSO_PRO) - 1 INTO MESES_DIF;
                                END IF;
            
                                IF DIAS_HASTA_FIN < 0 THEN
                                    SET DIAS_HASTA_FIN = 0;
                                END IF;
            
                                IF MESES_DIF < 0 THEN
                                    SET MESES_DIF = 0;
                                END IF;
            
                                IF ANNOS_DIF < 0 THEN
                                    SET ANNOS_DIF = 0;
                                END IF;
            
                                SET V_DIASNORMALIZACION = DIAS_HASTA_FIN + DIAS_FIN + 30*MESES_DIF + 30*12*ANNOS_DIF;
                                SET V_VALORINTERESNORMALIZACION = ((((V_VALORDESEMBOLSO_PRO * V_TASANMV) / 100) / 30) * V_DIASNORMALIZACION);
                                SET V_TOTALINTERESNORMALIZACION = V_TOTALINTERESNORMALIZACION + V_VALORINTERESNORMALIZACION;
            
                                -- INICIALIZA VARIABLES DE TRABAJO
                                SET E_NORMALIZACION = 1;
                                SET V_DIASNORMALIZACION = 0;
                                SET V_VALORINTERESNORMALIZACION = 0;
                                SET DIAS_HASTA_FIN = 0;
                                SET DIAS_FIN = 0;
                                SET MESES_DIF = 0;
                                SET ANNOS_DIF = 0;
            
                            END LOOP CURDESEMBOLSOSLOOP;
                            CLOSE CURDESEMBOLSOS;
                        END;
            
                    END IF;
                ELSE
                    IF V_FECHANORMALIZACION IS NOT NULL AND V_FECHANORMALIZACION > V_FECHAVENCIMIENTO THEN
                        SELECT DESEMBOLSOSFECHADESEMBOLSO, DESEMBOLSOSVALORDESEMBOLSO 
                        INTO V_FECHADESEMBOLSO_PRO, V_VALORDESEMBOLSO_PRO
                        FROM DESEMBOLSOS 
                        WHERE PROYECTO_ID = P_NUMEROPROYECTO 
                        AND DESEMBOLSOSFECHADESEMBOLSO = (
                            SELECT MAX(DESEMBOLSOSFECHADESEMBOLSO)
                            FROM DESEMBOLSOS
                            WHERE PROYECTO_ID = P_NUMEROPROYECTO
                        )
                        ORDER BY ID DESC
                        LIMIT 1;
            
                        -- SELECT DATEDIFF(V_FECHANORMALIZACION, V_FECHADESEMBOLSO_PRO) INTO V_DIASNORMALIZACION;
                        SELECT 30-DAY(V_FECHADESEMBOLSO_PRO) INTO DIAS_HASTA_FIN;
                        SELECT DAY(V_FECHANORMALIZACION) INTO DIAS_FIN;
                        SELECT YEAR(V_FECHANORMALIZACION) - YEAR(V_FECHADESEMBOLSO_PRO) - 1 INTO ANNOS_DIF;
                        IF ANNOS_DIF >= 0 THEN
                            SELECT MONTH(V_FECHANORMALIZACION) - MONTH(V_FECHADESEMBOLSO_PRO) + 11 INTO MESES_DIF;
                        ELSE 
                            SELECT MONTH(V_FECHANORMALIZACION) - MONTH(V_FECHADESEMBOLSO_PRO) - 1 INTO MESES_DIF;
                        END IF;
            
                        IF DIAS_HASTA_FIN < 0 THEN
                            SET DIAS_HASTA_FIN = 0;
                        END IF;
            
                        IF MESES_DIF < 0 THEN
                            SET MESES_DIF = 0;
                        END IF;
            
                        IF ANNOS_DIF < 0 THEN
                            SET ANNOS_DIF = 0;
                        END IF;
            
                        SET V_DIASNORMALIZACION = DIAS_HASTA_FIN + DIAS_FIN + 30*MESES_DIF + 30*12*ANNOS_DIF;
                        SET V_VALORINTERESNORMALIZACION = ((((V_VALORDESEMBOLSO_PRO * V_TASANMV) / 100) / 30) * V_DIASNORMALIZACION);
                        SET V_TOTALINTERESNORMALIZACION = V_TOTALINTERESNORMALIZACION + V_VALORINTERESNORMALIZACION;
                        SET ES_DESEMBOLSO = 1;
            
                        -- INICIALIZA VARIABLES DE TRABAJO
                        SET V_DIASNORMALIZACION = 0;
                        SET V_VALORINTERESNORMALIZACION = 0;
                        SET DIAS_HASTA_FIN = 0;
                        SET DIAS_FIN = 0;
                        SET MESES_DIF = 0;
                        SET ANNOS_DIF = 0;
            
                        SELECT PLAMDEFECHAVENCIMIENTOCUOTA
                        INTO FECHA_VECIMIENTO
                        FROM PLAN_AMORTIZACION_DEF
                        WHERE PROYECTO_ID = P_NUMEROPROYECTO
                        AND PLAMDENUMEROCUOTA = V_ULTIMACUOTA-1;
            
                        -- SELECT DATEDIFF(V_FECHANORMALIZACION, FECHA_VECIMIENTO) INTO V_DIASNORMALIZACION;
                        SELECT 30-DAY(FECHA_VECIMIENTO) INTO DIAS_HASTA_FIN;
                        SELECT DAY(V_FECHANORMALIZACION) INTO DIAS_FIN;
                        SELECT YEAR(V_FECHANORMALIZACION) - YEAR(FECHA_VECIMIENTO) - 1 INTO ANNOS_DIF;
                        IF ANNOS_DIF >= 0 THEN
                            SELECT MONTH(V_FECHANORMALIZACION) - MONTH(FECHA_VECIMIENTO) + 11 INTO MESES_DIF;
                        ELSE 
                            SELECT MONTH(V_FECHANORMALIZACION) - MONTH(FECHA_VECIMIENTO) - 1 INTO MESES_DIF;
                        END IF;
            
                        IF DIAS_HASTA_FIN < 0 THEN
                            SET DIAS_HASTA_FIN = 0;
                        END IF;
            
                        IF MESES_DIF < 0 THEN
                            SET MESES_DIF = 0;
                        END IF;
            
                        IF ANNOS_DIF < 0 THEN
                            SET ANNOS_DIF = 0;
                        END IF;
            
                        SET V_DIASNORMALIZACION = DIAS_HASTA_FIN + DIAS_FIN + 30*MESES_DIF + 30*12*ANNOS_DIF;
                        SET V_VALORINTERESNORMALIZACION = (((((V_VALORCREDITO - V_VALORDESEMBOLSO_PRO) * V_TASANMV) / 100) / 30) * V_DIASNORMALIZACION);
                        SET V_TOTALINTERESNORMALIZACION = V_TOTALINTERESNORMALIZACION + V_VALORINTERESNORMALIZACION;
                        SET E_NORMALIZACION = 1;
            
                    END IF;
                END IF;
            
                -- SE BUSCA EL VALOR DEL SEGURO
                SELECT PROYECTOSVALORSEGUROVIDA 
                INTO V_VALORSEGURO
                FROM PROYECTOS 
                WHERE ID = P_NUMEROPROYECTO
                LIMIT 1;
            
                IF V_VALORSEGURO IS NULL THEN
                    SET V_VALORSEGURO = 0;
                END IF;
                
                -- SE BUSCA EL VALOR DE LA CUOTA APROBADA
                SELECT PROYECTOSVALORCUOTAAPROBADA 
                INTO V_VALORCUOTAAPROBADA 
                FROM PROYECTOS 
                WHERE ID = P_NUMEROPROYECTO
                LIMIT 1;
            
                IF V_FECHANORMALIZACION IS NOT NULL THEN
                    IF V_FECHANORMALIZACION > V_FECHAVENCIMIENTO THEN
                        SELECT CASE WHEN DATEDIFF((SELECT LAST_DAY(V_FECHANORMALIZACION)), V_FECHANORMALIZACION) = 0 THEN 1 ELSE 0 END INTO ISLASTDAY;
                        SELECT EXTRACT(DAY FROM V_FECHANORMALIZACION) INTO DIADELMES;
                    ELSE 
                        SELECT CASE WHEN DATEDIFF((SELECT LAST_DAY(V_FECHAVENCIMIENTO)), V_FECHAVENCIMIENTO) = 0 THEN 1 ELSE 0 END INTO ISLASTDAY;
                        SELECT EXTRACT(DAY FROM V_FECHAVENCIMIENTO) INTO DIADELMES;
                    END IF;
                END IF;
            
                -- SE GENERA EL PLAN AMORTIZACIÓN DEFINITIVO
                SET V_VALORSALDOMES = V_VALORCREDITO;
            
                label: WHILE V_VALORSALDOMES > 0 DO
                    -- CREDITO
                    SET V_VALORSALDOMES = V_VALORSALDOMES - V_VALORCAPITALMES;
                    SET V_VALORINTERESMES = (V_VALORSALDOMES * V_TASANMV) / 100;
                    
                    IF V_NCUOTAMES = 1 AND P_TIPOPLAN <> 'APR' THEN
                        IF REGISTROS <> 0 THEN
                            IF V_FECHANORMALIZACION > V_FECHAVENCIMIENTO THEN
                                SET V_VALORINTERESMES = V_TOTALINTERESNORMALIZACION;
                            ELSE
                                SET V_VALORINTERESMES = V_VALORINTERESMES + V_TOTALINTERESNORMALIZACION;
                            END IF;
                        ELSE
                            SET V_VALORINTERESMES = V_TOTALINTERESNORMALIZACION;
                        END IF;
                        IF V_TOTALINTERESNORMALIZACION > 0 OR E_NORMALIZACION = 1 THEN
                            SET V_FECHAVENCIMIENTO = V_FECHANORMALIZACION;
                            SET E_NORMALIZACION = 0;
                        END IF;
                    END IF;
            
                    IF V_VALORSALDOMES > V_VALORCAPITALMES THEN
                        IF V_VALORSALDOMES	> V_VALORCUOTAAPROBADA THEN
                            SET V_VALORCAPITALMES = V_VALORCUOTAAPROBADA - V_VALORINTERESMES - V_VALORSEGURO;
                        ELSE
                            SET V_VALORCAPITALMES = V_VALORSALDOMES;
                        END IF;
                    ELSE
                        SET V_VALORCAPITALMES = V_VALORSALDOMES;
                    END IF;
            
                    
                    IF ES_DESEMBOLSO = 1 THEN
                        SET ES_DESEMBOLSO = 0;
                    ELSE 	
                        IF V_NCUOTAMES <> 1 OR REGISTROS <> 0 OR P_TIPOPLAN = 'APR' THEN
                            SET V_FECHAVENCIMIENTO = DATE_ADD(V_FECHAVENCIMIENTO, INTERVAL 1 MONTH);
                        END IF;
                    END IF;
            
                    IF V_FECHANORMALIZACION IS NOT NULL THEN
                        IF ISLASTDAY = 1 THEN 
                            SELECT LAST_DAY(V_FECHAVENCIMIENTO) INTO V_FECHAVENCIMIENTO;
                        ELSEIF (DIADELMES = 30 OR DIADELMES = 29) AND (SELECT EXTRACT(MONTH FROM V_FECHAVENCIMIENTO)) = 3 THEN
                            IF (SELECT EXTRACT(DAY FROM V_FECHAVENCIMIENTO)) = 29 THEN
                                IF DIADELMES = 30 THEN
                                    SET V_FECHAVENCIMIENTO = DATE_ADD(V_FECHAVENCIMIENTO, INTERVAL 1 DAY);
                                END IF;
                            ELSEIF (SELECT EXTRACT(DAY FROM V_FECHAVENCIMIENTO)) = 28 THEN
                                IF DIADELMES = 29 THEN
                                    SET V_FECHAVENCIMIENTO = DATE_ADD(V_FECHAVENCIMIENTO, INTERVAL 1 DAY);
                                ELSE
                                    SET V_FECHAVENCIMIENTO = DATE_ADD(V_FECHAVENCIMIENTO, INTERVAL 2 DAY);
                                END IF;
                            END IF;
                        END IF;
                    END IF;
            
                    IF P_TIPOPLAN <> 'REG' THEN
                        INSERT INTO PLAN_AMORTIZACION 
                            (
                                PROYECTO_ID,
                                PLAAMONUMEROCUOTA,
                                PLAAMOFECHAVENCIMIENTOCUOTA,
                                PLAAMOVALORSALDOCAPITAL,
                                PLAAMOVALORCAPITALCUOTA,
                                PLAAMOVALORINTERESCUOTA,
                                PLAAMOVALORSEGUROCUOTA,
                                PLAAMOVALORINTERESMORA,
                                PLAAMODIASMORA,
                                PLAAMOFECHAULTIMOPAGOCUOTA,
                                PLAAMOCUOTACANCELADA,
                                PLAAMOESTADOPLANAMORTIZACION,
                                PLAAMOESTADO,
                                USUARIO_CREACION_ID,
                                USUARIO_CREACION_NOMBRE,
                                USUARIO_MODIFICACION_ID,
                                USUARIO_MODIFICACION_NOMBRE,
                                CREATED_AT,
                                UPDATED_AT
                            )
                        VALUES
                            (
                                P_NUMEROPROYECTO,
                                V_NCUOTAMES,	
                                V_FECHAVENCIMIENTO,
                                V_VALORSALDOMES,
                                V_VALORCAPITALMES,
                                V_VALORINTERESMES,
                                V_VALORSEGURO,
                                0, -- INTERES DE MORA
                                0, -- DIAS DE MORA
                                SYSDATE(), -- FECHA ULTIMO PAGO
                                'N', -- CUOTA CANCELADA
                                P_TIPOPLAN,
                                1,
                                P_USUARIOID,
                                P_USUARIO,
                                P_USUARIOID,
                                P_USUARIO,
                                SYSDATE(),
                                SYSDATE()
                            );
            
                        SET V_NCUOTAMES = V_NCUOTAMES + 1;
                    ELSE
                        INSERT INTO PLAN_AMORTIZACION_DEF
                            (
                                PROYECTO_ID,
                                PLAMDENUMEROCUOTA,
                                PLAMDEFECHAVENCIMIENTOCUOTA,
                                PLAMDEVALORSALDOCAPITAL,
                                PLAMDEVALORCAPITALCUOTA,
                                PLAMDEVALORINTERESCUOTA,
                                PLAMDEVALORSEGUROCUOTA,
                                PLAMDEVALORINTERESMORA,
                                PLAMDEDIASMORA,
                                PLAMDEFECHAULTIMOPAGOCUOTA,
                                PLAMDECUOTACANCELADA,
                                PLAMDEESTADOPLANAMORTIZACION,
                                PLAMDEESTADO,
                                USUARIO_CREACION_ID,
                                USUARIO_CREACION_NOMBRE,
                                USUARIO_MODIFICACION_ID,
                                USUARIO_MODIFICACION_NOMBRE,
                                CREATED_AT,
                                UPDATED_AT
                            )
                        VALUES
                            (
                                P_NUMEROPROYECTO,
                                V_ULTIMACUOTA,	
                                V_FECHAVENCIMIENTO,
                                V_VALORSALDOMES,
                                V_VALORCAPITALMES,
                                V_VALORINTERESMES,
                                V_VALORSEGURO,
                                0, -- INTERES DE MORA
                                0, -- DIAS DE MORA
                                SYSDATE(), -- FECHA ULTIMO PAGO
                                'N', -- CUOTA CANCELADA
                                P_TIPOPLAN,
                                1,
                                P_USUARIOID,
                                P_USUARIO,
                                P_USUARIOID,
                                P_USUARIO,
                                SYSDATE(),
                                SYSDATE()
                            );
            
                        SET V_ULTIMACUOTA = V_ULTIMACUOTA + 1;
                        SET V_NCUOTAMES = V_NCUOTAMES + 1;
                    END IF;
            
                    IF V_VALORSALDOMES <= V_VALORCAPITALMES THEN
                        SET V_VALORSALDOMES = 0;
                    END IF;
                END WHILE label;
            
                -- SE VALIDA SI ES PLAN DEFINITIVO
                IF P_PLANDEF = 'S' THEN
                    DELETE FROM PLAN_AMORTIZACION_DEF WHERE PROYECTO_ID = P_NUMEROPROYECTO;
            
                    INSERT PLAN_AMORTIZACION_DEF (
                        PROYECTO_ID,
                        PLAMDENUMEROCUOTA,
                        PLAMDEFECHAVENCIMIENTOCUOTA,
                        PLAMDEVALORSALDOCAPITAL,
                        PLAMDEVALORCAPITALCUOTA,
                        PLAMDEVALORINTERESCUOTA,
                        PLAMDEVALORSEGUROCUOTA,
                        PLAMDEVALORINTERESMORA,
                        PLAMDEDIASMORA,
                        PLAMDEFECHAULTIMOPAGOCUOTA,
                        PLAMDECUOTACANCELADA,
                        PLAMDEESTADOPLANAMORTIZACION,
                        PLAMDEESTADO,
                        USUARIO_CREACION_ID,
                        USUARIO_CREACION_NOMBRE,
                        USUARIO_MODIFICACION_ID,
                        USUARIO_MODIFICACION_NOMBRE,
                        CREATED_AT,
                        UPDATED_AT
                    )
                    SELECT PROYECTO_ID,
                        PLAAMONUMEROCUOTA,
                        PLAAMOFECHAVENCIMIENTOCUOTA,
                        PLAAMOVALORSALDOCAPITAL,			
                        PLAAMOVALORCAPITALCUOTA,			
                        PLAAMOVALORINTERESCUOTA,			
                        PLAAMOVALORSEGUROCUOTA,			
                        PLAAMOVALORINTERESMORA,			
                        PLAAMODIASMORA,			
                        PLAAMOFECHAULTIMOPAGOCUOTA,			
                        PLAAMOCUOTACANCELADA,
                        'DEF',	
                        1,
                        P_USUARIOID,
                        P_USUARIO,
                        P_USUARIOID,
                        P_USUARIO,
                        SYSDATE(),
                        SYSDATE()		
                    FROM PLAN_AMORTIZACION 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO;
                END IF;

                IF V_VALORSEGURO <> 0 THEN
                    UPDATE PLAN_AMORTIZACION_DEF 
                    SET PLAMDEVALORSEGUROCUOTA = V_VALORSEGURO*2,
                    UPDATED_AT = SYSDATE() 
                    WHERE PROYECTO_ID = P_NUMEROPROYECTO
                    ORDER BY ID DESC
                    LIMIT 1;
                END IF;
            
                SELECT CONCAT('Proceso terminó correctamente - Proyecto : ', CAST(P_NUMEROPROYECTO AS CHAR)) INTO V_ERRORMENSAJE;
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
                        V_ERRORMENSAJE,
                        P_USUARIOID,
                        P_USUARIO,
                        SYSDATE(),
                        SYSDATE()
                    );
            END;";
        DB::unprepared($procedure2);
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
