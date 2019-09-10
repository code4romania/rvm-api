<?php

use Illuminate\Database\Seeder;
use App\ResourceCategory;

class ResourceCategoriesTableSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  
  public function run(){
    $parent = ResourceCategory::create(['parent_id' => '0',  'slug' => 'Adapostire', 'name' => 'Adăpostire']);
    ResourceCategory::create(['parent_id' => strval($parent->id),  'slug' => 'Corturi'                                                    , 'name' => 'Corturi']);
    ResourceCategory::create(['parent_id' => strval($parent->id),  'slug' => 'Textile (paturi, asternuturi, etc)'                         , 'name' => 'Textile (pături, așternuturi, etc)']);
    ResourceCategory::create(['parent_id' => strval($parent->id),  'slug' => 'Mobilier (paturi de campanie, patut de copil, saltele, etc)', 'name' => 'Mobilier (paturi de campanie, pătuț de copil, saltele, etc)']);
    ResourceCategory::create(['parent_id' => strval($parent->id),  'slug' => 'Spatii de cazare'                                           , 'name' => 'Spații de cazare']);

    $parent = ResourceCategory::create(['parent_id' => '0',  'slug' => 'Echipamente', 'name' => 'Echipamente']);
    ResourceCategory::create(['parent_id' => strval($parent->id),  'slug' => 'Echipamente electrice (motopompe, generatoare curent, pompe apa, drone, etc)'                                                  , 'name' => 'Echipamente electrice (motopompe, generatoare curent, pompe apă, drone, etc)']);
    ResourceCategory::create(['parent_id' => strval($parent->id),  'slug' => 'Echipamente utilitare (rucsace de supravietuire, lanterne, lampi, instalatii de iluminat, bucatarii de campanie, resouri, etc)', 'name' => 'Echipamente utilitare (rucsace de supraviețuire, lanterne, lămpi, instalații de iluminat, bucătării de campanie, reșouri, etc)']);

    ResourceCategory::create(['parent_id' => '0',  'slug' => 'Caini utilitari', 'name' => 'Câini utilitari']);

    $parent = ResourceCategory::create(['parent_id' => '0',  'slug' => 'Transport',                                                              'name' => 'Transport']);
    ResourceCategory::create(['parent_id' => strval($parent->id), 'slug' => 'Masini (dube 7 locuri, microbuze, masini 4x4, masini mici, etc)',    'name' => 'Mașini (dube 7 locuri, microbuze, mașini 4x4, mașini mici, etc)']);
    ResourceCategory::create(['parent_id' => strval($parent->id), 'slug' => 'Motociclete (motociclete, scutere, alte vehicule motorizate, etc)', 'name' => 'Motociclete (motociclete, scutere, alte vehicule motorizate, etc)']);
    ResourceCategory::create(['parent_id' => strval($parent->id), 'slug' => 'Rulote',                                                            'name' => 'Rulote']);
    ResourceCategory::create(['parent_id' => strval($parent->id), 'slug' => 'Masini specializate (ambulante, etc)',                              'name' => 'Mașini specializate (ambulanțe, etc)']);

    ResourceCategory::create(['parent_id' => '0',  'slug' => 'Altele', 'name' => 'Altele']);
  }
}
