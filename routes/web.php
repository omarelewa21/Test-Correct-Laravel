<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/user/confirm_email/{EmailConfirmation}', [tcCore\Http\Controllers\UsersController::class, 'confirmEmail']);
Route::get('/inv/{shortcode}',[tcCore\Http\Controllers\Api\ShortcodeController::class, 'registerClickAndRedirect']);
Route::get('/', tcCore\Http\Livewire\Onboarding::class);

Route::get('/password-reset', tcCore\Http\Livewire\PasswordReset::class)->name('password.reset');

Route::middleware(['auth', 'dll'])->prefix('student')->name('student.')->group(function () {
    Route::get('/test-take/{test_take}', tcCore\Http\Livewire\Student\TestTake::class)->name('test-take');
    Route::get('/test-take-stub/{test_take}', tcCore\Http\Livewire\Student\TesttakeStub::class)->name('test-take-stub');
    Route::get('/test-take-overview/{test_take}', [tcCore\Http\Controllers\TestTakeLaravelController::class, 'overview'])->name('test-take-overview');
    Route::get('/test-take-laravel/{test_take}', [tcCore\Http\Controllers\TestTakeLaravelController::class, 'show'])->name('test-take-laravel');
    Route::get('/attachment/{attachment}/{answer}', [tcCore\Http\Controllers\AttachmentsLaravelController::class, 'show'])->name('question-attachment-show');
    Route::get('/attachment/pdf/{attachment}/{answer}', [tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'show'])->name('question-pdf-attachment-show');
    Route::get('/drawing_question_answers/{answer}', [tcCore\Http\Controllers\DrawingQuestionLaravelController::class, 'show'])->name('drawing-question-answer');
});


/** @TODO needs prefix for teacher? */
Route::middleware(['auth', 'dll'])->group(function () {
    Route::get('/preview/{test}/{user}', [tcCore\Http\Controllers\PreviewLaravelController::class, 'show'])->name('test-preview');
});


/**
 * Authentication
 */

Route::middleware('guest')->group(function () {
    Route::get('/start-test-take-with-short-code/{test_take}/{short_code}', [tcCore\Http\Controllers\ShortCodeController::class, 'loginAndRedirect'])->name('auth.login_test_take_with_short_code');
    Route::get('/show-test-with-short-code/{test}/{short_code}', [tcCore\Http\Controllers\ShortCodeController::class, 'loginAndRedirect'])->name('auth.teacher.show-test-with-short-code');
    Route::get('/login', tcCore\Http\Livewire\Auth\Login::class)->name('auth.login');
});



