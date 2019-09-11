<?php

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate([
            'name' => 'Dsu Admin',
            'email' => 'dsu@rvm.com',
            'password' => bcrypt('test1234'),
            'role' => '3',
            'phone' => substr(str_shuffle("0123456789"), 0, 10),
        ]);
        User::firstOrCreate([
            'name' => 'NGO Admin',
            'email' => 'ngo@rvm.com',
            'county' => [
                '_id' => 'county_romania_arges_22',
                'name' => 'Argeș'
            ],
            'city' => [
                '_id' => 'city_arges_albesti_6964',
                'name' => 'Albești'
            ],
            'password' => bcrypt('test1234'),
            'role' => '2',
            'phone' => substr(str_shuffle("0123456789"), 0, 10),
        ]);
        User::firstOrCreate([
            'name' => 'Instituition Admin',
            'email' => 'institution@rvm.com',
            'password' => bcrypt('test1234'),
            'role' => '1',
            'institution' => [
                '_id' => 'db6cdf670d80621a9a6fc92ae4af8920',
                'name' => 'Departamentul pentru Situații de Urgență'
            ],
            'phone' => substr(str_shuffle("0123456789"), 0, 10),
        ]);
        User::firstOrCreate([
            'name' => 'Rescue Officer ',
            'email' => 'officer@rvm.com',
            'password' => bcrypt('test1234'),
            'role' => '0',
            'institution' => [
                '_id' => 'db6cdf670d80621a9a6fc92ae4af8920',
                'name' => 'Departamentul pentru Situații de Urgență'
            ],
            'phone' => substr(str_shuffle("0123456789"), 0, 10),
        ]);
    }
}
