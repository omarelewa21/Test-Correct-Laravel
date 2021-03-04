<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use tcCore\Http\Controllers\DrawingQuestionLaravelController;
use tcCore\Http\Controllers\ShortCodeController;
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
    Route::get('/test-take-stub/{test_take}', tcCore\Http\Livewire\Student\TesttakeStub::class)->name('test-take-stub');
    Route::get('/test-take-overview/{test_take}', [\tcCore\Http\Controllers\TestTakeLaravelController::class, 'overview'])->name('test-take-overview');
    Route::get('/test-take-laravel/{test_take}', [\tcCore\Http\Controllers\TestTakeLaravelController::class, 'show'])->name('test-take-laravel');
    Route::get('/attachment/{attachment}', [\tcCore\Http\Controllers\AttachmentsLaravelController::class, 'show'])->name('question-attachment-show');
    Route::get('/attachment/pdf/{attachment}', [\tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'show'])->name('question-pdf-attachment-show');
    Route::get('/drawing_question_answers/{answer}', [DrawingQuestionLaravelController::class, 'show'])->name('drawing-question-answer');
});

Route::get('/plan', function() {
    return  view('plan-test-take');
});

/**
 * Authentication
 */
Route::middleware('guest')->group(function () {
    Route::get('/start-test-take-with-short-code/{test_take}/{short_code}', [ShortCodeController::class, 'loginAndRedirect'])->name('auth.login_test_take_with_short_code');
    Route::get('/login', Login::class)->name('auth.login');
});





Route::get('/magister', [\tcCore\Http\Controllers\MagisterController::class, 'index']);
Route::get('/somtoday', [\tcCore\Http\Controllers\SomeTodayController::class, 'index']);
Route::get('/uwlr', tcCore\Http\Livewire\UwlrFetcher::class)->name('uwlr');
