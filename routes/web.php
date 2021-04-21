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
Route::get('/login', tcCore\Http\Livewire\Auth\Login::class)->name('auth.login');


Route::middleware(['auth.temp'])->group(function () {
    Route::get('/redirect-with-temporary-login/{temporary_login}',tcCore\Http\Controllers\TemporaryLoginController::class)->name('auth.temporary-login-redirect');
});

Route::middleware('auth')->group(function () {
    Route::middleware(['dll', 'student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/test-take-overview/{test_take}', [tcCore\Http\Controllers\TestTakeLaravelController::class, 'overview'])->name('test-take-overview');
        Route::get('/test-take-laravel/{test_take}', [tcCore\Http\Controllers\TestTakeLaravelController::class, 'show'])->name('test-take-laravel');
        Route::get('/attachment/{attachment}/{answer}', [tcCore\Http\Controllers\AttachmentsLaravelController::class, 'show'])->name('question-attachment-show');
        Route::get('/attachment/pdf/{attachment}/{answer}', [tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'show'])->name('question-pdf-attachment-show');
        Route::get('/drawing_question_answers/{answer}', [tcCore\Http\Controllers\DrawingQuestionLaravelController::class, 'show'])->name('drawing-question-answer');
    });

    Route::middleware(['dll', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/preview/{test}', [tcCore\Http\Controllers\PreviewLaravelController::class, 'show'])->name('test-preview');
        Route::get('/preview/attachment/{attachment}/{question}', [tcCore\Http\Controllers\AttachmentsLaravelController::class, 'showPreview'])->name('preview.question-attachment-show');
        Route::get('/preview/attachment/pdf/{attachment}/{question}', [tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'showPreview'])->name('preview.question-pdf-attachment-show');
    });
});


