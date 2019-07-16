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

    // private routes
    Route::middleware('auth:api')->group(function () {
        Route::get('/profile', 'AuthController@profile')->name('profile.api');
        Route::get('/logout', 'AuthController@logout')->name('logout');
    });

    Route::middleware('checkRole')->group(function () {

    });

    Route::get('organisations', 'OrganisationController@index');
    Route::get('organisations/{id}', 'OrganisationController@show');
    Route::post('organisations', 'OrganisationController@store');
    Route::put('organisations/{id}', 'OrganisationController@update');
    Route::delete('organisations/{id}', 'OrganisationController@delete');

    Route::get('volunteers', 'VolunteerController@index');
    Route::get('volunteers/{id}', 'VolunteerController@show');
    Route::post('volunteers', 'VolunteerController@store');
    Route::put('volunteers/{id}', 'VolunteerController@update');
    Route::delete('volunteers/{id}', 'VolunteerController@delete');

    Route::get('resources', 'ResourcesController@index');
    Route::get('resources/{id}', 'ResourcesController@show');
    Route::post('resources', 'ResourcesController@store');
    Route::put('resources/{id}', 'ResourcesController@update');
    Route::delete('resources/{id}', 'ResourcesController@delete');

    Route::get('courses', 'CourseController@index');
    Route::get('courses/{id}', 'CourseController@show');
    Route::post('courses', 'CourseController@store');
    Route::put('courses/{id}', 'CourseController@update');
    Route::delete('courses/{id}', 'CourseController@delete');
});