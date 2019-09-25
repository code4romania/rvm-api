<?php

use Illuminate\Database\Seeder;
use App\DBViews\StaticCitiesBySlugAndNameView;
use App\DBViews\StaticCountiesBySlugAndNameView;

class StaticsViewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        $client = \DB::connection('statics')->getCouchDBClient();

        $client->createDesignDocument('cities', new StaticCitiesBySlugAndNameView());
		    $client->createDesignDocument('counties', new StaticCountiesBySlugAndNameView());
    }
}
