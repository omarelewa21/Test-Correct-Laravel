<?php namespace tcCore\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{

    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'tcCore\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Route::model('address', 'tcCore\Address', function () {
            throw new NotFoundHttpException('Address not found');
        });

        Route::model('answer_rating', 'tcCore\AnswerRating', function () {
            throw new NotFoundHttpException('Answer rating not found');
        });

        Route::model('answer', 'tcCore\Answer', function () {
            throw new NotFoundHttpException('Answer not found');
        });

        Route::model('attachment', 'tcCore\Attachment', function () {
            throw new NotFoundHttpException('Attachment not found');
        });

        Route::model('attainment', 'tcCore\Attainment', function () {
            throw new NotFoundHttpException('Attainment not found');
        });

        Route::model('base_subject', 'tcCore\Question', function () {
            throw new NotFoundHttpException('Base subject not found');
        });

        Route::model('completion_question_answer', 'tcCore\CompletionQuestionAnswer', function () {
            throw new NotFoundHttpException('Completion question answer not found');
        });

        Route::model('completion_question', 'tcCore\CompletionQuestion', function () {
            throw new NotFoundHttpException('Completion question not found');
        });

        Route::model('contact', 'tcCore\Contact', function () {
            throw new NotFoundHttpException('Contact not found');
        });

        Route::model('drawing_question', 'tcCore\DrawingQuestion', function () {
            throw new NotFoundHttpException('Drawing question not found');
        });

        Route::model('education_level', 'tcCore\EducationLevel', function () {
            throw new NotFoundHttpException('Education level not found');
        });

        Route::model('grading_score', 'tcCore\GradingScale', function () {
            throw new NotFoundHttpException('Grading scale not found');
        });

        Route::model('group_question', 'tcCore\GroupQuestion', function () {
            throw new NotFoundHttpException('Group question not found');
        });

        Route::bind('group_question_question', function ($id) {
            try {
                return \tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager::getInstance($id);
            } catch (\Exception $e) {
                throw new NotFoundHttpException('Group question question path not found');
            }
        });

        Route::model('group_question_question_id', 'tcCore\GroupQuestionQuestion', function () {
            throw new NotFoundHttpException('Group question question not found');
        });

        /**
         * Route::model('invigilator','tcCore\Invigilator', function() {
         * throw new NotFoundHttpException('Invigilator not found');
         * });
         */

        Route::model('license', 'tcCore\License', function () {
            throw new NotFoundHttpException('License not found');
        });

        Route::model('matching_question_answer', 'tcCore\MatchingQuestionAnswer', function () {
            throw new NotFoundHttpException('Matching question answer not found');
        });

        Route::model('matching_question', 'tcCore\MatchingQuestion', function () {
            throw new NotFoundHttpException('Matching question not found');
        });

        Route::model('message', 'tcCore\Message', function () {
            throw new NotFoundHttpException('Message not found');
        });

        Route::model('multiple_choice_question_answer', 'tcCore\MultipleChoiceQuestionAnswer', function () {
            throw new NotFoundHttpException('Multiple choice question answer not found');
        });

        Route::model('multiple_choice_question', 'tcCore\MultipleChoiceQuestion', function () {
            throw new NotFoundHttpException('Multiple choice question not found');
        });

        Route::model('open_question', 'tcCore\OpenQuestion', function () {
            throw new NotFoundHttpException('Open question not found');
        });

        Route::model('period', 'tcCore\Period', function () {
            throw new NotFoundHttpException('Period not found');
        });

        Route::model('question', 'tcCore\Question', function () {
            throw new NotFoundHttpException('Question not found');
        });

        Route::model('ranking_question_answer', 'tcCore\RankingQuestionAnswer', function () {
            throw new NotFoundHttpException('Ranking question answer not found');
        });

        Route::model('ranking_question', 'tcCore\RankingQuestion', function () {
            throw new NotFoundHttpException('Ranking question not found');
        });

        Route::model('role', 'tcCore\Role', function () {
            throw new NotFoundHttpException('Role not found');
        });

        Route::model('sales_organisation', 'tcCore\SalesOrganization', function () {
            throw new NotFoundHttpException('Sales organization not found');
        });

        Route::model('school_class', 'tcCore\SchoolClass', function () {
            throw new NotFoundHttpException('School class not found');
        });

        Route::model('school_location', 'tcCore\SchoolLocation', function () {
            throw new NotFoundHttpException('School location not found');
        });

        Route::model('school_location_ip', 'tcCore\SchoolLocationIp', function () {
            throw new NotFoundHttpException('School location ip not found');
        });

        Route::model('school_year', 'tcCore\SchoolYear', function () {
            throw new NotFoundHttpException('School year not found');
        });

        Route::model('school', 'tcCore\School', function () {
            throw new NotFoundHttpException('School not found');
        });

        Route::model('section', 'tcCore\Section', function () {
            throw new NotFoundHttpException('Section not found');
        });

        /**
         * Route::model('student','tcCore\Student', function() {
         * throw new NotFoundHttpException('Student not found');
         * });
         */

        Route::model('subject', 'tcCore\Subject', function () {
            throw new NotFoundHttpException('Subject not found');
        });

        Route::model('tag', 'tcCore\Tag', function () {
            throw new NotFoundHttpException('Tag not found');
        });

        Route::model('teacher', 'tcCore\Teacher', function () {
            throw new NotFoundHttpException('Teacher not found');
        });

        Route::model('test_kind', 'tcCore\TestKind', function () {
            throw new NotFoundHttpException('Test kind not found');
        });

        Route::model('test_participant', 'tcCore\TestParticipant', function () {
            throw new NotFoundHttpException('Test participant not found');
        });

        Route::model('test_question', 'tcCore\TestQuestion', function () {
            throw new NotFoundHttpException('Test question not found');
        });

        Route::model('test_take_event', 'tcCore\TestTakeEvent', function () {
            throw new NotFoundHttpException('Test take event not found');
        });

        Route::model('test_take_event_type', 'tcCore\TestTakeEventType', function () {
            throw new NotFoundHttpException('Test take event type not found');
        });

        Route::model('test_take_status', 'tcCore\TestTakeStatus', function () {
            throw new NotFoundHttpException('Test take status not found');
        });

        Route::model('test_take', 'tcCore\TestTake', function () {
            throw new NotFoundHttpException('Test take not found');
        });

        Route::model('test', 'tcCore\Test', function () {
            throw new NotFoundHttpException('Test not found');
        });

        Route::model('umbrella_organization', 'tcCore\UmbrellaOrganization', function () {
            throw new NotFoundHttpException('Umbrella organization not found');
        });

        /**
         * Route::model('user_role','tcCore\UserRole', function() {
         * throw new NotFoundHttpException('User role not found');
         * });
         */

        Route::model('user', 'tcCore\User', function () {
            throw new NotFoundHttpException('User not found');
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->loadRoutesFrom(app_path('Http/routes.php'));
    }

}
