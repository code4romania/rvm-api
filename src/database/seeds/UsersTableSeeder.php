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
            'password' => bcrypt('test1234'),
            'role' => '2',
            'phone' => substr(str_shuffle("0123456789"), 0, 10),
        ]);
        User::firstOrCreate([
            'name' => 'Instituition Admin',
            'email' => 'institution@rvm.com',
            'password' => bcrypt('test1234'),
            'role' => '1',
            'phone' => substr(str_shuffle("0123456789"), 0, 10),
        ]);
        User::firstOrCreate([
            'name' => 'Rescue Officer ',
            'email' => 'officer@rvm.com',
            'password' => bcrypt('test1234'),
            'role' => '0',
            'phone' => substr(str_shuffle("0123456789"), 0, 10),
        ]);
    }
}
