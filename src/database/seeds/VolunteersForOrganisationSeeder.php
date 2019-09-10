<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VolunteersForOrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for ($i=0; $i < 100; $i++) { 
            $authorization = "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImE1NmE3MDE2ZWE4NDIxMDdiYTE1ZjUwMDlhMDk0ODFkOGNhZmFiYjlkZDk0NDUzZmFlMTBmYjlmMGE2MTA4YjIyNGUwNDdjNjVkNzQ3MzVlIn0.eyJhdWQiOiIyZTY1MmU0MDJiNWVkNGI1YWIwM2EzY2QwNTAxMmE1OCIsImp0aSI6ImE1NmE3MDE2ZWE4NDIxMDdiYTE1ZjUwMDlhMDk0ODFkOGNhZmFiYjlkZDk0NDUzZmFlMTBmYjlmMGE2MTA4YjIyNGUwNDdjNjVkNzQ3MzVlIiwiaWF0IjoxNTY4MTA3MzQwLCJuYmYiOjE1NjgxMDczNDAsImV4cCI6MTU5OTcyOTc0MCwic3ViIjoiMmU2NTJlNDAyYjVlZDRiNWFiMDNhM2NkMDUwMTBhMTQiLCJzY29wZXMiOltdfQ.q1tgYUfM4XtYJUhV9oubVeWOnJ1zXaSFXk7XtPrjJiFljuETlrop7xbDZQY8IMvmSi-gW9T8i9J8Ik18e080edHvupQynwdAEfiycCrFkca0nyoY_6HhCjsxLtAackulO2SjU8nKcXy2-xWe3RxgawoepF4ifxVO5i60Da4O70gPLjGx6EAnzAC0AipIIUsPFRXMAe6lO-Iww_FrUYltMmfwfKQAxwLSvjsQJPmNLOP6oCj1DgN28WN7TtXdxIcslCWORAY_DSLXvQgQK6YXvs3nZLA9VnHqVaIssXbcFC5ZFR1h79Uy3KnpN4luvbglIPVbaK6nCyO4plY42IS5T-RBp7XBPibUs6fvsfxM9U7O1MlntHHaGDs6lf_VgeDYCSQExkPvsQi3nSL74svkRAhRxAanoh_p-VAzqEnfzi2Hm2JR6SBJmkUAkMh2Nh75nPfDweuNzBaO_5Xb8wc_ZRh_W6x6ZmYzSoCJ_16JBF-1cJz6FVrHcJqbdrtjXdC77qTQHF-TYUPsn-BaUx5QMVtMqXI-YH1NLeylWAVCONT2AwyCk0HVN9BofAtfv0CuQvTwl6TkkKR1iviRt6fkTICLs0cvweZSJNbrCXPX_oMAt3ZO_zFTZEyV3psoZBtnOSTcGvRl_sN560a6FruMu4wdD4czTo598i42OffWV0g";

            $volunteer = array(
                'name' => $faker->name . $i,
                'email' => $i.$faker->email,
                'ssn' => '1910619272003',
                'phone' => '0711223344',
                'county' => 'county_romania_arges_22',
                'city' => 'city_arges_albesti_6964',
                'organisation_id' => '2e652e402b5ed4b5ab03a3cd0501374c',
                'courses' => [[
                    'course_name_id' => '220d2831795024e392fd883e6b008569',
                    'obtained' => '20-11-1999',
                    'accredited_by' => 'Mine',
                ]],
                'job' => 'Gradinar'
            );
            $fields_string = json_encode($volunteer);
            $url = 'http://rvm.localhost/api/volunteers';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
        }
    }
}