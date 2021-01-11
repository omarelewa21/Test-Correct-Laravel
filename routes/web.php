<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use tcCore\Http\Livewire\Auth\Login;
use tcCore\Jobs\SendOnboardingWelcomeMail;
use tcCore\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/onboarding', tcCore\Http\Livewire\Onboarding::class)->name('onboarding.welcome');
Route::get('/user/confirm_email/{EmailConfirmation}', 'tcCore\Http\Controllers\UsersController@confirmEmail');
Route::get('/inv/{shortcode}','tcCore\Http\Controllers\Api\ShortcodeController@registerClickAndRedirect');
Route::get('/', tcCore\Http\Livewire\Onboarding::class);

Route::middleware('auth')->prefix('student')->name('student.')->group(function () {
    Route::get('/test-take/{test_take}', tcCore\Http\Livewire\Student\TestTake::class)->name('test-take');
});

/**
 * Authentication
 */
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('auth.login');
});
