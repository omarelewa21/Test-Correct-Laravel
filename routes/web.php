<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
if (App::environment('local')) {
    Route::get('/testmail', function () {
        return (new \tcCore\Jobs\SendTellATeacherMail(User::where('id', 1486)->first(), 'Hallo allemaal', 'roan@sobit.nl', 1234))->render();
    });
}
Route::get('/onboarding', tcCore\Http\Livewire\Onboarding::class);
Route::get('/user/confirm_email/{EmailConfirmation}', 'tcCore\Http\Controllers\UsersController@confirmEmail');
Route::get('/inv/{shortcode}','tcCore\Http\Controllers\Api\ShortcodeController@registerClickAndRedirect');