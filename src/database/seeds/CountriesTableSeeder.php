<?php

use Illuminate\Database\Seeder;
use App\Country;

class CountriesTableSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    Country::firstOrCreate(['country_id' => '1', 'slug' => 'Romania', 'name' => 'România']);
  }
}
