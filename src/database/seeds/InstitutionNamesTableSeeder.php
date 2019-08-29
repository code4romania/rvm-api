<?php

use Illuminate\Database\Seeder;
use App\InstitutionName;

class InstitutionNamesTableSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    InstitutionName::insert([
      ['slug' => 'Departamentul pentru Situatii de Urgenta',         'name' => 'Departamentul pentru Situații de Urgență'],
      ['slug' => 'Inspectoratul General pentru Situatii de Urgenta', 'name' => 'Inspectoratul General pentru Situații de Urgență'],
      ['slug' => 'Inspectoratul pentru Situatii de Urgenta',         'name' => 'Inspectoratul pentru Situații de Urgență'],
      ['slug' => 'Inspectoratul General al Jandarmeriei Romane',     'name' => 'Inspectoratul General al Jandarmeriei Române'],
      ['slug' => 'Inspectoratul General al Politiei Romane',         'name' => 'Inspectoratul General al Poliției Române']
     ]);
  }
}
