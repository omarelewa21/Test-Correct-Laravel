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
Route::get('/inv/{shortcode}', [tcCore\Http\Controllers\Api\ShortcodeController::class, 'registerClickAndRedirect']);
Route::get('/', tcCore\Http\Livewire\Onboarding::class);

Route::get('/password-reset', tcCore\Http\Livewire\PasswordReset::class)->name('password.reset');
Route::post('/send_password_reset', [tcCore\Http\Controllers\Auth\PasswordController::class, 'sendPasswordReset']);


Route::get('/login', tcCore\Http\Livewire\Auth\Login::class)->name('auth.login');

Route::get('/magister', [\tcCore\Http\Controllers\MagisterController::class, 'index']);
Route::get('/somtoday', [\tcCore\Http\Controllers\SomTodayController::class, 'index']);
Route::get('/uwlr/fetcher', tcCore\Http\Livewire\UwlrFetcher::class)->name('uwlr.fetcher');
Route::get('/uwlr', tcCore\Http\Livewire\UwlrGrid::class)->name('uwlr.grid');

Route::get('/ckeditor/plugins/ckeditor_wiris/integration/configurationjs', [\tcCore\Http\Controllers\WirisIntegrationController::class, 'configurationjs']);


Route::middleware(['auth.temp'])->group(function () {
    Route::get('/redirect-with-temporary-login/{temporary_login}',[tcCore\Http\Controllers\TemporaryLoginController::class,'redirect'])->name('auth.temporary-login.redirect');
});

Route::middleware('auth')->group(function () {

    Route::get('/temporary-login-to-cake',[tcCore\Http\Controllers\TemporaryLoginController::class,'toCake'])->name('auth.temporary-login.to-cake');

    Route::get('/questions/inlineimage/{image}', [tcCore\Http\Controllers\QuestionsController::class, 'inlineImageLaravel'])->name('inline-image');

    Route::middleware(['dll', 'student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/test-take-overview/{test_take}', [tcCore\Http\Controllers\TestTakeLaravelController::class, 'overview'])->name('test-take-overview');
        Route::get('/test-take-laravel/{test_take}', [tcCore\Http\Controllers\TestTakeLaravelController::class, 'show'])->name('test-take-laravel');
        Route::get('/attachment/{attachment}/{answer}', [tcCore\Http\Controllers\AttachmentsLaravelController::class, 'show'])->name('question-attachment-show');
        Route::get('/attachment/pdf/{attachment}/{answer}', [tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'show'])->name('question-pdf-attachment-show');
        Route::get('/drawing_question_answers/{answer}', [tcCore\Http\Controllers\DrawingQuestionLaravelController::class, 'show'])->name('drawing-question-answer');
        Route::get('/dashboard', tcCore\Http\Livewire\Student\Dashboard::class)->name('dashboard');
        Route::get('/splash',\tcCore\Http\Livewire\Student\Splash::class)->name('splash');

        Route::get('/dashboard/logout', [tcCore\Http\Livewire\Student\Dashboard::class, 'logout'])->name('dashboard.logout');
        Route::get('/test-takes', tcCore\Http\Livewire\Student\TestTakes::class)->name('test-takes');
        Route::get('/waiting-room', tcCore\Http\Livewire\Student\WaitingRoom::class)->name('waiting-room');
    });

    Route::middleware(['dll', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/preview/{test}', [tcCore\Http\Controllers\PreviewLaravelController::class, 'show'])->name('test-preview');
        Route::get('/preview/attachment/{attachment}/{question}', [tcCore\Http\Controllers\AttachmentsLaravelController::class, 'showPreview'])->name('preview.question-attachment-show');
        Route::get('/preview/attachment/pdf/{attachment}/{question}', [tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'showPreview'])->name('preview.question-pdf-attachment-show');
        Route::get('/questions/open-short/{action}', tcCore\Http\Livewire\Teacher\Questions\OpenShort::class)->name('questions-add-open-short');
    });

    Route::middleware(['dll', 'student'])->prefix('appapi')->name('appapi')->group(function() {
        Route::put('/test_participant/{test_participant}/hand_in', [tcCore\Http\Controllers\AppApi::class, 'handIn'])->name('appapi-hand-in');
    });

    Route::get('/entree-link', tcCore\Http\Livewire\Auth\EntreeLink::class)->name('entree-link');
});
Route::middleware(['guest', 'auth.temp'])->group(function () {
    Route::get('/show-test-with-temporary-login/{test}/{temporary_login}', [tcCore\Http\Controllers\TemporaryLoginController::class, 'teacherPreview'])->name('auth.teacher.show-test-with-short-code');
    Route::get('/start-test-take-with-temporary-login/{test_take}/{temporary_login}', [tcCore\Http\Controllers\TemporaryLoginController::class, 'studentPlayer'])->name('auth.login_test_take_with_short_code');
});
Route::middleware(['guest_choice'])->group(function () {
    Route::get('/guest-choice', tcCore\Http\Livewire\Student\GuestUserChoosingPage::class)->name('guest-choice');
    Route::get('/guest-graded-overview', tcCore\Http\Livewire\Student\GuestGradedOverview::class)->name('guest-graded-overview');
});
