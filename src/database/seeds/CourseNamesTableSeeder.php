<?php

use Illuminate\Database\Seeder;
use App\CourseName;

class CourseNamesTableSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $id = str_random(16);
    CourseName::insert([
      ['slug' => 'Prim-ajutor calificat',                                             'name' => 'Prim-ajutor calificat',        'static_accreditor'=>(object) ["_id"=>$id, "name"=>"IGSU-Pompieri"]],
      ['slug' => 'Prim-ajutor',                                                       'name' => 'Prim-ajutor'],
      ['slug' => 'Medicina primara (asistente/medici rezidenti/etc)',                 'name' => 'Medicină primară (asistente/medici rezidenți/etc)'],
      ['slug' => 'Asistenta sociala',                                                 'name' => 'Asistență socială'],
      ['slug' => 'Personal medical auxiliar (brancardieri/mediatori sanitari/etc)',   'name' => 'Personal medical auxiliar (brancardieri/mediatori sanitari/etc)'],
      ['slug' => 'Constructii',                                                       'name' => 'Construcții'],
      ['slug' => 'Instalatii de apa/gaz/electrice',                                   'name' => 'Instalații de apă/gaz/electrice'],
      ['slug' => 'Sofer profesionist (permis pentru ambulanta sau alte masini mari)', 'name' => 'Șofer profesionist (permis pentru ambulanță sau alte mașini mari)'],
      ['slug' => 'Organizare comunitara',                                             'name' => 'Organizare comunitară'],
      ['slug' => 'Asistenta tehnica',                                                 'name' => 'Asistență tehnică'],
      ['slug' => 'Logistica',                                                         'name' => 'Logistică'],
      ['slug' => 'Altele',                                                            'name' => 'Altele'],
    ]);
  }
}
