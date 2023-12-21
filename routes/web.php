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

Route::get('redirect-to-dashboard', [\tcCore\Http\Controllers\DashboardController::class, 'index'])->name('redirect-to-dashboard');
Route::get('redirect-to-entree', [\tcCore\Http\Controllers\Saml2Controller::class, 'redirectToEntree'])->name('redirect-to-entree');

Route::get('appapi/feature_flags',[tcCore\Http\Controllers\AppApiController::class,'featureFlags'])->name('appapi.feature_flags');

Route::get('/onboarding', tcCore\Http\Livewire\Onboarding::class)->name('onboarding.welcome');
Route::get('/entree/onboarding', tcCore\Http\Livewire\EntreeOnboarding::class)->name('onboarding.welcome.entree');
Route::get('/user/confirm_email/{EmailConfirmation}', [tcCore\Http\Controllers\UsersController::class, 'confirmEmail']);
Route::get('/inv/{shortcode}', [tcCore\Http\Controllers\Api\ShortcodeController::class, 'registerClickAndRedirect']);
Route::get('/', tcCore\Http\Livewire\Onboarding::class);

Route::get('/password-reset', tcCore\Http\Livewire\PasswordReset::class)->name('password.reset');
Route::post('/send_password_reset', [tcCore\Http\Controllers\Auth\PasswordController::class, 'sendPasswordReset']);

Route::get('/entree/register', [tcCore\Http\Controllers\Saml2Controller::class, 'register'])->name('entree.register');

Route::get('/login', tcCore\Http\Livewire\Auth\Login::class)->name('auth.login');

Route::get('/magister', [\tcCore\Http\Controllers\MagisterController::class, 'index']);
Route::get('/somtoday', [\tcCore\Http\Controllers\SomTodayController::class, 'index']);
Route::get('/uwlr/fetcher', tcCore\Http\Livewire\UwlrFetcher::class)->name('uwlr.fetcher');
Route::get('/uwlr', tcCore\Http\Livewire\UwlrGrid::class)->name('uwlr.grid');

Route::get('/ckeditor/plugins/ckeditor_wiris/integration/configurationjs', [\tcCore\Http\Controllers\WirisIntegrationController::class, 'configurationjs']);
Route::post('integration/configurationjs', [\tcCore\Http\Controllers\WirisIntegrationController::class, 'configurationjs']);
Route::post('/wiris/createimage', [\tcCore\Http\Controllers\WirisIntegrationController::class, 'createimage']);
Route::post('/wiris/showimage', [\tcCore\Http\Controllers\WirisIntegrationController::class, 'showimage']);
Route::get('/wiris/showimage', [\tcCore\Http\Controllers\WirisIntegrationController::class, 'showimage']);
Route::get('/get_app_version', [\tcCore\Http\Helpers\AppVersionDetector::class, 'getAppVersion']);
Route::get('/appapi/version_info', [\tcCore\Http\Controllers\AppApiController::class, 'versionInfo']);
Route::get('/appapi/get_current_date', [\tcCore\Http\Controllers\AppApiController::class, 'getCurrentDate']);
Route::get('/directlink/{testTakeUuid}', [\tcCore\Http\Controllers\TestTakeLaravelController::class, 'directLink'])->name('take.directLink');

Route::get('styleguide', \tcCore\Http\Livewire\ComponentStyleguide::class)->name('styleguide');

if (\tcCore\Http\Helpers\BaseHelper::notProduction()) {
    Route::get('entree/testSession', \tcCore\Http\Controllers\EntreeTestSession::class);
}

Route::middleware(['auth.temp'])->group(function () {
    Route::get('/redirect-with-temporary-login/{temporary_login}', [tcCore\Http\Controllers\TemporaryLoginController::class, 'redirect'])->name('auth.temporary-login.redirect');
});

Route::middleware('auth')->group(function () {
    Route::get('/log-out-as-user-log-in-as-support', [tcCore\Http\Controllers\SupportEscapeController::class, 'index'])->name('support.return_as_support_user');
    Route::get('/temporary-login-to-cake', [tcCore\Http\Controllers\TemporaryLoginController::class, 'toCake'])->name('auth.temporary-login.to-cake');
    Route::get('/entree-link', tcCore\Http\Livewire\Auth\EntreeLink::class)->name('entree-link');
    Route::get('/questions/inlineimage/{image}', [tcCore\Http\Controllers\QuestionsController::class, 'inlineImageLaravel'])->name('inline-image');
    Route::get('/drawing-question/{drawingQuestion}/{identifier}/answer', [tcCore\Http\Controllers\QuestionsController::class, 'drawingQuestionAnswerBackgroundImage'])->name('drawing-question.background-answer-svg');
    Route::get('/drawing-question/{drawingQuestion}/{identifier}/question', [tcCore\Http\Controllers\QuestionsController::class, 'drawingQuestionQuestionBackgroundImage'])->name('drawing-question.background-question-svg');
    Route::get('/drawing-question/{drawingQuestion}/correction-model', [tcCore\Http\Controllers\QuestionsController::class, 'drawingQuestionCorrectionModelPng'])->name('drawing-question.correction-model');
    Route::get('/drawing-question/{drawingQuestion}/svg', [tcCore\Http\Controllers\QuestionsController::class, 'drawingQuestionSvg'])->name('drawing-question.svg');
    Route::get('/infos/inline-image/{image}', [tcCore\Http\Controllers\InfoController::class, 'getInlineImage']);
    Route::get('/account', [\tcCore\Http\Controllers\UsersController::class, 'account'])->name('users.account');
    Route::middleware(['dll', 'student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/test-take-overview/{test_take}', [tcCore\Http\Controllers\TestTakeLaravelController::class, 'overview'])->name('test-take-overview');
        Route::get('/test-take-laravel/{test_take}', [tcCore\Http\Controllers\TestTakeLaravelController::class, 'show'])->name('test-take-laravel');
        Route::get('/attachment/preview/{attachment}/{question}', [tcCore\Http\Controllers\AttachmentsLaravelController::class, 'showPreview'])->name('question-attachment-show');
        Route::get('/attachment/{attachment}/{answer}', [tcCore\Http\Controllers\AttachmentsLaravelController::class, 'show'])->name('answer-attachment-show');
        Route::get('/attachment/pdf/preview/{attachment}/{question}', [tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'showPreview'])->name('question-pdf-attachment-show');
        Route::get('/attachment/pdf/{attachment}/{answer}', [tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'show'])->name('answer-pdf-attachment-show');
        Route::get('/drawing_question_answers/{answer}', [tcCore\Http\Controllers\DrawingQuestionLaravelController::class, 'show'])->name('drawing-question-answer');
        Route::get('/drawing_question_answer_model/{question}', [tcCore\Http\Controllers\DrawingQuestionLaravelController::class, 'showAnswerModel'])->name('drawing-question-answer-model');
        Route::get('/dashboard', tcCore\Http\Livewire\Student\Dashboard::class)->name('dashboard');
        Route::get('/splash', \tcCore\Http\Livewire\Student\Splash::class)->name('splash');
        Route::get('/dashboard/logout', [tcCore\Http\Livewire\Student\Dashboard::class, 'logout'])->name('dashboard.logout');
        Route::get('/test-takes', tcCore\Http\Livewire\Student\TestTakes::class)->name('test-takes');
        Route::get('/waiting-room', tcCore\Http\Livewire\Student\WaitingRoom::class)->name('waiting-room');
        Route::get('/analyses', tcCore\Http\Livewire\Analyses\AnalysesOverviewDashboard::class)->name('analyses.show');
        Route::get('/analyses/subject/{subject}', tcCore\Http\Livewire\Analyses\AnalysesSubjectDashboard::class)->name('analyses.subject.show');
        Route::get('/analyses/attainment/{baseAttainment}', tcCore\Http\Livewire\Analyses\AnalysesAttainmentDashboard::class)->name('analyses.attainment.show');
        Route::get('/analyses/sub-attainment/{baseAttainment}', tcCore\Http\Livewire\Analyses\AnalysesSubAttainmentDashboard::class)->name('analyses.subattainment.show');
        Route::get('/analyses/sub-sub-attainment/{baseAttainment}', tcCore\Http\Livewire\Analyses\AnalysesSubSubAttainmentDashboard::class)->name('analyses.subsubattainment.show');
        Route::get('/co-learning/{test_take}', \tcCore\Http\Livewire\Student\CoLearning::class)->name('co-learning');
        Route::get('/review/{testTakeUuid}', \tcCore\Http\Livewire\Student\TestReview::class)->name('test-review');
    });

    Route::middleware(['dll', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/analyses/{student_uuid}/{class_uuid}', tcCore\Http\Livewire\Analyses\AnalysesOverviewDashboard::class)->name('analyses.show');
        Route::get('/analyses/{student_uuid}/{class_uuid}/subject/{subject}', tcCore\Http\Livewire\Analyses\AnalysesSubjectDashboard::class)->name('analyses.subject.show');
        Route::get('/analyses/{student_uuid}/{class_uuid}/attainment/{baseAttainment}', tcCore\Http\Livewire\Analyses\AnalysesAttainmentDashboard::class)->name('analyses.attainment.show');
        Route::get('/analyses/{student_uuid}/{class_uuid}/sub-attainment/{baseAttainment}', tcCore\Http\Livewire\Analyses\AnalysesSubAttainmentDashboard::class)->name('analyses.subattainment.show');
        Route::get('/analyses/{student_uuid}/{class_uuid}/sub-sub-attainment/{baseAttainment}', tcCore\Http\Livewire\Analyses\AnalysesSubSubAttainmentDashboard::class)->name('analyses.subsubattainment.show');

        Route::get('/preview/{test}', [tcCore\Http\Controllers\PreviewLaravelController::class, 'show'])->name('test-preview');
        Route::get('/preview/attachment/{attachment}/{question}', [tcCore\Http\Controllers\AttachmentsLaravelController::class, 'showPreview'])->name('preview.question-attachment-show');
        Route::get('/preview/attachment/pdf/{attachment}/{question}', [tcCore\Http\Controllers\PdfAttachmentsLaravelController::class, 'showPreview'])->name('preview.question-pdf-attachment-show');
        Route::get('/question-editor', tcCore\Http\Livewire\Teacher\Cms\Constructor::class)->name('question-editor');
        Route::get('/tests', tcCore\Http\Livewire\Teacher\TestsOverview::class)->name('tests');
        Route::get('/test-detail/{uuid}', tcCore\Http\Livewire\Teacher\TestDetail::class)->name('test-detail');
        Route::get('/preview/answer_model/{test}', [tcCore\Http\Controllers\PreviewAnswerModelController::class, 'show'])->name('test-answer-model');
        Route::get('/preview/pdf/test_take_answers/{test_take}', [tcCore\Http\Controllers\PreviewTestTakeController::class, 'show'])->name('preview.test_take_answers_pdf');
        Route::get('/preview/pdf/test/{test}', [tcCore\Http\Controllers\PrintTestController::class, 'showTest'])->name('preview.test_pdf');
        Route::get('/preview/pdf/test_opgaven/{test}', [tcCore\Http\Controllers\PrintTestController::class, 'showTestOpgaven'])->name('preview.test_opgaven_pdf');
        Route::get('/preview/pdf/test_take/{test_take}', [tcCore\Http\Controllers\PrintTestController::class, 'showTestTake'])->name('preview.test_take_pdf');
        Route::get('/preview/pdf/test_attachments/{test}', [tcCore\Http\Controllers\PrintTestController::class, 'downloadTestAttachments'])->name('preview.test_attachments');
        Route::get('/preview/pdf/grade_list/{test_take}', [tcCore\Http\Controllers\PrintTestController::class, 'showGradeList'])->name('pdf.grade-list');
        Route::get('/test_takes/{stage}', \tcCore\Http\Livewire\Teacher\TestTakeOverview::class)->name('test-takes');
        Route::get('/upload_test', \tcCore\Http\Livewire\Teacher\UploadTest::class)->name('upload-tests');
        Route::get('/file-management/testuploads', \tcCore\Http\Livewire\FileManagement\ToetsenbakkerUploadsOverview::class)->name('file-management.testuploads');

        Route::get('/drawing_question_answers/{answer}', [tcCore\Http\Controllers\DrawingQuestionLaravelController::class, 'show'])->name('drawing-question-answer');
        Route::get('/drawing_question_answer_model/{question}', [tcCore\Http\Controllers\DrawingQuestionLaravelController::class, 'showAnswerModel'])->name('drawing-question-answer-model');

        Route::get('/co-learning/{test_take}', \tcCore\Http\Livewire\Teacher\CoLearning::class)->name('co-learning');
        Route::get('/assessment/{testTake}', \tcCore\Http\Livewire\Teacher\Assessment::class)->name('assessment');
        Route::get('/test-take/{testTake}', [\tcCore\Http\Controllers\TestTakesController::class, 'openDetail'])->name('test-take.open-detail');
        Route::get('/test-take/planned/{testTake}', \tcCore\Http\Livewire\Teacher\TestTake\Planned::class)->name('test-take.planned')->middleware('testTakeStatus:1');
        Route::get('/test-take/taking/{testTake}', \tcCore\Http\Livewire\Teacher\TestTake\Taking::class)->name('test-take.taking')->middleware('testTakeStatus:3');
        Route::get('/test-take/taken/{testTake}', \tcCore\Http\Livewire\Teacher\TestTake\Taken::class)->name('test-take.taken')->middleware('testTakeStatus:6,7,8,9');
        Route::get('/test-take/{test_take}/rtti-export-file', [\tcCore\Http\Controllers\TestTakesController::class, 'exportRttiCsvFile'])->name('test-take.rtti-export-file');
        Route::get('/test-take/{test_take}/export-grades-csv', [\tcCore\Http\Controllers\TestTakesController::class, 'exportGradesCsvFile'])->name('test-take.export-grades-csv');

        Route::get('/wordlists', \tcCore\Http\Livewire\Teacher\WordListsOverview::class)->name('wordlists');
    });

    Route::middleware(['dll', 'student'])->prefix('appapi')->name('appapi')->group(function () {
        Route::put('/test_participant/{test_participant}/hand_in', [tcCore\Http\Controllers\AppApiController::class, 'handIn'])->name('appapi-hand-in');
        Route::put('/test_participant/{test_participant}/fraud_event', [tcCore\Http\Controllers\AppApiController::class, 'fraudEvent'])->name('appapi-fraud-event');
    });

    Route::middleware(['dll', 'teacher'])->prefix('cms')->name('cms.')->group(function () {
        Route::post('/ckeditor_upload/{type}', [tcCore\Http\Controllers\CkeditorImageController::class, 'store'])->name('upload');
        Route::get('/ckeditor_upload/{filename}', [tcCore\Http\Controllers\CkeditorImageController::class, 'show'])->name('upload.get');
    });

    Route::middleware(['dll', 'accountManager'])->prefix('account-manager')->name('account-manager.')->group(function () {
        Route::get('/school-locations', \tcCore\Http\Livewire\SchoolLocationsGrid::class)->name('school-locations');
        Route::get('/schools', \tcCore\Http\Livewire\SchoolsGrid::class)->name('schools');
        Route::get('/file-management/testuploads', \tcCore\Http\Livewire\FileManagement\TestUploadsOverview::class)->name('file-management.testuploads');
    });

    Route::middleware(['dll', 'administrator'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/school-locations', \tcCore\Http\Livewire\SchoolLocationsGrid::class)->name('school-locations');
        Route::get('/schools', \tcCore\Http\Livewire\SchoolsGrid::class)->name('schools');
    });

    if (\tcCore\Http\Helpers\BaseHelper::notProduction()) {
        Route::get('/preview_password_changed_mail', [tcCore\Http\Controllers\PreviewMailController::class, 'passwordChanged'])->name('PasswordChangedMail');
        Route::get('/preview_password_changed_self_mail', [tcCore\Http\Controllers\PreviewMailController::class, 'passwordChangedSelf'])->name('PasswordChangedSelf');
    }
});
Route::middleware(['guestChoice'])->group(function () {
    Route::get('/guest-choice', tcCore\Http\Livewire\Student\GuestUserChoosingPage::class)->name('guest-choice');
    Route::get('/guest-graded-overview', tcCore\Http\Livewire\Student\GuestGradedOverview::class)->name('guest-graded-overview');
});

Route::middleware(['development'])->group(function () {
    Route::get('styleguide', \tcCore\Http\Livewire\ComponentStyleguide::class)->name('development.styleguide');
});
