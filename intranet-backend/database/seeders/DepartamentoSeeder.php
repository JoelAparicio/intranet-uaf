<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departamentos')->insert([
            ['nombre' => 'Despacho superior', 'descripcion' => 'Directora y subdirección encargados'],
            ['nombre' => 'Secretaría despacho superior', 'descripcion' => 'Gestión de documentos y asistencia administrativa'],
            ['nombre' => 'Relaciones públicas', 'descripcion' => 'Manejo de la comunicación externa y la imagen pública'],
            ['nombre' => 'Administración', 'descripcion' => 'Gestión de recursos y procesos administrativos'],
            ['nombre' => 'Recursos humanos', 'descripcion' => 'Administración del personal y bienestar de los empleados'],
            ['nombre' => 'Análisis operativo', 'descripcion' => 'Evaluación y seguimiento de operaciones financieras del pais'],
            ['nombre' => 'Análisis estratégico', 'descripcion' => 'Desarrollo de estrategias y planificación sobre operaciones financieras del pais'],
            ['nombre' => 'Asesoría legal', 'descripcion' => 'Consultoría legal y gestión de asuntos jurídicos'],
            ['nombre' => 'Contact Center', 'descripcion' => 'Atención y soporte a los sujetos obligados'],
            ['nombre' => 'Cooperación nacional e internacional', 'descripcion' => 'Colaboración y coordinación con entidades externas'],
            ['nombre' => 'Tecnología', 'descripcion' => 'Soporte y desarrollo de infraestructura tecnológica'],
            ['nombre' => 'Escoltas despacho superior', 'descripcion' => 'Desarrollo de proyectos y soluciones innovadoras'],
        ]);
    }
}
