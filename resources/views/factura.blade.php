<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura</title>
    <link rel="stylesheet" href="css/app.css">
  </head>
  <body class="antialiased">
    <body class="antialiased">
      <header class="header">
        <div class="container">
          <div class="container-div">
            <img class="main-logo" src="img/logo-home.png"/>
          </div>
          <div class="container-div">
            <div class="main-info center">
              <img class="slogan" src="img/logo-slogan.png"/>
              <h3>NIT: 890908956-3</h3>
              <h3>TELÉFONOS: 2314342 - 2316841</h3>
              <h3>MEDELLÍN</h3>
            </div>
          </div>
          <div class="container-div">
            <div class="pay-container">
              <h1 class="right">RECIBO DE CAJA</h1>
              <div>
                <div style="display: inline-block; width: 108px">
                  <h2 style="color: darkred; font-size: 18px; text-align: left; padding-left: 70px">N°</h2>
                </div>
                <div style="display: inline-block; width: 108px" >
                  <h2 style="color: darkred; font-size: 18px; text-align: right">71</h2>
                </div>
              </div>
              <div class="pay-info">
                <div style="display: inline-block; width: 108px">
                  <h2 style="font-size: 18px; text-align: left; padding-left: 70px">POR:</h2>
                </div>
                <div style="display: inline-block; width: 108px; border-style: solid; border-width: 1px; border-radius: 5px" >
                  <h2 style="font-size: 18px; text-align: right">{{number_format($pago->pagosValorTotalPago, 2, '.', ',')}}</h2>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>
      <br/>
      <main class="main">
        <div class="main-container">
          <div class="customer">
            <h2 style="font-size: 12px">RECIBIMOS DE:</h2>
            <h2 class="bold">
              {{
                $pago->proyecto->solicitante->personasNombres.' '
                .$pago->proyecto->solicitante->personasPrimerApellido.' '
                .$pago->proyecto->solicitante->personasSegundoApellido
              }}
            </h2>
          </div>
          <div class="customer-id">
            <h2 style="font-size: 12px">IDENTIFICACIÓN:</h2>
            <h2 class="bold">{{$pago->proyecto->solicitante->personasIdentificacion}}</h2>
          </div>
          <div class="payment">
            <h2 style="font-size: 12px">LA SUMA DE:</h2>
            <h2 class="bold">{{$numberToWord}}</h2>
          </div>
          <div class="concept">
            <h2 style="font-size: 12px">CONCEPTO:</h2>
            <h2 class="bold">{{$pago->pagosDescripcionPago}}</h2>
          </div>
          <div class="payment-info">
            <div class="payment-info-div">
              <h2 style="font-size: 12px; padding-top: 4px">ABONO A CAPITAL:</h2>
              <h2 class="bold bottom">{{number_format($totales->capital, 2, '.', ',')}}</h2>
            </div>
            <div class="payment-info-div taxes">
              <div class="taxes-div taxes-div-first">
                <h2 style="font-size: 12px">INTERÉS ORDINARIO:</h2>
                <h2 class="bold bottom">{{number_format($totales->interesCuota, 2, '.', ',')}}</h2>  
              </div>
              <div class="taxes-div">
                <h2 style="font-size: 12px">INTERÉS MORA:</h2>
                <h2 class="bold bottom">{{number_format($totales->interesMora, 2, '.', ',')}}</h2>  
              </div>
            </div>
            <div class="payment-info-div taxes">
              <div class="taxes-div taxes-div-first">
                <h2 style="font-size: 12px">TOTAL INTERESES:</h2>
                <h2 class="bold bottom">{{number_format($totales->interesMora+$totales->interesCuota, 2, '.', ',')}}</h2>
              </div>
              <div class="taxes-div">
                <h2 style="font-size: 12px">SEGURO:</h2>
                <h2 class="bold bottom">{{number_format($totales->seguro, 2, '.', ',')}}</h2>
              </div>
            </div>
          </div>
          <div class="consignment-info">
            <div class="consignment-info-div consignment-info-div-first">
              <h2 style="font-size: 12px">FECHA CONSIGNACIÓN:</h3>
              <h2 class="bold">{{$pago->pagosFechaPago}}</h2>
            </div>
            <div class="consignment-info-div">
              <h2 style="font-size: 12px">BANCO:</h3>
              <h2 class="bold">{{$pago->proyecto->banco->bancosDescripcion}}</h2>
            </div>
            <div class="consignment-info-div">
              <h2 style="font-size: 12px">SALDO CARTERA:</h3>
              <h2 class="bold">$4.800.000</h2>
            </div>
          </div>
          <div class="sign-info">
            <div class="sign-info-div sign-info-div-first">
              <h2 style="font-size: 12px">FECHA ELABORACIÓN:</h3>
              <h2 class="bold">{{date_format($pago->created_at, "Y-m-d")}}</h2>
            </div>
            <div class="sign-info-div">
              <h2 style="font-size: 12px">FIRMA AUTORIZADA:</h3>
              <h2 style="font-size: 12px; padding-top: 10px">___________________________________________________________________________________</h3>
            </div>
          </div>
        </div>
      </main>
    </body>
  </body>
</html>
  
