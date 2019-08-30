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
    Route::post('/register', 'AuthController@register')->name('register.api');
    Route::post('/recoverpassword', 'AuthController@recoverpassword');
    Route::post('/resetpassword', 'AuthController@resetpassword');
    Route::get('/cities','StaticController@getAllCities');
    Route::get('/counties','StaticController@getAllCounties');
    Route::get('/resources/categories', 'ResourceController@getAllResourceCategories');

    // private routes
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/profile', 'AuthController@profile')->name('profile.api');
        Route::get('/logout', 'AuthController@logout')->name('logout');

        Route::middleware(['checkRole:dsu'])->group(function () {
            Route::get('users', 'UserController@index');
            Route::get('organisations', 'OrganisationController@index');
            Route::get('resources', 'ResourceController@index');
            Route::get('volunteers', 'VolunteerController@index');
        }); 

        Route::middleware(['checkRole:dsu,institution'])->group(function () {
            Route::get('users/{id}', 'UserController@show');
            Route::post('users', 'UserController@store');
            Route::put('users/{id}', 'UserController@update');
            Route::delete('users/{id}', 'UserController@delete');
        });

        Route::middleware(['checkRole:dsu,ngo'])->group(function () {
            Route::get('organisations/{id}', 'OrganisationController@show');
            Route::get('organisations/{id}/volunteers', 'OrganisationController@showVolunteers');
            Route::get('organisations/{id}/resources', 'OrganisationController@showResources');
            Route::post('organisations', 'OrganisationController@store');
            Route::put('organisations/{id}', 'OrganisationController@update');
            Route::delete('organisations/{id}', 'OrganisationController@delete');

            Route::get('resources/list', 'ResourceController@list');
            Route::get('resources/organisations', 'ResourceController@showOrganisations');
            Route::get('resources/{id}', 'ResourceController@show');
            Route::post('resources', 'ResourceController@store');
            Route::put('resources/{id}', 'ResourceController@update');
            Route::delete('resources/{id}', 'ResourceController@delete');

            Route::get('courses', 'CourseController@index');
            Route::get('courses/{id}', 'CourseController@show');
            Route::post('courses', 'CourseController@store');
            Route::put('courses/{id}', 'CourseController@update');
            Route::delete('courses/{id}', 'CourseController@delete');
        });

        Route::middleware(['checkRole:dsu,ngo,institution'])->group(function () {
            Route::get('volunteers/{id}', 'VolunteerController@show');
            Route::post('volunteers', 'VolunteerController@store');
            Route::put('volunteers/{id}', 'VolunteerController@update');
            Route::delete('volunteers/{id}', 'VolunteerController@delete');
        });

        Route::get('filter/resources/type_name', 'FilterController@filterResourcesTypeName');
        Route::get('filter/organisations/name', 'FilterController@filterOrganisationsName');
        // Route::get('filter/specialization/name', 'FilterController@filterSpecialziationsName');
        // Route::get('filter/job/name', 'FilterController@filterJobsName');
    });
});