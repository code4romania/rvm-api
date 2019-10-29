<?php

use Illuminate\Database\Seeder;

class DatabaseSeed extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $this->call([
      CourseNamesTableSeeder::class,
      InstitutionsTableSeeder::class,
      ResourceCategoriesTableSeeder::class,
      CountriesTableSeeder::class,
      CountiesTableSeeder::class,
      CitiesTableSeeder::class,
      StaticsViewsSeeder::class
    ]);
  }
}
