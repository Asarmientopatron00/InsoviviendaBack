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
        $procedure = "DROP PROCEDURE IF EXISTS `SP_PlanAmortizacionGenerar`;
            CREATE PROCEDURE SP_PlanAmortizacionGenerar
            (
                P_NUMEROPROYECTO INT,
                P_TIPOPLAN VARCHAR(3),
                P_PLANDEF VARCHAR(1),
                P_TRANSACCION VARCHAR(30),
                P_USUARIOID INT,
                P_USUARIO VARCHAR(10)
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
            
                                SELECT DATEDIFF(V_FECHANORMALIZACION, V_FECHADESEMBOLSO_PRO) INTO V_DIASNORMALIZACION;
            
                                SET V_VALORINTERESNORMALIZACION = ((((V_VALORDESEMBOLSO_PRO * V_TASANMV) / 100) / 30) * V_DIASNORMALIZACION);
            
                                SET V_TOTALINTERESNORMALIZACION = V_TOTALINTERESNORMALIZACION + V_VALORINTERESNORMALIZACION;
            
                                -- INICIALIZA VARIABLES DE TRABAJO
                                SET V_DIASNORMALIZACION = 0;
                                SET V_VALORINTERESNORMALIZACION = 0;
            
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
            
                        SELECT DATEDIFF(V_FECHANORMALIZACION, V_FECHADESEMBOLSO_PRO) INTO V_DIASNORMALIZACION;
            
                        SET V_VALORINTERESNORMALIZACION = ((((V_VALORDESEMBOLSO_PRO * V_TASANMV) / 100) / 30) * V_DIASNORMALIZACION);
                        SET V_TOTALINTERESNORMALIZACION = V_TOTALINTERESNORMALIZACION + V_VALORINTERESNORMALIZACION;
                        SET ES_DESEMBOLSO = 1;
            
                        -- INICIALIZA VARIABLES DE TRABAJO
                        SET V_DIASNORMALIZACION = 0;
                        SET V_VALORINTERESNORMALIZACION = 0;
            
                        SELECT PLAMDEFECHAVENCIMIENTOCUOTA
                        INTO FECHA_VECIMIENTO
                        FROM PLAN_AMORTIZACION_DEF
                        WHERE PROYECTO_ID = P_NUMEROPROYECTO
                        AND PLAMDENUMEROCUOTA = V_ULTIMACUOTA-1;
            
                        SELECT DATEDIFF(V_FECHANORMALIZACION, FECHA_VECIMIENTO) INTO V_DIASNORMALIZACION;
            
                        SET V_VALORINTERESNORMALIZACION = (((((V_VALORCREDITO - V_VALORDESEMBOLSO_PRO) * V_TASANMV) / 100) / 30) * V_DIASNORMALIZACION);
                        SET V_TOTALINTERESNORMALIZACION = V_TOTALINTERESNORMALIZACION + V_VALORINTERESNORMALIZACION;
            
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
                    
                    IF V_NCUOTAMES = 1 THEN
                        IF REGISTROS <> 0 THEN
                            IF V_FECHANORMALIZACION > V_FECHAVENCIMIENTO THEN
                                SET V_VALORINTERESMES = V_TOTALINTERESNORMALIZACION;
                            ELSE
                                SET V_VALORINTERESMES = V_VALORINTERESMES + V_TOTALINTERESNORMALIZACION;
                            END IF;
                        ELSE
                            SET V_VALORINTERESMES = V_TOTALINTERESNORMALIZACION;
                        END IF;
                        IF V_TOTALINTERESNORMALIZACION > 0 THEN
                            SET V_FECHAVENCIMIENTO = V_FECHANORMALIZACION;
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
                        IF V_NCUOTAMES <> 1 OR REGISTROS <> 0 THEN
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
