<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposolicitudSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipos_de_solicitud')->insert([
            ['tipo_solicitud' => 'Formulario de permiso', 'descripcion' => 'Solicitud de permisos descontables o no descontables'],
            ['tipo_solicitud' => 'Formulario de reincorporación', 'descripcion' => 'Notificación de reincorporación a la institución por uso de ausencia justificada '],
            ['tipo_solicitud' => 'Formulario de tiempo compensatorio', 'descripcion' => 'Solicitud de uso de horas/dias del tiempo compensatorio acumulado'],
            ['tipo_solicitud' => 'Formulario de vacaciones', 'descripcion' => 'Solicitud para uso de vacaciones'],
            ['tipo_solicitud' => 'Formulario de horas extraordinarias', 'descripcion' => 'Solicitud para acumular horas extras de trabajo'],
            // Agrega otros roles según sea necesario
        ]);
    }
}
