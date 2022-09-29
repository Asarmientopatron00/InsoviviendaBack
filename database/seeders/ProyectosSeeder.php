<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProyectosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('modulos')->insert([
            'nombre'=> 'Proyectos',
            'aplicacion_id'=> 1,
            'estado'=> true,
            'icono_menu'=> 'account_tree',
            'posicion'=> 10,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Proyectos',
            'modulo_id'=> 4,
            'posicion'=> 5,
            'icono_menu'=> 'account_tree',
            'url'=> '/proyectos',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Asesorías',
            'modulo_id'=> 4,
            'posicion'=> 10,
            'icono_menu'=> 'cast_for_education',
            'url'=> '/orientaciones',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Donaciones',
            'modulo_id'=> 4,
            'posicion'=> 15,
            'icono_menu'=> 'money',
            'url'=> '/donaciones',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Desembolsos',
            'modulo_id'=> 4,
            'posicion'=> 20,
            'icono_menu'=> 'money',
            'url'=> '/desembolsos',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Pagos',
            'modulo_id'=> 4,
            'posicion'=> 25,
            'icono_menu'=> 'money',
            'url'=> '/pagos',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Pagos Especiales',
            'modulo_id'=> 4,
            'posicion'=> 30,
            'icono_menu'=> 'money',
            'url'=> '/pagos-especiales',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Abonos Extras Capital',
            'modulo_id'=> 4,
            'posicion'=> 35,
            'icono_menu'=> 'money',
            'url'=> '/pagos-abonar-extra',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Informe Gestión Cartera',
            'modulo_id'=> 4,
            'posicion'=> 40,
            'icono_menu'=> 'format_list_bulleted',
            'url'=> '/informe-gestion-cartera',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Ajuste Fecha Normalizacion',
            'modulo_id'=> 4,
            'posicion'=> 45,
            'icono_menu'=> 'timer',
            'url'=> '/ajustar-fecha-pago',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ExportarProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'Exportar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearBitacoraProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'CrearBitPro',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarBitacoraProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'EliminarBitPro',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarBitacoraProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'ListarBitPro',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarBitacoraProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'ModificarBitPro',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarPlanAmortizacion',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'ListarPlAmPro',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ExportarPlanAmortizacion',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'ExportarPlAmPro',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ExportarPlanAmortizacionDefinitivo',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'ExportarPlAmDePro',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarDocumentosProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'ListarDocPro',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarDocumentosProyecto',
            'guard_name'=> 'api',
            'option_id'=> 43,
            'title'=> 'ModificarDocPro',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearOrientacion',
            'guard_name'=> 'api',
            'option_id'=> 44,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarOrientacion',
            'guard_name'=> 'api',
            'option_id'=> 44,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarOrientacion',
            'guard_name'=> 'api',
            'option_id'=> 44,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarOrientacion',
            'guard_name'=> 'api',
            'option_id'=> 44,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ExportarOrientacion',
            'guard_name'=> 'api',
            'option_id'=> 44,
            'title'=> 'Exportar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearDonacion',
            'guard_name'=> 'api',
            'option_id'=> 45,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarDonacion',
            'guard_name'=> 'api',
            'option_id'=> 45,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarDonacion',
            'guard_name'=> 'api',
            'option_id'=> 45,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarDonacion',
            'guard_name'=> 'api',
            'option_id'=> 45,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearDesembolso',
            'guard_name'=> 'api',
            'option_id'=> 46,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarDesembolso',
            'guard_name'=> 'api',
            'option_id'=> 46,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarDesembolso',
            'guard_name'=> 'api',
            'option_id'=> 46,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarDesembolso',
            'guard_name'=> 'api',
            'option_id'=> 46,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ExportarDesembolso',
            'guard_name'=> 'api',
            'option_id'=> 46,
            'title'=> 'Exportar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearPago',
            'guard_name'=> 'api',
            'option_id'=> 47,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarPago',
            'guard_name'=> 'api',
            'option_id'=> 47,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarPago',
            'guard_name'=> 'api',
            'option_id'=> 47,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarPago',
            'guard_name'=> 'api',
            'option_id'=> 47,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ExportarPago',
            'guard_name'=> 'api',
            'option_id'=> 47,
            'title'=> 'Exportar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ReversarPago',
            'guard_name'=> 'api',
            'option_id'=> 47,
            'title'=> 'Reversar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearPagoEspecial',
            'guard_name'=> 'api',
            'option_id'=> 48,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearAbonoExtra',
            'guard_name'=> 'api',
            'option_id'=> 49,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarAbonoExtra',
            'guard_name'=> 'api',
            'option_id'=> 49,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarAbonoExtra',
            'guard_name'=> 'api',
            'option_id'=> 49,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarAbonoExtra',
            'guard_name'=> 'api',
            'option_id'=> 49,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ExportarAbonoExtra',
            'guard_name'=> 'api',
            'option_id'=> 49,
            'title'=> 'Exportar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ExportarInformeGestionCartera',
            'guard_name'=> 'api',
            'option_id'=> 50,
            'title'=> 'Exportar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'AjustarFechaNormalizacion',
            'guard_name'=> 'api',
            'option_id'=> 51,
            'title'=> 'Ajustar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 166,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 167,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 168,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 169,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 170,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 171,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 172,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 173,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 174,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 175,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 176,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 177,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 178,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 179,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 180,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 181,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 182,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 183,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 184,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 185,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 186,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 187,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 188,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 189,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 190,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 191,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 192,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 193,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 194,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 195,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 196,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 197,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 198,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 199,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 200,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 201,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 202,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 203,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 204,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 205,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 206,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 207,
            'role_id'=> 1,
        ]);
    }
}
