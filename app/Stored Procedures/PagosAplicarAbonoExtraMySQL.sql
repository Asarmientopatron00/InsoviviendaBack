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
			
		IF V_VALORCREDITO+SALDO_UNIFICADO < V_VALORABONOSTOTAL THEN
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
			P_FECHAPAGO, 
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
END;