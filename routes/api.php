<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Seguridad;
use App\Http\Controllers\PersonasEntidades;
use App\Http\Controllers\Parametrizacion;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/users/token', [UserController::class,'getToken']);
Route::post('/forgot-password', [UserController::class,'forgotPassword'])->middleware('guest')->name('password.email');
Route::post('/reset-password',[UserController::class,'resetPassword'])->middleware('guest')->name('password.update');
Route::post('/register', [UserController::class, 'register'])->name('register.api');
Route::post('/login', [UserController::class, 'login'])->name('login.api');

Route::group(['middleware' => ['auth:api']], function (){
    // User
    Route::group(["prefix" => "users"],function(){
        Route::get('current/session',  [UserController::class,'getSession'])->name('session.show');
    });

    // ---------------------- Seguridad -------------------------- //

    // Usuarios
    Route::group(["prefix" => "usuarios"],function(){
        Route::get('/', [Seguridad\UsuarioController::class,'index'])->name('usuarios.index');
        Route::post('/', [Seguridad\UsuarioController::class,'store'])->name('usuarios.store');
            // ->middleware(['permission:CrearUsuario']);
        Route::get('/{id}', [Seguridad\UsuarioController::class,'show'])->name('usuarios.show');
            // ->middleware(['permission:ListarUsuario']);
        Route::put('/{id}', [Seguridad\UsuarioController::class,'update'])->name('usuarios.update');
            // ->middleware(['permission:ModificarUsuario']);
        Route::delete('/{id}', [Seguridad\UsuarioController::class,'destroy'])->name('usuarios.delete');
            // ->middleware(['permission:EliminarUsuario']);
    });

    // Roles
    Route::group(["prefix" => "roles"],function(){
        Route::get('/', [Seguridad\RolController::class,'index'])->name('roles.index');
        Route::get('/permisos/{id}', [Seguridad\RolController::class,'obtenerPermisos'])->name('roles.permisos');
            // ->middleware(['permission:PermitirRol']);
        Route::post('/permisos', [Seguridad\RolController::class,'otorgarPermisos'])->name('roles.otorgarPermisos');
            // ->middleware(['permission:PermitirRol']);
        Route::put('/permisos', [Seguridad\RolController::class,'revocarPermisos'])->name('roles.revocarPermisos');
            // ->middleware(['permission:PermitirRol']);
        Route::post('/', [Seguridad\RolController::class,'store'])->name('roles.store');
            // ->middleware(['permission:CrearRol']);
        Route::get('/{id}', [Seguridad\RolController::class,'show'])->name('roles.show');
            // ->middleware(['permission:ListarRol']);
        Route::put('/{id}', [Seguridad\RolController::class,'update'])->name('roles.update');
            // ->middleware(['permission:ModificarRol']);
        Route::delete('/{id}', [Seguridad\RolController::class,'destroy'])->name('roles.delete');
            // ->middleware(['permission:EliminarRol']);
    });

    // Aplicaciones
    Route::group(["prefix" => "aplicaciones"],function(){
        Route::get('/', [Seguridad\AplicacionController::class,'index'])->name('aplicaciones.index');
        Route::post('/', [Seguridad\AplicacionController::class,'store'])->name('aplicaciones.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Seguridad\AplicacionController::class,'show'])->name('aplicaciones.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Seguridad\AplicacionController::class,'update'])->name('aplicaciones.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Seguridad\AplicacionController::class,'destroy'])->name('aplicaciones.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Módulos
    Route::group(["prefix" => "modulos"],function(){
        Route::get('/', [Seguridad\ModuloController::class,'index'])->name('modulos.index');
        Route::post('/', [Seguridad\ModuloController::class,'store'])->name('modulos.store');
            // ->middleware(['permission:CrearModulo']);
        Route::get('/{id}', [Seguridad\ModuloController::class,'show'])->name('modulos.show');
            // ->middleware(['permission:ListarModulo']);
        Route::put('/{id}', [Seguridad\ModuloController::class,'update'])->name('modulos.update');
            // ->middleware(['permission:ModificarModulo']);
        Route::delete('/{id}', [Seguridad\ModuloController::class,'destroy'])->name('modulos.delete');
            // ->middleware(['permission:EliminarModulo']);
    });

    // Opciones del Sistema
    Route::group(["prefix" => "opciones-del-sistema"],function(){
        Route::get('/', [Seguridad\OpcionSistemaController::class,'index'])->name('opciones-del-sistema.index');
        Route::post('/', [Seguridad\OpcionSistemaController::class,'store'])->name('opciones-del-sistema.store');
            // ->middleware(['permission:CrearOpcionSistema']);
        Route::get('/{id}', [Seguridad\OpcionSistemaController::class,'show'])->name('opciones-del-sistema.show');
            // ->middleware(['permission:ListarOpcionSistema']);
        Route::put('/{id}', [Seguridad\OpcionSistemaController::class,'update'])->name('opciones-del-sistema.update');
            // ->middleware(['permission:ModificarOpcionSistema']);
        Route::delete('/{id}', [Seguridad\OpcionSistemaController::class,'destroy'])->name('opciones-del-sistema.delete');
            // ->middleware(['permission:EliminarOpcionSistema']);
    });

    // Permisos
    Route::group(["prefix" => "permisos"],function(){
        Route::get('/', [Seguridad\PermisoController::class,'index'])->name('permisos.index');
        Route::post('/', [Seguridad\PermisoController::class,'store'])->name('permisos.store');
            // ->middleware(['permission:CrearAccionPermiso']);
        Route::get('/{id}', [Seguridad\PermisoController::class,'show'])->name('permisos.show');
            // ->middleware(['permission:ListarAccionPermiso']);
        Route::put('/{id}', [Seguridad\PermisoController::class,'update'])->name('permisos.update');
            // ->middleware(['permission:ModificarAccionPermiso']);
        Route::delete('/{id}', [Seguridad\PermisoController::class,'destroy'])->name('permisos.delete');
            // ->middleware(['permission:EliminarAccionPermiso']);
    });

    // Auditoria Tablas
    Route::group(["prefix" => "auditoria-tablas"],function(){
        Route::get('/', [Seguridad\AuditoriaTablaController::class,'index'])->name('auditoria-tablas.index');
            // ->middleware(['permission:ListarAuditorias']);
    });

    // ---------------------- Personas/Entidades -------------------------- //

    // Personas
    Route::group(["prefix" => "personas"],function(){
        Route::get('/', [PersonasEntidades\PersonaController::class,'index'])->name('modulos.index');
        Route::post('/', [PersonasEntidades\PersonaController::class,'store'])->name('modulos.store');
            // ->middleware(['permission:CrearModulo']);
        Route::get('/{id}', [PersonasEntidades\PersonaController::class,'show'])->name('modulos.show');
            // ->middleware(['permission:ListarModulo']);
        Route::put('/{id}', [PersonasEntidades\PersonaController::class,'update'])->name('modulos.update');
            // ->middleware(['permission:ModificarModulo']);
        Route::delete('/{id}', [PersonasEntidades\PersonaController::class,'destroy'])->name('modulos.delete');
            // ->middleware(['permission:EliminarModulo']);
    });

    // ---------------------- Parametrizacion -------------------------- //
    // Tipos Identificacion
    Route::group(["prefix" => "tipos-identificacion"],function(){
        Route::get('/', [Parametrizacion\TipoIdentificacionController::class,'index'])->name('tipos_identificacion.index');
        Route::post('/', [Parametrizacion\TipoIdentificacionController::class,'store'])->name('tipos_identificacion.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoIdentificacionController::class,'show'])->name('tipos_identificacion.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoIdentificacionController::class,'update'])->name('tipos_identificacion.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoIdentificacionController::class,'destroy'])->name('tipos_identificacion.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });
    // Condicion Familia
    Route::group(["prefix" => "condiciones-familia"],function(){
        Route::get('/', [Parametrizacion\CondicionFamiliaController::class,'index'])->name('condicion_familia.index');
        Route::post('/', [Parametrizacion\CondicionFamiliaController::class,'store'])->name('condicion_familia.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\CondicionFamiliaController::class,'show'])->name('condicion_familia.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\CondicionFamiliaController::class,'update'])->name('condicion_familia.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\CondicionFamiliaController::class,'destroy'])->name('condicion_familia.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });
     // Tipos población
     Route::group(["prefix" => "tipos-poblacion"],function(){
        Route::get('/', [Parametrizacion\TipoPoblacionController::class,'index'])->name('tipos_poblacion.index');
        Route::post('/', [Parametrizacion\TipoPoblacionController::class,'store'])->name('tipos_poblacion.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoPoblacionController::class,'show'])->name('tipos_poblacion.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoPoblacionController::class,'update'])->name('tipos_poblacion.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoPoblacionController::class,'destroy'])->name('tipos_poblacion.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });
     // Tipos vivienda
     Route::group(["prefix" => "tipos-vivienda"],function(){
        Route::get('/', [Parametrizacion\TipoViviendaController::class,'index'])->name('tipo_vivienda.index');
        Route::post('/', [Parametrizacion\TipoViviendaController::class,'store'])->name('tipos_vivienda.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoViviendaController::class,'show'])->name('tipos_vivienda.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoViviendaController::class,'update'])->name('tipos_vivienda.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoViviendaController::class,'destroy'])->name('tipos_vivienda.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });
     // Tipos techo
     Route::group(["prefix" => "tipos-techo"],function(){
        Route::get('/', [Parametrizacion\TipoTechoController::class,'index'])->name('tipo_techo.index');
        Route::post('/', [Parametrizacion\TipoTechoController::class,'store'])->name('tipos_techo.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoTechoController::class,'show'])->name('tipos_techo.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoTechoController::class,'update'])->name('tipos_techo.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoTechoController::class,'destroy'])->name('tipos_techo.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });    
     // Tipos piso
     Route::group(["prefix" => "tipos-piso"],function(){
        Route::get('/', [Parametrizacion\TipoPisoController::class,'index'])->name('tipo_Piso.index');
        Route::post('/', [Parametrizacion\TipoPisoController::class,'store'])->name('tipos_Piso.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoPisoController::class,'show'])->name('tipos_Piso.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoPisoController::class,'update'])->name('tipos_Piso.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoPisoController::class,'destroy'])->name('tipos_Piso.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });        
     // Tipos division
     Route::group(["prefix" => "tipos-division"],function(){
        Route::get('/', [Parametrizacion\TipoDivisionController::class,'index'])->name('tipo_Division.index');
        Route::post('/', [Parametrizacion\TipoDivisionController::class,'store'])->name('tipos_Division.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoDivisionController::class,'show'])->name('tipos_Division.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoDivisionController::class,'update'])->name('tipos_Division.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoDivisionController::class,'destroy'])->name('tipos_Division.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });            
});