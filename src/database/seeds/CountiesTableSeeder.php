<?php

use Illuminate\Database\Seeder;
use App\County;
use App\Country;

class CountiesTableSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $country =  Country::where('country_id', '=', '1')->get(['_id'])->first();

    if($country->id){
      $id = strval($country->id);

      County::insert([
        ['county_id' => '1',  'country_id' => $id, 'slug'  => 'Dolj',            'name' => 'Dolj'],
        ['county_id' => '2',  'country_id' => $id, 'slug'  => 'Bacau',           'name' => 'Bacău'],
        ['county_id' => '3',  'country_id' => $id, 'slug'  => 'Harghita',        'name' => 'Harghita'],
        ['county_id' => '4',  'country_id' => $id, 'slug'  => 'Bistrita-Nasaud', 'name' => 'Bistrița-Năsăud'],
        ['county_id' => '5',  'country_id' => $id, 'slug'  => 'Dambovita',       'name' => 'Dâmbovița'],
        ['county_id' => '6',  'country_id' => $id, 'slug'  => 'Suceava',         'name' => 'Suceava'],
        ['county_id' => '7',  'country_id' => $id, 'slug'  => 'Botosani',        'name' => 'Botoșani'],
        ['county_id' => '8',  'country_id' => $id, 'slug'  => 'Brasov',          'name' => 'Brașov'],
        ['county_id' => '9',  'country_id' => $id, 'slug'  => 'BucureSti',       'name' => 'București'],
        ['county_id' => '10', 'country_id' => $id, 'slug'  => 'Braila',          'name' => 'Brăila'],
        ['county_id' => '11', 'country_id' => $id, 'slug'  => 'Hunedoara',       'name' => 'Hunedoara'],
        ['county_id' => '12', 'country_id' => $id, 'slug'  => 'Teleorman',       'name' => 'Teleorman'],
        ['county_id' => '13', 'country_id' => $id, 'slug'  => 'Covasna',         'name' => 'Covasna'],
        ['county_id' => '14', 'country_id' => $id, 'slug'  => 'Tulcea',          'name' => 'Tulcea'],
        ['county_id' => '15', 'country_id' => $id, 'slug'  => 'Timis',           'name' => 'Timiș'],
        ['county_id' => '16', 'country_id' => $id, 'slug'  => 'Buzau',           'name' => 'Buzău'],
        ['county_id' => '17', 'country_id' => $id, 'slug'  => 'Prahova',         'name' => 'Prahova'],
        ['county_id' => '18', 'country_id' => $id, 'slug'  => 'Ilfov',           'name' => 'Ilfov'],
        ['county_id' => '19', 'country_id' => $id, 'slug'  => 'Neamt',           'name' => 'Neamț'],
        ['county_id' => '20', 'country_id' => $id, 'slug'  => 'Cluj',            'name' => 'Cluj'],
        ['county_id' => '21', 'country_id' => $id, 'slug'  => 'Alba',            'name' => 'Alba'],
        ['county_id' => '22', 'country_id' => $id, 'slug'  => 'Giurgiu',         'name' => 'Giurgiu'],
        ['county_id' => '23', 'country_id' => $id, 'slug'  => 'Arges',           'name' => 'Argeș'],
        ['county_id' => '24', 'country_id' => $id, 'slug'  => 'Calarasi',        'name' => 'Călărași'],
        ['county_id' => '25', 'country_id' => $id, 'slug'  => 'Bihor',           'name' => 'Bihor'],
        ['county_id' => '26', 'country_id' => $id, 'slug'  => 'Iasi',            'name' => 'Iași'],
        ['county_id' => '27', 'country_id' => $id, 'slug'  => 'Valcea',          'name' => 'Vâlcea'],
        ['county_id' => '28', 'country_id' => $id, 'slug'  => 'Vrancea',         'name' => 'Vrancea'],
        ['county_id' => '29', 'country_id' => $id, 'slug'  => 'Arad',            'name' => 'Arad'],
        ['county_id' => '30', 'country_id' => $id, 'slug'  => 'Ialomita',        'name' => 'Ialomița'],
        ['county_id' => '31', 'country_id' => $id, 'slug'  => 'Caras-Severin',   'name' => 'Caraș-Severin'],
        ['county_id' => '32', 'country_id' => $id, 'slug'  => 'Galati',          'name' => 'Galați'],
        ['county_id' => '33', 'country_id' => $id, 'slug'  => 'Gorj',            'name' => 'Gorj'],
        ['county_id' => '34', 'country_id' => $id, 'slug'  => 'Constanta',       'name' => 'Constanța'],
        ['county_id' => '35', 'country_id' => $id, 'slug'  => 'Satu Mare',       'name' => 'Satu Mare'],
        ['county_id' => '36', 'country_id' => $id, 'slug'  => 'Maramures',       'name' => 'Maramureș'],
        ['county_id' => '37', 'country_id' => $id, 'slug'  => 'Mehedinti',       'name' => 'Mehedinți'],
        ['county_id' => '38', 'country_id' => $id, 'slug'  => 'Salaj',           'name' => 'Sălaj'],
        ['county_id' => '39', 'country_id' => $id, 'slug'  => 'Vaslui',          'name' => 'Vaslui'],
        ['county_id' => '40', 'country_id' => $id, 'slug'  => 'Mures',           'name' => 'Mureș'],
        ['county_id' => '41', 'country_id' => $id, 'slug'  => 'Sibiu',           'name' => 'Sibiu'],
        ['county_id' => '42', 'country_id' => $id, 'slug'  => 'Olt',             'name' => 'Olt']
      ]);
    }
  }
}
