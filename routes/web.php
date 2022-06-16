<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonasEntidades;
use App\Http\Controllers\Proyectos;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(["prefix" => "personas"],function(){
    Route::get('/informe-personas', [PersonasEntidades\PersonaController::class,'informePersonas'])->name('personas.informeParticipantes');
        // ->middleware(['permission:ListarColaborador']);
});

Route::group(["prefix" => "pagos"],function(){
    Route::get('/', [Proyectos\PagoController::class,'listaPagos'])->name('pagos.listaPagos');
    Route::get('/{id}', [Proyectos\PagoController::class,'factura'])->name('pagos.factura');
        // ->middleware(['permission:ListarColaborador']);
});

Route::group(["prefix" => "proyectos"],function(){
    Route::get('/plan-amortizacion', [Proyectos\PlanAmortizacionController::class,'descargaPlanAmortizacion'])->name('plan-amortizacion.descargaPlanAmortizacion');
    Route::get('/plan-amortizacion-definitivo', [Proyectos\PlanAmortizacionDefinitivoController::class,'descargaPlanAmortizacionDefinitivo'])->name('plan-amortizacion-definitivo.descargaPlanAmortizacionDefinitivo');
    Route::get('/desembolso', [Proyectos\DesembolsoController::class,'descargaDesembolso'])->name('desembolso.descargaDesembolso');
        // ->middleware(['permission:ListarColaborador']);
});

