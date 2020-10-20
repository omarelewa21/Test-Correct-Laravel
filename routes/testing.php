<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/* TEST MULTIPLE QUESTION STUFF */

Route::get('/testing/selenium', 'Testing\TestingController@seleniumState')->name('testing.seleniumState');
Route::post('/testing/selenium', 'Testing\TestingController@seleniumToggle')->name('testing.seleniumToggle');
Route::post('/testing/testing', 'Testing\TestingController@store')->name('testing.store');

