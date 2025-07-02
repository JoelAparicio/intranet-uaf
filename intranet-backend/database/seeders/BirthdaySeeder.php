<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BirthdaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $birthdayData = [
            [
                'usuario' => 'Vanessa Rodríguez',
                'id_departamento' => 8,
                'fecha_birthday' => '01-02',
            ],
            [
                'usuario' => 'Luis Torres',
                'id_departamento' => 4,
                'fecha_birthday' => '01-27',
            ],
            [
                'usuario' => 'Vanessa Tuñón',
                'id_departamento' => 6,
                'fecha_birthday' => '01-29',
            ],
            [
                'usuario' => 'Ana Sofía García',
                'id_departamento' => 8,
                'fecha_birthday' => '02-06',
            ],
            [
                'usuario' => 'Gabriela Itzel  Rengifo',
                'id_departamento' => 6,
                'fecha_birthday' => '02-08',
            ],
            [
                'usuario' => 'Olivia Pineda',
                'id_departamento' => 3,
                'fecha_birthday' => '02-08',
            ],
            [
                'usuario' => 'Carlos Pelletier',
                'id_departamento' => 6,
                'fecha_birthday' => '02-17',
            ],
            [
                'usuario' => 'Aracely Lic',
                'id_departamento' => 6,
                'fecha_birthday' => '02-18',
            ],
            [
                'usuario' => 'Zuhelem Zárate',
                'id_departamento' => 10,
                'fecha_birthday' => '02-19',
            ],
            [
                'usuario' => 'Armando Vásquez',
                'id_departamento' => 6,
                'fecha_birthday' => '03-02',
            ],
            [
                'usuario' => 'Angela Guerrero',
                'id_departamento' => 5,
                'fecha_birthday' => '03-11',
            ],
            [
                'usuario' => 'Ricardo José Moreno Sáenz',
                'id_departamento' => 8,
                'fecha_birthday' => '03-13',
            ],
            [
                'usuario' => 'Luis Alberto Bethancourt',
                'id_departamento' => 11,
                'fecha_birthday' => '03-25',
            ],
            [
                'usuario' => 'Alisson Echevers',
                'id_departamento' => 10,
                'fecha_birthday' => '04-21',
            ],
            [
                'usuario' => 'Gustavo De Gracia',
                'id_departamento' => 8,
                'fecha_birthday' => '05-06',
            ],
            [
                'usuario' => 'José Batista',
                'id_departamento' => 6,
                'fecha_birthday' => '05-04',
            ],
            [
                'usuario' => 'Yaneth del Carmen Jaramillo',
                'id_departamento' => 1,
                'fecha_birthday' => '05-15',
            ],
            [
                'usuario' => 'Ana Adjani Ayala',
                'id_departamento' => 4,
                'fecha_birthday' => '05-17',
            ],
            [
                'usuario' => 'Marcos Alberto Vargas Cerrud',
                'id_departamento' => 4,
                'fecha_birthday' => '05-25',
            ],
            [
                'usuario' => 'Isabel Jiménez Ábrego',
                'id_departamento' => 4,
                'fecha_birthday' => '05-26',
            ],
            [
                'usuario' => 'Juan José Castillo Martínez',
                'id_departamento' => 11,
                'fecha_birthday' => '06-09',
            ],
            [
                'usuario' => 'Victoria Amelia Cruz Martínez',
                'id_departamento' => 9,
                'fecha_birthday' => '06-14',
            ],
            [
                'usuario' => 'Ricardo Lizondro',
                'id_departamento' => 4,
                'fecha_birthday' => '06-16',
            ],
            [
                'usuario' => 'Víctor Daniel Castillo',
                'id_departamento' => 6,
                'fecha_birthday' => '06-22',
            ],
            [
                'usuario' => 'Janell Giselle Vásquez Lañas',
                'id_departamento' => 7,
                'fecha_birthday' => '06-24',
            ],
            [
                'usuario' => 'Ángel Ovidio González Montilla',
                'id_departamento' => 7,
                'fecha_birthday' => '06-30',
            ],
            [
                'usuario' => 'Carolin Giselle Rivera Navarrete',
                'id_departamento' => 7,
                'fecha_birthday' => '07-15',
            ],
            [
                'usuario' => 'Abbiel Mojica',
                'id_departamento' => 4,
                'fecha_birthday' => '07-15',
            ],
            [
                'usuario' => 'Karol González Quintero',
                'id_departamento' => 6,
                'fecha_birthday' => '07-25',
            ],
            [
                'usuario' => 'Rita Espinosa',
                'id_departamento' => 8,
                'fecha_birthday' => '08-07',
            ],
            [
                'usuario' => 'Kinpyler Harris',
                'id_departamento' => 11,
                'fecha_birthday' => '08-11',
            ],
            [
                'usuario' => 'Javier Jaén',
                'id_departamento' => 11,
                'fecha_birthday' => '08-15',
            ],
            [
                'usuario' => 'Dalis Alicia Serrano',
                'id_departamento' => 1,
                'fecha_birthday' => '08-16',
            ],
            [
                'usuario' => 'Dania Yisel Córdova Villarreal',
                'id_departamento' => 4,
                'fecha_birthday' => '08-16',
            ],
            [
                'usuario' => 'Jorlenny Prado V.',
                'id_departamento' => 6,
                'fecha_birthday' => '08-20',
            ],
            [
                'usuario' => 'Lorena Martínez González',
                'id_departamento' => 6,
                'fecha_birthday' => '08-22',
            ],
            [
                'usuario' => 'Maritza Herrera',
                'id_departamento' => 6,
                'fecha_birthday' => '08-26',
            ],
            [
                'usuario' => 'Marianela Hernández',
                'id_departamento' => 5,
                'fecha_birthday' => '09-19',
            ],
            [
                'usuario' => 'Fernando Antonio Velásquez',
                'id_departamento' => 6,
                'fecha_birthday' => '09-25',
            ],
            [
                'usuario' => 'Galileo Obando',
                'id_departamento' => 10,
                'fecha_birthday' => '10-08',
            ],
            [
                'usuario' => 'Lissette Ginella López Berrio',
                'id_departamento' => 6,
                'fecha_birthday' => '10-10',
            ],
            [
                'usuario' => 'Oldemar Guerra',
                'id_departamento' => 1,
                'fecha_birthday' => '10-12',
            ],
            [
                'usuario' => 'Gabriel Santiago',
                'id_departamento' => 3,
                'fecha_birthday' => '10-14',
            ],
            [
                'usuario' => 'Rolando Carrera',
                'id_departamento' => 4,
                'fecha_birthday' => '10-27',
            ],
            [
                'usuario' => 'Óscar Alberto Navarro González',
                'id_departamento' => 4,
                'fecha_birthday' => '10-28',
            ],
            [
                'usuario' => 'Katerine Flores',
                'id_departamento' => 6,
                'fecha_birthday' => '10-29',
            ],
            [
                'usuario' => 'Nahyr Segundo Alonso',
                'id_departamento' => 7,
                'fecha_birthday' => '10-29',
            ],
            [
                'usuario' => 'Yestefani Senid Sánchez',
                'id_departamento' => 9,
                'fecha_birthday' => '11-04',
            ],
            [
                'usuario' => 'Isabel Pérez  Henríquez',
                'id_departamento' => 1,
                'fecha_birthday' => '11-07',
            ],
            [
                'usuario' => 'Ashley Espitia',
                'id_departamento' => 9,
                'fecha_birthday' => '11-13',
            ],
            [
                'usuario' => 'Linett Añino Flores',
                'id_departamento' => 6,
                'fecha_birthday' => '11-15',
            ],
            [
                'usuario' => 'Carmen Hernández',
                'id_departamento' => 6,
                'fecha_birthday' => '11-20',
            ],
            [
                'usuario' => 'Julissa Thompson Daquin',
                'id_departamento' => 8,
                'fecha_birthday' => '11-21',
            ],
            [
                'usuario' => 'Zulay Rodríguez',
                'id_departamento' => 6,
                'fecha_birthday' => '11-21',
            ],
            [
                'usuario' => 'Liseth Mémbora',
                'id_departamento' => 10,
                'fecha_birthday' => '11-29',
            ],
            [
                'usuario' => 'Melitza Mariela De León Ríos',
                'id_departamento' => 4,
                'fecha_birthday' => '12-08',
            ],
            [
                'usuario' => 'Sandra Soribel Barrios de Méndez',
                'id_departamento' => 9,
                'fecha_birthday' => '12-11',
            ],
            [
                'usuario' => 'Luis Crison',
                'id_departamento' => 9,
                'fecha_birthday' => '12-13',
            ],
        ];

        $currentYear = Carbon::now()->year;

        foreach ($birthdayData as $data) {
            DB::table('birthdays')->insert([
                'usuario' => $data['usuario'],
                'id_departamento' => $data['id_departamento'],
                'fecha_birthday' => Carbon::createFromFormat('m-d', $data['fecha_birthday'])->year($currentYear)->format('Y-m-d'),
                'deleted' => 0,
                'deleted_at' => null,
            ]);
        }
    }
}
