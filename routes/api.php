<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Seguridad;
use App\Http\Controllers\PersonasEntidades;
use App\Http\Controllers\Parametrizacion;
use App\Http\Controllers\Proyectos;

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

    // Auditoria Proceso
    Route::group(["prefix" => "auditoria-procesos"],function(){
        Route::get('/', [Seguridad\AuditoriaProcesoController::class,'index'])->name('auditoria-procesos.index');
            // ->middleware(['permission:ListarAuditorias']);
    });

    // ---------------------- Personas/Entidades -------------------------- //

    // Personas
    Route::group(["prefix" => "personas"],function(){
        Route::get('/', [PersonasEntidades\PersonaController::class,'index'])->name('personas.index');
        Route::post('/', [PersonasEntidades\PersonaController::class,'store'])->name('personas.store');
            // ->middleware(['permission:CrearModulo']);
        Route::get('/{id}', [PersonasEntidades\PersonaController::class,'show'])->name('personas.show');
            // ->middleware(['permission:ListarModulo']);
        Route::put('/{id}', [PersonasEntidades\PersonaController::class,'update'])->name('personas.update');
            // ->middleware(['permission:ModificarModulo']);
        Route::delete('/{id}', [PersonasEntidades\PersonaController::class,'destroy'])->name('personas.delete');
            // ->middleware(['permission:EliminarModulo']);
    });

    // Familias
    Route::group(["prefix" => "familias"],function(){
        Route::get('/', [PersonasEntidades\FamiliaController::class,'index'])->name('familias.index');
        Route::post('/', [PersonasEntidades\FamiliaController::class,'store'])->name('familias.store');
            // ->middleware(['permission:CrearModulo']);
        Route::get('/{id}', [PersonasEntidades\FamiliaController::class,'show'])->name('familias.show');
            // ->middleware(['permission:ListarModulo']);
        Route::put('/{id}', [PersonasEntidades\FamiliaController::class,'update'])->name('familias.update');
            // ->middleware(['permission:ModificarModulo']);
        Route::delete('/{id}', [PersonasEntidades\FamiliaController::class,'destroy'])->name('familias.delete');
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

    // Tipos Parentesco
    Route::group(["prefix" => "tipos-parentesco"],function(){
        Route::get('/', [Parametrizacion\TipoParentescoController::class,'index'])->name('tipos_parentesco.index');
        Route::post('/', [Parametrizacion\TipoParentescoController::class,'store'])->name('tipos_parentesco.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoParentescoController::class,'show'])->name('tipos_parentesco.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoParentescoController::class,'update'])->name('tipos_parentesco.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoParentescoController::class,'destroy'])->name('tipos_parentesco.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Tipos Discapacidad
    Route::group(["prefix" => "tipos-discapacidad"],function(){
        Route::get('/', [Parametrizacion\TipoDiscapacidadController::class,'index'])->name('tipos_discapacidad.index');
        Route::post('/', [Parametrizacion\TipoDiscapacidadController::class,'store'])->name('tipos_discapacidad.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoDiscapacidadController::class,'show'])->name('tipos_discapacidad.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoDiscapacidadController::class,'update'])->name('tipos_discapacidad.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoDiscapacidadController::class,'destroy'])->name('tipos_discapacidad.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Tipos Asesorias
    Route::group(["prefix" => "tipos-orientacion"],function(){
        Route::get('/', [Parametrizacion\TipoAsesoriaController::class,'index'])->name('tipos_orientacion.index');
        Route::post('/', [Parametrizacion\TipoAsesoriaController::class,'store'])->name('tipos_orientacion.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoAsesoriaController::class,'show'])->name('tipos_orientacion.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoAsesoriaController::class,'update'])->name('tipos_orientacion.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoAsesoriaController::class,'destroy'])->name('tipos_orientacion.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Tipos Asesorias
    Route::group(["prefix" => "orientador"],function(){
        Route::get('/', [PersonasEntidades\OrientadorController::class,'index'])->name('orientadores.index');
        Route::post('/', [PersonasEntidades\OrientadorController::class,'store'])->name('orientadores.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [PersonasEntidades\OrientadorController::class,'show'])->name('orientadores.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [PersonasEntidades\OrientadorController::class,'update'])->name('orientadores.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [PersonasEntidades\OrientadorController::class,'destroy'])->name('orientadores.delete');
    });
    
    // Estados Civil
    Route::group(["prefix" => "estados-civil"],function(){
        Route::get('/', [Parametrizacion\EstadoCivilController::class,'index'])->name('estado_civil.index');
        Route::post('/', [Parametrizacion\EstadoCivilController::class,'store'])->name('estado_civil.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\EstadoCivilController::class,'show'])->name('estado_civil.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\EstadoCivilController::class,'update'])->name('estado_civil.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\EstadoCivilController::class,'destroy'])->name('estado_civil.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Grado Escolaridad
    Route::group(["prefix" => "grado-escolaridad"],function(){
        Route::get('/', [Parametrizacion\GradoEscolaridadController::class,'index'])->name('grado_escolaridad.index');
        Route::post('/', [Parametrizacion\GradoEscolaridadController::class,'store'])->name('grado_escolaridad.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\GradoEscolaridadController::class,'show'])->name('grado_escolaridad.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\GradoEscolaridadController::class,'update'])->name('grado_escolaridad.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\GradoEscolaridadController::class,'destroy'])->name('grado_escolaridad.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Ocupaciones
    Route::group(["prefix" => "ocupaciones"],function(){
        Route::get('/', [Parametrizacion\OcupacionController::class,'index'])->name('Ocupaciones.index');
        Route::post('/', [Parametrizacion\OcupacionController::class,'store'])->name('Ocupaciones.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\OcupacionController::class,'show'])->name('Ocupaciones.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\OcupacionController::class,'update'])->name('Ocupaciones.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\OcupacionController::class,'destroy'])->name('Ocupaciones.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Eps
    Route::group(["prefix" => "eps"],function(){
        Route::get('/', [Parametrizacion\EpsController::class,'index'])->name('eps.index');
        Route::post('/', [Parametrizacion\EpsController::class,'store'])->name('eps.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\EpsController::class,'show'])->name('eps.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\EpsController::class,'update'])->name('eps.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\EpsController::class,'destroy'])->name('eps.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Tipos Familia
    Route::group(["prefix" => "tipos-familia"],function(){
        Route::get('/', [Parametrizacion\TipoFamiliaController::class,'index'])->name('tiposFamilia.index');
        Route::post('/', [Parametrizacion\TipoFamiliaController::class,'store'])->name('tiposFamilia.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoFamiliaController::class,'show'])->name('tiposFamilia.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoFAmiliaController::class,'update'])->name('tiposFamilia.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoFAmiliaController::class,'destroy'])->name('tiposFamilia.delete');
    });

    // Banco
    Route::group(["prefix" => "bancos"],function(){
        Route::get('/', [Parametrizacion\BancoController::class,'index'])->name('bancos.index');
        Route::post('/', [Parametrizacion\BancoController::class,'store'])->name('bancos.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\BancoController::class,'show'])->name('bancos.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\BancoController::class,'update'])->name('bancos.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\BancoController::class,'destroy'])->name('bancos.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Formas de pago
    Route::group(["prefix" => "formas-pago"],function(){
        Route::get('/', [Parametrizacion\FormaPagoController::class,'index'])->name('formas_pago.index');
        Route::post('/', [Parametrizacion\TipoGastoController::class,'store'])->name('formas_pago.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\FormaPagoController::class,'show'])->name('formas_pago.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\FormaPagoController::class,'update'])->name('formas_pago.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\FormaPagoController::class,'destroy'])->name('formas_pago.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Tipos benefactor
    Route::group(["prefix" => "tipos-benefactor"],function(){
        Route::get('/', [Parametrizacion\TipoBenefactorController::class,'index'])->name('tipos_benefactor.index');
        Route::post('/', [Parametrizacion\TipoBenefactorController::class,'store'])->name('tipos_benefactor.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoBenefactorController::class,'show'])->name('tipos_benefactor.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoBenefactorController::class,'update'])->name('tipos_benefactor.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoBenefactorController::class,'destroy'])->name('tipos_benefactor.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Tipos de gasto
    Route::group(["prefix" => "tipos-gasto"],function(){
        Route::get('/', [Parametrizacion\TipoGastoController::class,'index'])->name('tipos_gasto.index');
        Route::post('/', [Parametrizacion\TipoGastoController::class,'store'])->name('tipos_gasto.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoGastoController::class,'show'])->name('tipos_gasto.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoGastoController::class,'update'])->name('tipos_gasto.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoGastoController::class,'destroy'])->name('tipos_gasto.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Tipos de donacion
    Route::group(["prefix" => "tipos-donacion"],function(){
        Route::get('/', [Parametrizacion\TipoDonacionController::class,'index'])->name('tipos_donacion.index');
        Route::post('/', [Parametrizacion\TipoDonacionController::class,'store'])->name('tipos_donacion.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoDonacionController::class,'show'])->name('tipos_donacion.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoDonacionController::class,'update'])->name('tipos_donacion.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoDonacionController::class,'destroy'])->name('tipos_donacion.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Tipos documentos proyectos
    Route::group(["prefix" => "tipo-documento-proyecto"],function(){
        Route::get('/', [Parametrizacion\TipoDocumentoProyectoController::class,'index'])->name('tipo_documento_proyecto.index');
        Route::post('/', [Parametrizacion\TipoDocumentoProyectoController::class,'store'])->name('tipo_documento_proyecto.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\TipoDocumentoProyectoController::class,'show'])->name('tipo_documento_proyecto.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\TipoDocumentoProyectoController::class,'update'])->name('tipo_documento_proyecto.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\TipoDocumentoProyectoController::class,'destroy'])->name('tipo_documento_proyecto.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // Parametros correos
    Route::group(["prefix" => "parametros-correos"],function(){
        Route::get('/', [Parametrizacion\ParametroCorreoController::class,'index'])->name('parametros_correos.index');
        Route::post('/', [Parametrizacion\ParametroCorreoController::class,'store'])->name('parametros_correos.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Parametrizacion\ParametroCorreoController::class,'show'])->name('parametros_correos.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Parametrizacion\ParametroCorreoController::class,'update'])->name('parametros_correos.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Parametrizacion\ParametroCorreoController::class,'destroy'])->name('parametros_correos.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });

    // ---------------------- Proyectos -------------------------- //
     // Parametros correos
     Route::group(["prefix" => "proyectos"],function(){
        Route::get('/', [Proyectos\ProyectoController::class,'index'])->name('proyectos.index');
        Route::post('/', [Proyectos\ProyectoController::class,'store'])->name('proyectos.store');
            // ->middleware(['permission:CrearAplicacion']);
        Route::get('/{id}', [Proyectos\ProyectoController::class,'show'])->name('proyectos.show');
            // ->middleware(['permission:ListarAplicacion']);
        Route::put('/{id}', [Proyectos\ProyectoController::class,'update'])->name('proyectos.update');
            // ->middleware(['permission:ModificarAplicacion']);
        Route::delete('/{id}', [Proyectos\ProyectoController::class,'destroy'])->name('proyectos.delete');
            // ->middleware(['permission:EliminarAplicacion']);
    });
});