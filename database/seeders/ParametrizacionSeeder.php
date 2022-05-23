<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ParametrizacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Auditoría Procesos',
            'modulo_id'=> 1,
            'posicion'=> 8,
            'icono_menu'=> 'task',
            'url'=> '/auditoria-procesos',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarAuditoriaProceso',
            'guard_name'=> 'api',
            'option_id'=> 8,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 27,
            'role_id'=> 1,
        ]);
        DB::table('modulos')->insert([
            'nombre'=> 'Parametrización',
            'aplicacion_id'=> 1,
            'estado'=> true,
            'icono_menu'=> 'settings',
            'posicion'=> 90,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Identificación',
            'modulo_id'=> 2,
            'posicion'=> 5,
            'icono_menu'=> 'featured_video',
            'url'=> '/tipos-identificacion',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Asesoría',
            'modulo_id'=> 2,
            'posicion'=> 10,
            'icono_menu'=> 'people',
            'url'=> '/tipos-orientacion',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Parentesco',
            'modulo_id'=> 2,
            'posicion'=> 15,
            'icono_menu'=> 'family_restroom',
            'url'=> '/tipos-parentesco',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Discapacidad',
            'modulo_id'=> 2,
            'posicion'=> 20,
            'icono_menu'=> 'accessible',
            'url'=> '/tipos-discapacidad',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Programa',
            'modulo_id'=> 2,
            'posicion'=> 25,
            'icono_menu'=> 'task',
            'url'=> '/tipos-programa',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Paises',
            'modulo_id'=> 2,
            'posicion'=> 30,
            'icono_menu'=> 'public',
            'url'=> '/paises',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Departamentos',
            'modulo_id'=> 2,
            'posicion'=> 35,
            'icono_menu'=> 'public',
            'url'=> '/departamentos',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Ciudades',
            'modulo_id'=> 2,
            'posicion'=> 40,
            'icono_menu'=> 'public',
            'url'=> '/ciudades',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Comunas',
            'modulo_id'=> 2,
            'posicion'=> 45,
            'icono_menu'=> 'public',
            'url'=> '/comunas',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Barrios',
            'modulo_id'=> 2,
            'posicion'=> 50,
            'icono_menu'=> 'public',
            'url'=> '/barrios',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'EPS',
            'modulo_id'=> 2,
            'posicion'=> 55,
            'icono_menu'=> 'local_hospital',
            'url'=> '/eps',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Estados Civiles',
            'modulo_id'=> 2,
            'posicion'=> 60,
            'icono_menu'=> 'assessment',
            'url'=> '/estados-civil',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Ocupaciones',
            'modulo_id'=> 2,
            'posicion'=> 65,
            'icono_menu'=> 'work',
            'url'=> '/ocupaciones',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Grados de Escolaridad',
            'modulo_id'=> 2,
            'posicion'=> 70,
            'icono_menu'=> 'school',
            'url'=> '/grado-escolaridad',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Familia',
            'modulo_id'=> 2,
            'posicion'=> 75,
            'icono_menu'=> 'family_restroom',
            'url'=> '/tipos-familia',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Condiciones de Familia',
            'modulo_id'=> 2,
            'posicion'=> 80,
            'icono_menu'=> 'people',
            'url'=> '/condiciones-familia',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Población',
            'modulo_id'=> 2,
            'posicion'=> 85,
            'icono_menu'=> 'people',
            'url'=> '/tipos-poblacion',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Vivienda',
            'modulo_id'=> 2,
            'posicion'=> 90,
            'icono_menu'=> 'home',
            'url'=> '/tipos-vivienda',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Techo',
            'modulo_id'=> 2,
            'posicion'=> 95,
            'icono_menu'=> 'home',
            'url'=> '/tipos-techo',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Piso',
            'modulo_id'=> 2,
            'posicion'=> 100,
            'icono_menu'=> 'home',
            'url'=> '/tipos-piso',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de División',
            'modulo_id'=> 2,
            'posicion'=> 105,
            'icono_menu'=> 'home',
            'url'=> '/tipos-division',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Bancos',
            'modulo_id'=> 2,
            'posicion'=> 110,
            'icono_menu'=> 'account_balance',
            'url'=>'/banco',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Formas de Pago',
            'modulo_id'=> 2,
            'posicion'=> 115,
            'icono_menu'=> 'attach_money',
            'url'=> '/forma-pago',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Benefactor',
            'modulo_id'=> 2,
            'posicion'=> 120,
            'icono_menu'=> 'attach_money',
            'url'=> '/tipo-benefactor',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Documentos de Proyecto',
            'modulo_id'=> 2,
            'posicion'=> 125,
            'icono_menu'=> 'article',
            'url'=> '/tipo-documento-proyecto',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Donación',
            'modulo_id'=> 2,
            'posicion'=> 130,
            'icono_menu'=> 'attach_money',
            'url'=> '/tipo-donacion',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Tipos de Gasto',
            'modulo_id'=> 2,
            'posicion'=> 135,
            'icono_menu'=> 'money_off',
            'url'=> '/tipo-gasto',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Parámetros Correos',
            'modulo_id'=> 2,
            'posicion'=> 140,
            'icono_menu'=> 'alternate_email',
            'url'=> '/parametro-correo',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('opciones_del_sistema')->insert([
            'nombre'=> 'Parámetros Constantes',
            'modulo_id'=> 2,
            'posicion'=> 145,
            'icono_menu'=> 'looks_5',
            'url'=> '/parametros-constantes',
            'estado'=> true,
            'usuario_creacion_id' =>1,
            'usuario_creacion_nombre'=>'SuperUser',
            'usuario_modificacion_id' =>1,
            'usuario_modificacion_nombre' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoIdentificacion',
            'guard_name'=> 'api',
            'option_id'=> 9,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoIdentificacion',
            'guard_name'=> 'api',
            'option_id'=> 9,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoIdentificacion',
            'guard_name'=> 'api',
            'option_id'=> 9,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoIdentificacion',
            'guard_name'=> 'api',
            'option_id'=> 9,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoAsesoria',
            'guard_name'=> 'api',
            'option_id'=> 10,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoAsesoria',
            'guard_name'=> 'api',
            'option_id'=> 10,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoAsesoria',
            'guard_name'=> 'api',
            'option_id'=> 10,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoAsesoria',
            'guard_name'=> 'api',
            'option_id'=> 10,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoParentesco',
            'guard_name'=> 'api',
            'option_id'=> 11,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoParentesco',
            'guard_name'=> 'api',
            'option_id'=> 11,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoParentesco',
            'guard_name'=> 'api',
            'option_id'=> 11,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoParentesco',
            'guard_name'=> 'api',
            'option_id'=> 11,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoDiscapacidad',
            'guard_name'=> 'api',
            'option_id'=> 12,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoDiscapacidad',
            'guard_name'=> 'api',
            'option_id'=> 12,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoDiscapacidad',
            'guard_name'=> 'api',
            'option_id'=> 12,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoDiscapacidad',
            'guard_name'=> 'api',
            'option_id'=> 12,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoPrograma',
            'guard_name'=> 'api',
            'option_id'=> 13,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoPrograma',
            'guard_name'=> 'api',
            'option_id'=> 13,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoPrograma',
            'guard_name'=> 'api',
            'option_id'=> 13,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoPrograma',
            'guard_name'=> 'api',
            'option_id'=> 13,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearPais',
            'guard_name'=> 'api',
            'option_id'=> 14,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarPais',
            'guard_name'=> 'api',
            'option_id'=> 14,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarPais',
            'guard_name'=> 'api',
            'option_id'=> 14,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarPais',
            'guard_name'=> 'api',
            'option_id'=> 14,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearDepartamento',
            'guard_name'=> 'api',
            'option_id'=> 15,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarDepartamento',
            'guard_name'=> 'api',
            'option_id'=> 15,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarDepartamento',
            'guard_name'=> 'api',
            'option_id'=> 15,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarDepartamento',
            'guard_name'=> 'api',
            'option_id'=> 15,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearCiudad',
            'guard_name'=> 'api',
            'option_id'=> 16,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarCiudad',
            'guard_name'=> 'api',
            'option_id'=> 16,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarCiudad',
            'guard_name'=> 'api',
            'option_id'=> 16,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarCiudad',
            'guard_name'=> 'api',
            'option_id'=> 16,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearComuna',
            'guard_name'=> 'api',
            'option_id'=> 17,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarComuna',
            'guard_name'=> 'api',
            'option_id'=> 17,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarComuna',
            'guard_name'=> 'api',
            'option_id'=> 17,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarComuna',
            'guard_name'=> 'api',
            'option_id'=> 17,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearBarrio',
            'guard_name'=> 'api',
            'option_id'=> 18,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarBarrio',
            'guard_name'=> 'api',
            'option_id'=> 18,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarBarrio',
            'guard_name'=> 'api',
            'option_id'=> 18,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarBarrio',
            'guard_name'=> 'api',
            'option_id'=> 18,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearEPS',
            'guard_name'=> 'api',
            'option_id'=> 19,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarEPS',
            'guard_name'=> 'api',
            'option_id'=> 19,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarEPS',
            'guard_name'=> 'api',
            'option_id'=> 19,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarEPS',
            'guard_name'=> 'api',
            'option_id'=> 19,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearEstadoCivil',
            'guard_name'=> 'api',
            'option_id'=> 20,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarEstadoCivil',
            'guard_name'=> 'api',
            'option_id'=> 20,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarEstadoCivil',
            'guard_name'=> 'api',
            'option_id'=> 20,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarEstadoCivil',
            'guard_name'=> 'api',
            'option_id'=> 20,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearOcupacion',
            'guard_name'=> 'api',
            'option_id'=> 21,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarOcupacion',
            'guard_name'=> 'api',
            'option_id'=> 21,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarOcupacion',
            'guard_name'=> 'api',
            'option_id'=> 21,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarOcupacion',
            'guard_name'=> 'api',
            'option_id'=> 21,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearGradoEscolaridad',
            'guard_name'=> 'api',
            'option_id'=> 22,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarGradoEscolaridad',
            'guard_name'=> 'api',
            'option_id'=> 22,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarGradoEscolaridad',
            'guard_name'=> 'api',
            'option_id'=> 22,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarGradoEscolaridad',
            'guard_name'=> 'api',
            'option_id'=> 22,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoFamilia',
            'guard_name'=> 'api',
            'option_id'=> 23,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoFamilia',
            'guard_name'=> 'api',
            'option_id'=> 23,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoFamilia',
            'guard_name'=> 'api',
            'option_id'=> 23,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoFamilia',
            'guard_name'=> 'api',
            'option_id'=> 23,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearCondicionFamilia',
            'guard_name'=> 'api',
            'option_id'=> 24,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarCondicionFamilia',
            'guard_name'=> 'api',
            'option_id'=> 24,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarCondicionFamilia',
            'guard_name'=> 'api',
            'option_id'=> 24,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarCondicionFamilia',
            'guard_name'=> 'api',
            'option_id'=> 24,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoPoblacion',
            'guard_name'=> 'api',
            'option_id'=> 25,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoPoblacion',
            'guard_name'=> 'api',
            'option_id'=> 25,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoPoblacion',
            'guard_name'=> 'api',
            'option_id'=> 25,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoPoblacion',
            'guard_name'=> 'api',
            'option_id'=> 25,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoVivienda',
            'guard_name'=> 'api',
            'option_id'=> 26,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoVivienda',
            'guard_name'=> 'api',
            'option_id'=> 26,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoVivienda',
            'guard_name'=> 'api',
            'option_id'=> 26,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoVivienda',
            'guard_name'=> 'api',
            'option_id'=> 26,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoTecho',
            'guard_name'=> 'api',
            'option_id'=> 27,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoTecho',
            'guard_name'=> 'api',
            'option_id'=> 27,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoTecho',
            'guard_name'=> 'api',
            'option_id'=> 27,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoTecho',
            'guard_name'=> 'api',
            'option_id'=> 27,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoPiso',
            'guard_name'=> 'api',
            'option_id'=> 28,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoPiso',
            'guard_name'=> 'api',
            'option_id'=> 28,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoPiso',
            'guard_name'=> 'api',
            'option_id'=> 28,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoPiso',
            'guard_name'=> 'api',
            'option_id'=> 28,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoDivision',
            'guard_name'=> 'api',
            'option_id'=> 29,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoDivision',
            'guard_name'=> 'api',
            'option_id'=> 29,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoDivision',
            'guard_name'=> 'api',
            'option_id'=> 29,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoDivision',
            'guard_name'=> 'api',
            'option_id'=> 29,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearBanco',
            'guard_name'=> 'api',
            'option_id'=> 30,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarBanco',
            'guard_name'=> 'api',
            'option_id'=> 30,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarBanco',
            'guard_name'=> 'api',
            'option_id'=> 30,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarBancco',
            'guard_name'=> 'api',
            'option_id'=> 30,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearFormaPago',
            'guard_name'=> 'api',
            'option_id'=> 31,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarFormaPago',
            'guard_name'=> 'api',
            'option_id'=> 31,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarFormaPago',
            'guard_name'=> 'api',
            'option_id'=> 31,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarFormaPago',
            'guard_name'=> 'api',
            'option_id'=> 31,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoBenefactor',
            'guard_name'=> 'api',
            'option_id'=> 32,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoBenefactor',
            'guard_name'=> 'api',
            'option_id'=> 32,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoBenefactor',
            'guard_name'=> 'api',
            'option_id'=> 32,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoBenefactor',
            'guard_name'=> 'api',
            'option_id'=> 32,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoDocumentoProyecto',
            'guard_name'=> 'api',
            'option_id'=> 33,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoDocumentoProyecto',
            'guard_name'=> 'api',
            'option_id'=> 33,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoDocumentoProyecto',
            'guard_name'=> 'api',
            'option_id'=> 33,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoDocumentoProyecto',
            'guard_name'=> 'api',
            'option_id'=> 33,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoDonacion',
            'guard_name'=> 'api',
            'option_id'=> 34,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoDonacion',
            'guard_name'=> 'api',
            'option_id'=> 34,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoDonacion',
            'guard_name'=> 'api',
            'option_id'=> 34,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoDonacion',
            'guard_name'=> 'api',
            'option_id'=> 34,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearTipoGasto',
            'guard_name'=> 'api',
            'option_id'=> 35,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarTipoGasto',
            'guard_name'=> 'api',
            'option_id'=> 35,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarTipoGasto',
            'guard_name'=> 'api',
            'option_id'=> 35,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarTipoGasto',
            'guard_name'=> 'api',
            'option_id'=> 35,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearParametroCorreo',
            'guard_name'=> 'api',
            'option_id'=> 36,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarParametroCorreo',
            'guard_name'=> 'api',
            'option_id'=> 36,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarParametroCorreo',
            'guard_name'=> 'api',
            'option_id'=> 36,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarParametroCorreo',
            'guard_name'=> 'api',
            'option_id'=> 36,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'CrearParametroConstante',
            'guard_name'=> 'api',
            'option_id'=> 37,
            'title'=> 'Crear',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ModificarParametroConstante',
            'guard_name'=> 'api',
            'option_id'=> 37,
            'title'=> 'Modificar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'ListarParametroConstante',
            'guard_name'=> 'api',
            'option_id'=> 37,
            'title'=> 'Listar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('permissions')->insert([
            'name'=> 'EliminarParametroConstante',
            'guard_name'=> 'api',
            'option_id'=> 37,
            'title'=> 'Eliminar',
            'user_creation_id' =>1,
            'user_creation_name'=>'SuperUser',
            'user_modification_id' =>1,
            'user_modification_name' =>'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 28,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 29,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 30,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 31,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 32,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 33,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 34,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 35,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 36,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 37,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 38,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 39,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 40,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 41,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 42,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 43,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 44,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 45,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 46,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 47,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 48,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 49,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 50,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 51,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 52,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 53,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 54,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 55,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 56,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 57,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 58,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 59,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 60,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 61,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 62,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 63,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 64,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 65,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 66,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 67,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 68,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 69,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 70,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 71,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 72,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 73,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 74,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 75,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 76,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 77,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 78,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 79,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 80,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 81,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 82,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 83,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 84,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 85,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 86,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 87,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 88,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 89,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 90,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 91,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 92,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 93,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 94,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 95,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 96,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 97,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 98,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 99,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 100,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 101,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 102,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 103,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 104,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 105,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 106,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 107,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 108,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 109,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 110,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 111,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 112,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 113,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 114,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 115,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 116,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 117,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 118,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 119,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 120,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 121,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 122,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 123,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 124,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 125,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 126,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 127,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 128,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 129,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 130,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 131,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 132,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 133,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 134,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 135,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 136,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 137,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 138,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 139,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 140,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 141,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 142,
            'role_id'=> 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id'=> 143,
            'role_id'=> 1,
        ]);
    }
}
