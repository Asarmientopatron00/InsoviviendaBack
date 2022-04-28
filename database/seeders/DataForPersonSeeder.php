<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DataForPersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tipos_identificacion')->insert([
            'tipIdeDescripcion'=> 'Cedula',
            'tipIdeEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('paises')->insert([
            'paisesDescripcion'=> 'Colombia',
            'paisesEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('departamentos')->insert([
            'departamentosDescripcion'=> 'Antioquia',
            'pais_id'=> 1,
            'departamentosEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('ciudades')->insert([
            'ciudadesDescripcion'=> 'Medellin',
            'departamento_id'=> 1,
            'ciudadesEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('comunas')->insert([
            'comunasDescripcion'=> 'Comuna X',
            'ciudad_id'=> 1,
            'comunasEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('barrios')->insert([
            'barriosDescripcion'=> 'Barrio X',
            'comuna_id'=> 1,
            'barriosEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('estados_civil')->insert([
            'estCivDescripcion'=> 'Soltero(a)',
            'estCivEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('tipos_poblacion')->insert([
            'tipPobDescripcion'=> 'No Aplica',
            'tipPobEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('tipos_discapacidad')->insert([
            'tipDisDescripcion'=> 'Ninguna',
            'tipDisEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('eps')->insert([
            'epsDescripcion'=> 'Sura',
            'epsEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('grados_escolaridad')->insert([
            'graEscDescripcion'=> 'Profesional',
            'graEscEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('tipos_vivienda')->insert([
            'tipVivDescripcion'=> 'Propia',
            'tipVivEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('tipos_techo')->insert([
            'tipTecDescripcion'=> 'Ethernit',
            'tipTecEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('tipos_piso')->insert([
            'tipPisDescripcion'=> 'Baldosa',
            'tipPisEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('tipos_division')->insert([
            'tipDivDescripcion'=> 'Pared',
            'tipDivEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
        DB::table('ocupaciones')->insert([
            'ocupacionesDescripcion'=> 'Empleado',
            'ocupacionesEstado'=> 1,
            'usuario_creacion_id'=> 1,
            'usuario_creacion_nombre'=> 'SuperUser',
            'usuario_modificacion_id'=>1,
            'usuario_modificacion_nombre'=> 'SuperUser',
            'created_at' =>Carbon::now(),
            'updated_at' =>Carbon::now(),
        ]);
    }
}
