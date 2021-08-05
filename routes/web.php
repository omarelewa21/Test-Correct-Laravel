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
Route::post('/send_password_reset', [tcCore\Http\Controllers\Auth\PasswordController::class, 'sendPasswordReset']);


    Route::get('/login', tcCore\Http\Livewire\Auth\Login::class)->name('auth.login');

    Route::get('/magister', [\tcCore\Http\Controllers\MagisterController::class, 'index']);
    Route::get('/somtoday', [\tcCore\Http\Controllers\SomTodayController::class, 'index']);
    Route::get('/uwlr/fetcher', tcCore\Http\Livewire\UwlrFetcher::class)->name('uwlr.fetcher');
    Route::get('/uwlr', tcCore\Http\Livewire\UwlrGrid::class)->name('uwlr.grid');


Route::middleware(['auth.temp'])->group(function () {
    Route::get('/redirect-with-temporary-login/{temporary_login}',tcCore\Http\Controllers\TemporaryLoginController::class)->name('auth.temporary-login-redirect');
});

Route::middleware('auth')->group(function () {
    Route::get('/questions/inlineimage/{image}', [tcCore\Http\Controllers\QuestionsController::class, 'inlineimageLaravel']);

    Route::middleware(['dll', 'student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/test-take-overview/{test_take}', [tcCore\Http\Controllers\TestTakeLaravelController::class, 'overview'])->name('test-take-overview');
        Route::get('/test-take-laravel/{test_take}', [tcCore\Http\Controllers\TestTakeLaravelController::class, 'show'])->name('test-take-laravel');
        Route::get('/attachment/{attachment}/{answer}', [tcCore\Http\Controllers\AttachmentsLaravelController::class, 'show'])->name('question-attachment-show');
        Route::get('/attachment/pdf/{attachment}/{answer}', [tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'show'])->name('question-pdf-attachment-show');
        Route::get('/drawing_question_answers/{answer}', [tcCore\Http\Controllers\DrawingQuestionLaravelController::class, 'show'])->name('drawing-question-answer');
        Route::get('/dashboard', tcCore\Http\Livewire\Student\Dashboard::class)->name('dashboard');
        Route::get('/dashboard/logout', [tcCore\Http\Livewire\Student\Dashboard::class, 'logout'])->name('dashboard.logout');
        Route::get('/test-takes', tcCore\Http\Livewire\Student\TestTakes::class)->name('test-takes');
        Route::get('/waiting-room', tcCore\Http\Livewire\Student\WaitingRoom::class)->name('waiting-room');
    });

    Route::middleware(['dll', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/preview/{test}', [tcCore\Http\Controllers\PreviewLaravelController::class, 'show'])->name('test-preview');
        Route::get('/preview/attachment/{attachment}/{question}', [tcCore\Http\Controllers\AttachmentsLaravelController::class, 'showPreview'])->name('preview.question-attachment-show');
        Route::get('/preview/attachment/pdf/{attachment}/{question}', [tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'showPreview'])->name('preview.question-pdf-attachment-show');
    });
});
Route::middleware(['guest', 'auth.temp'])->group(function () {
    Route::get('/show-test-with-temporary-login/{test}/{temporary_login}', [tcCore\Http\Controllers\TemporaryLoginController::class, 'teacherPreview' ])->name('auth.teacher.show-test-with-short-code');
    Route::get('/start-test-take-with-temporary-login/{test_take}/{temporary_login}', [tcCore\Http\Controllers\TemporaryLoginController::class, 'studentPlayer'])->name('auth.login_test_take_with_short_code');
});
