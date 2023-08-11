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
use tcCore\Http\Controllers\Testing\TestingController;

/* TEST MULTIPLE QUESTION STUFF */

//Route::get('/testing/selenium', [TestingController::class, 'seleniumState'])->name('testing.seleniumState');
//Route::post('/testing/selenium', [TestingController::class, 'seleniumToggle'])->name('testing.seleniumToggle');
//Route::post('/testing/testing', [TestingController::class, 'store'])->name('testing.store');

use Illuminate\Support\Facades\Route;
use tcCore\Http\Controllers\Testing\SeleniumController;
Route::group(['prefix'=>'/__selenium__', 'as'=>'selenium.'], function () {
    Route::post('/factory', [SeleniumController::class, 'factory'])->name('factory');
    Route::post('/login', [SeleniumController::class, 'login'])->name('login');
    Route::post('/logout', [SeleniumController::class, 'logout'])->name('logout');
    Route::post('/artisan', [SeleniumController::class, 'artisan'])->name('artisan');
    Route::post('/run-php', [SeleniumController::class, 'runPhp'])->name('run-php');
    Route::get('/csrf_token', [SeleniumController::class, 'csrfToken'])->name('csrf-token');
    Route::post('/routes', [SeleniumController::class, 'routes'])->name('routes');
    Route::post('/current-user', [SeleniumController::class, 'currentUser'])->name('current-user');
});