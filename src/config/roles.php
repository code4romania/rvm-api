<?php 

return [

    /*
    |--------------------------------------------------------------------------
    | Roles 
    |--------------------------------------------------------------------------
    |
    | This file is for storing roles such as
    | DSU, NGO (Organisation), Rescue Officer, Institution Admin
    |
    */

    'role' => [
        'officer' => \App\User::ROLE_RESCUE_OFFICER,
        'institution' => \App\User::ROLE_INSTITUTION_ADMIN,
        'ngo' => \App\User::ROLE_NGO_ADMIN,
        'dsu' => \App\User::ROLE_DSU_ADMIN
    ],

];
