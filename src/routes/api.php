<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['json.response']], function () {

    // public routes
    Route::post('/login', 'AuthController@login')->name('login.api');
    // Route::post('/register', 'AuthController@register')->name('register.api');
    Route::post('/recoverpassword', 'AuthController@recoverpassword')->name('recoverpassword');
    Route::post('/resetpassword', 'AuthController@resetpassword');
    Route::get('/cities','StaticController@getAllCities');
    Route::get('/counties','StaticController@getAllCounties');
    Route::get('/resources/categories', 'ResourceController@getAllResourceCategories');

    /** Route for downloading the template file for resources. */
    Route::get('resources/template', 'ResourceController@template');
    /** Route for downloading the template file for volunteers. */
    Route::get('volunteers/template', 'VolunteerController@template');

    // private routes
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/profile', 'AuthController@profile')->name('profile.api');
        Route::get('/logout', 'AuthController@logout')->name('logout');
        
        Route::middleware(['checkRole:dsu'])->group(function () {
            Route::get('organisations/{id}/email', 'OrganisationController@sendNotification');
        });

        Route::middleware(['checkRole:dsu,institution'])->group(function () {
            Route::get('users', 'UserController@index');
            Route::post('users', 'UserController@store');
        });

        Route::middleware(['checkRole:dsu,ngo'])->group(function () {
            Route::get('organisations', 'OrganisationController@index');
            Route::get('organisations/{id}', 'OrganisationController@show');
            Route::get('organisations/{id}/volunteers', 'OrganisationController@showVolunteers');
            Route::get('organisations/{id}/resources', 'OrganisationController@showResources');
            Route::get('organisations/{id}/validate', 'OrganisationController@validateData');
            Route::post('organisations', 'OrganisationController@store');
            Route::put('organisations/{id}', 'OrganisationController@update');
            Route::delete('organisations/{id}', 'OrganisationController@delete');

            Route::get('resources', 'ResourceController@index');
            Route::get('resources/list', 'ResourceController@list');
            Route::get('resources/organisations', 'ResourceController@showOrganisations');
            Route::get('resources/{id}', 'ResourceController@show');
            Route::get('resources/by_slug/{slug}', 'ResourceController@by_slug');
            Route::post('resources', 'ResourceController@store');
            Route::put('resources/{id}', 'ResourceController@update');
            Route::delete('resources/{id}', 'ResourceController@delete');
            Route::post('resources/import', 'ResourceController@importResources');
            
            Route::post('volunteers', 'VolunteerController@store');
            Route::put('volunteers/{id}', 'VolunteerController@update');
            Route::delete('volunteers/{id}', 'VolunteerController@delete');
            Route::post('volunteers/import', 'VolunteerController@importVolunteers');
          });


        Route::middleware(['checkRole:dsu,ngo,institution'])->group(function () {
            Route::get('volunteers', 'VolunteerController@index');
            Route::get('volunteers/{id}', 'VolunteerController@show');
            Route::get('users/{id}', 'UserController@show');
            Route::put('users/{id}', 'UserController@update');
            Route::delete('users/{id}', 'UserController@delete');
            Route::get('volunteers/{id}/allocations', 'VolunteerController@allocations');
        });
        Route::get('filter/organisations', 'FilterController@filterOrganisationsName');
        Route::get('filter/volunteers/courses', 'FilterController@filterVolunteerCourses');
        Route::get('filter/users/institutions', 'FilterController@filterInstitutionUsers');
        Route::get('filter/accreditedby', 'FilterController@filterAccreditedBy');
        Route::get('filter/map', 'FilterController@filterMap');
    });
});