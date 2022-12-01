<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{'Recibo de Caja '.$registro->consecutivo}}</title>
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
              <h3 style="font-size: 13px">TELÉFONOS: 5407880 - 3126012080</h3>
              <h3>MEDELLÍN</h3>
            </div>
          </div>
          <div class="container-div">
            <div class="pay-container">
              <h1 class="right" style="font-size: 20px">RECIBO DE CAJA</h1>
              <div>
                <div style="display: inline-block; width: 108px">
                  <h2 style="color: darkred; font-size: 18px; text-align: left; padding-left: 55px">N°</h2>
                </div>
                <div style="display: inline-block; width: 108px" >
                  <h2 style="color: darkred; font-size: 18px; text-align: right">{{$registro->consecutivo}}</h2>
                </div>
              </div>
              <div class="pay-info">
                <div style="display: inline-block; width: 95px">
                  <h2 style="font-size: 16px; text-align: left; padding-left: 55px">POR$</h2>
                </div>
                <div style="display: inline-block; width: 108px; border-style: solid; border-width: 1px; border-radius: 5px; margin-left: 10px" >
                  <h2 style="font-size: 16px; text-align: right">{{number_format($registro->valor, 2, '.', ',')}}</h2>
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
            <h2 class="bold">{{$registro->persona}}</h2>
          </div>
          <div class="customer-id">
            <h2 style="font-size: 12px">IDENTIFICACIÓN:</h2>
            <h2 class="bold">{{$registro->identificacion}}</h2>
          </div>
          <div class="payment">
            <h2 style="font-size: 12px">LA SUMA DE:</h2>
            <h2 class="bold">{{$registro->numberToWord}}</h2>
          </div>
          <div class="concept">
            <h2 style="font-size: 12px">CONCEPTO:</h2>
            <h2 class="bold">{{$registro->concepto}}</h2>
          </div>
          <div class="payment-info">
            <div class="payment-info-div">
              <h2 style="font-size: 12px; padding-top: 6px">ABONO A CAPITAL:</h2>
              <h2 class="bold bottom">{{$registro->capital != '' ? number_format($registro->capital, 2, '.', ',') : ''}}</h2>
            </div>
            <div class="payment-info-div taxes">
              <div class="taxes-div taxes-div-first">
                <h2 style="font-size: 12px">INTERÉS ORDINARIO:</h2>
                <h2 class="bold bottom">{{$registro->interesCuota != '' ? number_format($registro->interesCuota, 2, '.', ',') : ''}}</h2>  
              </div>
              <div class="taxes-div">
                <h2 style="font-size: 12px">INTERÉS MORA:</h2>
                <h2 class="bold bottom">{{$registro->interesMora != '' ? number_format($registro->interesMora, 2, '.', ',') : ''}}</h2>  
              </div>
            </div>
            <div class="payment-info-div taxes">
              <div class="taxes-div taxes-div-first">
                <h2 style="font-size: 12px">TOTAL INTERESES:</h2>
                <h2 class="bold bottom">{{$registro->interesTotal != '' ? number_format($registro->interesTotal, 2, '.', ',') : ''}}</h2>
              </div>
              <div class="taxes-div">
                <h2 style="font-size: 12px">SEGURO:</h2>
                <h2 class="bold bottom">{{$registro->seguro != '' ? number_format($registro->seguro, 2, '.', ',') : ''}}</h2>
              </div>
            </div>
          </div>
          <div class="consignment-info">
            <div class="consignment-info-div consignment-info-div-first">
              <h2 style="font-size: 12px">FECHA CONSIGNACIÓN:</h3>
              <h2 class="bold">{{$registro->fechaC}}</h2>
            </div>
            <div class="consignment-info-div">
              <h2 style="font-size: 12px">BANCO:</h3>
              <h2 class="bold">{{$registro->banco}}</h2>
            </div>
            <div class="consignment-info-div">
              <h2 style="font-size: 12px">SALDO CARTERA:</h3>
              <h2 class="bold">{{$registro->cartera != '' ? number_format($registro->cartera, 2, '.', ',') : ''}}</h2>
            </div>
          </div>
          <div class="sign-info">
            <div class="sign-info-div sign-info-div-first">
              <h2 style="font-size: 12px">FECHA ELABORACIÓN:</h3>
              <h2 class="bold">{{$registro->fechaE}}</h2>
            </div>
            <div class="sign-info-div">
              <h2 style="font-size: 12px">FIRMA AUTORIZADA:</h3>
              <h2 style="font-size: 12px; padding-top: 15px">__________________________________________________________________________</h3>
            </div>
          </div>
        </div>
        @if ($registro->estado === 0)
          <div class="watermark">
            ANULADO
          </div>
        @endif
      </main>
      <div style="font-size: 11px; margin: 8px 10px">
        Elaborador por {{$registro->elaboradoPor}}, Fecha y hora de impresión: {{$registro->fecha}}
      </div>
    </body>
  </body>
</html>
  
