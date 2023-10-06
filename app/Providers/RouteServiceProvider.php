<?php namespace tcCore\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Ramsey\Uuid\Nonstandard\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use tcCore\Address;
use tcCore\Answer;
use tcCore\Attachment;
use tcCore\Attainment;
use tcCore\BaseAttainment;
use tcCore\BaseSubject;
use tcCore\CompletionQuestion;
use tcCore\Contact;
use tcCore\Deployment;
use tcCore\DrawingQuestion;
use tcCore\EducationLevel;
use tcCore\EmailConfirmation;
use tcCore\Exceptions\RouteModelBindingNotFoundHttpException;
use tcCore\FileManagement;
use tcCore\GradingScale;
use tcCore\GroupQuestion;
use tcCore\GroupQuestionQuestion;
use tcCore\Info;
use tcCore\InfoscreenQuestion;
use tcCore\Invigilator;
use tcCore\LearningGoal;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\License;
use tcCore\MaintenanceWhitelistIp;
use tcCore\Message;
use tcCore\MultipleChoiceQuestion;
use tcCore\OnboardingWizard;
use tcCore\OpenQuestion;
use tcCore\Period;
use tcCore\Question;
use tcCore\RankingQuestion;
use tcCore\SalesOrganization;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationContact;
use tcCore\SchoolLocationIp;
use tcCore\SchoolYear;
use tcCore\Section;
use tcCore\Student;
use tcCore\Subject;
use tcCore\Tag;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\TestTakeEvent;
use tcCore\TestTakeEventType;
use tcCore\UmbrellaOrganization;
use tcCore\User;

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

        Route::model('info', 'tcCore\Info', function () {
            throw new RouteModelBindingNotFoundHttpException('Info not found');
        });

        Route::model('address', 'tcCore\Address', function () {
            throw new RouteModelBindingNotFoundHttpException('Address not found');
        });

        Route::model('answer_rating', 'tcCore\AnswerRating', function () {
            throw new RouteModelBindingNotFoundHttpException('Answer rating not found');
        });

        Route::model('answer', 'tcCore\Answer', function () {
            throw new RouteModelBindingNotFoundHttpException('Answer not found');
        });

        Route::model('attachment', 'tcCore\Attachment', function () {
            throw new RouteModelBindingNotFoundHttpException('Attachment not found');
        });

        Route::model('attainment', 'tcCore\Attainment', function () {
            throw new RouteModelBindingNotFoundHttpException('Attainment not found');
        });

        Route::model('base_subject', 'tcCore\Question', function () {
            throw new RouteModelBindingNotFoundHttpException('Base subject not found');
        });

        Route::model('completion_question_answer', 'tcCore\CompletionQuestionAnswer', function () {
            throw new RouteModelBindingNotFoundHttpException('Completion question answer not found');
        });

        Route::model('completion_question', 'tcCore\CompletionQuestion', function () {
            throw new RouteModelBindingNotFoundHttpException('Completion question not found');
        });

        Route::model('contact', 'tcCore\Contact', function () {
            throw new RouteModelBindingNotFoundHttpException('Contact not found');
        });

        Route::model('drawing_question', 'tcCore\DrawingQuestion', function () {
            throw new RouteModelBindingNotFoundHttpException('Drawing question not found');
        });

        Route::model('education_level', 'tcCore\EducationLevel', function () {
            throw new RouteModelBindingNotFoundHttpException('Education level not found');
        });

        Route::model('grading_score', 'tcCore\GradingScale', function () {
            throw new RouteModelBindingNotFoundHttpException('Grading scale not found');
        });

        Route::model('group_question', 'tcCore\GroupQuestion', function () {
            throw new RouteModelBindingNotFoundHttpException('Group question not found');
        });

        /**
         * @param id tcCore\TestQuestion::uuid
         */
        Route::bind('group_question_question', function ($id) {
            try {
                return \tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager::getInstanceWithUuid($id);
            } catch (\Exception $e) {
                throw new RouteModelBindingNotFoundHttpException('Group question question path not found');
            }
        });

        Route::model('group_question_question_id', 'tcCore\GroupQuestionQuestion', function () {
            throw new RouteModelBindingNotFoundHttpException('Group question question not found');
        });

        /**
         * Route::model('invigilator','tcCore\Invigilator', function() {
         * throw new NotFoundHttpException('Invigilator not found');
         * });
         */

        Route::model('license', 'tcCore\License', function () {
            throw new RouteModelBindingNotFoundHttpException('License not found');
        });

        Route::model('matching_question_answer', 'tcCore\MatchingQuestionAnswer', function () {
            throw new RouteModelBindingNotFoundHttpException('Matching question answer not found');
        });

        Route::model('matching_question', 'tcCore\MatchingQuestion', function () {
            throw new RouteModelBindingNotFoundHttpException('Matching question not found');
        });

        Route::model('message', 'tcCore\Message', function () {
            throw new RouteModelBindingNotFoundHttpException('Message not found');
        });

        Route::model('multiple_choice_question_answer', 'tcCore\MultipleChoiceQuestionAnswer', function () {
            throw new RouteModelBindingNotFoundHttpException('Multiple choice question answer not found');
        });

        Route::model('multiple_choice_question', 'tcCore\MultipleChoiceQuestion', function () {
            throw new RouteModelBindingNotFoundHttpException('Multiple choice question not found');
        });

        Route::model('open_question', 'tcCore\OpenQuestion', function () {
            throw new RouteModelBindingNotFoundHttpException('Open question not found');
        });

        Route::model('period', 'tcCore\Period', function () {
            throw new RouteModelBindingNotFoundHttpException('Period not found');
        });

        Route::model('question', 'tcCore\Question', function () {
            throw new RouteModelBindingNotFoundHttpException('Question not found');
        });

        Route::model('ranking_question_answer', 'tcCore\RankingQuestionAnswer', function () {
            throw new RouteModelBindingNotFoundHttpException('Ranking question answer not found');
        });

        Route::model('ranking_question', 'tcCore\RankingQuestion', function () {
            throw new RouteModelBindingNotFoundHttpException('Ranking question not found');
        });

        Route::model('role', 'tcCore\Role', function () {
            throw new RouteModelBindingNotFoundHttpException('Role not found');
        });

        Route::model('sales_organisation', 'tcCore\SalesOrganization', function () {
            throw new RouteModelBindingNotFoundHttpException('Sales organization not found');
        });

        Route::model('school_class', 'tcCore\SchoolClass', function () {
            throw new RouteModelBindingNotFoundHttpException('School class not found');
        });

        Route::model('school_location', 'tcCore\SchoolLocation', function () {
            throw new RouteModelBindingNotFoundHttpException('School location not found');
        });

        Route::model('school_location_ip', 'tcCore\SchoolLocationIp', function () {
            throw new RouteModelBindingNotFoundHttpException('School location ip not found');
        });

        Route::model('school_year', 'tcCore\SchoolYear', function () {
            throw new RouteModelBindingNotFoundHttpException('School year not found');
        });

        Route::model('school', 'tcCore\School', function () {
            throw new RouteModelBindingNotFoundHttpException('School not found');
        });

        Route::model('section', 'tcCore\Section', function () {
            throw new RouteModelBindingNotFoundHttpException('Section not found');
        });

        /**
         * Route::model('student','tcCore\Student', function() {
         * throw new NotFoundHttpException('Student not found');
         * });
         */

        Route::model('subject', 'tcCore\Subject', function () {
            throw new RouteModelBindingNotFoundHttpException('Subject not found');
        });

        Route::model('tag', 'tcCore\Tag', function () {
            throw new RouteModelBindingNotFoundHttpException('Tag not found');
        });

        Route::model('teacher', 'tcCore\Teacher', function () {
            throw new RouteModelBindingNotFoundHttpException('Teacher not found');
        });

        Route::model('test_kind', 'tcCore\TestKind', function () {
            throw new RouteModelBindingNotFoundHttpException('Test kind not found');
        });

        Route::model('test_participant', 'tcCore\TestParticipant', function () {
            throw new RouteModelBindingNotFoundHttpException('Test participant not found');
        });

        Route::model('test_question', 'tcCore\TestQuestion', function () {
            throw new RouteModelBindingNotFoundHttpException('Test question not found');
        });

        Route::model('test_take_event', 'tcCore\TestTakeEvent', function () {
            throw new RouteModelBindingNotFoundHttpException('Test take event not found');
        });

        Route::model('test_take_event_type', 'tcCore\TestTakeEventType', function () {
            throw new RouteModelBindingNotFoundHttpException('Test take event type not found');
        });

        Route::model('test_take_status', 'tcCore\TestTakeStatus', function () {
            throw new RouteModelBindingNotFoundHttpException('Test take status not found');
        });

        Route::model('test_take', 'tcCore\TestTake', function () {
            throw new RouteModelBindingNotFoundHttpException('Test take not found');
        });

        Route::model('test', 'tcCore\Test', function () {
            throw new RouteModelBindingNotFoundHttpException('Test not found');
        });

        Route::model('umbrella_organization', 'tcCore\UmbrellaOrganization', function () {
            throw new RouteModelBindingNotFoundHttpException('Umbrella organization not found');
        });

        Route::model('email_confirmation', 'tcCore\EmailConfirmation', function () {
            throw new RouteModelBindingNotFoundHttpException('Email Confirmation not found');
        });

        Route::model('deployment', 'tcCore\Deployment', function () {
            throw new RouteModelBindingNotFoundHttpException('Deployment not found');
        });

        Route::model('maintenanceWhitelistIp', 'tcCore\MaintenanceWhitelistIp', function () {
            throw new RouteModelBindingNotFoundHttpException('MaintenanceWhitelistIp not found');
        });
        Route::model('file_management', 'tcCore\FileManagement', function () {
            throw new RouteModelBindingNotFoundHttpException('FileManagement not found');
        });

        /**
         * Route::model('user_role','tcCore\UserRole', function() {
         * throw new NotFoundHttpException('User role not found');
         * });
         */

//        Route::model('user', 'tcCore\User', function () {
//            throw new NotFoundHttpException('User not found');
//        });

        //UUID Route binding

        Route::bind('info', function($item) {
            return Info::whereUuid($item)->firstOrFail();
        });

        Route::bind('school_year', function($item) {
            return SchoolYear::whereUuid($item)->firstOrFail();
        });

        Route::bind('period', function($item) {
            return Period::whereUuid($item)->firstOrFail();
        });

        Route::bind('section', function($item) {
            return Section::whereUuid($item)->firstOrFail();
        });

        Route::bind('subject', function($item) {
            return Subject::whereUuid($item)->firstOrFail();
        });

        Route::bind('school_class', function($item) {
            return SchoolClass::whereUuid($item)->firstOrFail();
        });

        Route::bind('schoolClass', function($item) {
            return SchoolClass::whereUuid($item)->firstOrFail();
        });

        Route::bind('school_location', function($item) {
            return SchoolLocation::whereUuid($item)->firstOrFail();
        });

        Route::bind('schoolLocation', function($item) {
            return SchoolLocation::whereUuid($item)->firstOrFail();
        });

        Route::bind('school_location_ip', function($item) {
            return SchoolLocationIp::whereUuid($item)->firstOrFail();
        });

        Route::bind('contact', function($item) {
            return Contact::whereUuid($item)->firstOrFail();
        });

        Route::bind('user', function($item) {
            return User::whereUuid($item)->firstOrFail();
        });

        Route::bind('address', function($item) {
            return Address::whereUuid($item)->firstOrFail();
        });

        Route::bind('answer', function($item) {
            return Answer::whereUuid($item)->firstOrFail();
        });

        Route::bind('test', function($item) {
            /**
             * Sometimes the Test UUID is not actually
             * a Test UUID, but a TestQuestion UUID that
             * should be handled as it were a 'group_question_question'
             * So now we handle both cases
             *
             * This inconsistency is also the case for 'question'
             *
             * Relates to TCP-833
             */
            $test = Test::whereUuid($item)->first();
            if ($test != null) {
                return $test;
            }

            $groupQuestionQuestionManager = \tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager::getInstanceWithUuid($item);
            return $groupQuestionQuestionManager->getQuestionLink()->test;
        });

        Route::bind('onboarding_wizard', function($item) {
            return OnboardingWizard::whereUuid($item)->firstOrFail();
        });

        Route::bind('group_question_question_id', function($item) {
            return GroupQuestionQuestion::whereUuid($item)->firstOrFail();
        });

        Route::bind('group_question_question', function($item) {
            try {
                return \tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager::getInstanceWithUuid($item);
            } catch (\Exception $e) {
                throw new NotFoundHttpException('Group question question path not found');
            }
        });

        Route::bind('test_take', function($item) {
            //return TestTake::select('test_takes.*')->whereUuid($item)->firstOrFail();
            return TestTake::whereUuid($item)->firstOrFail();
        });

        Route::bind('test_participant', function($item) {
            return TestParticipant::whereUuid($item)->firstOrFail();
        });

        Route::bind('test_take_event', function($item) {
            return TestTakeEvent::whereUuid($item)->firstOrFail();
        });

        Route::bind('test_take_event_type', function($item) {
            return TestTakeEventType::whereUuid($item)->firstOrFail();
        });

        Route::bind('education_level', function($item) {
            return EducationLevel::whereUuid($item)->firstOrFail();
        });

        Route::bind('invigilator', function($item) {
            return Invigilator::whereUuid($item)->firstOrFail();
        });

        Route::bind('student', function($item) {
            return Student::whereUuid($item)->firstOrFail();
        });

        Route::bind('open_question', function($item) {
            return OpenQuestion::whereUuid($item)->firstOrFail();
        });

        Route::bind('test_question', function($item) {
            return TestQuestion::whereUuid($item)->firstOrFail();
        });

        Route::bind('group_question', function($item) {
            return GroupQuestion::whereUuid($item)->firstOrFail();
        });

        Route::bind('drawing_question', function($item) {
            return DrawingQuestion::whereUuid($item)->firstOrFail();
        });

        Route::bind('ranking_question', function($item) {
            return RankingQuestion::whereUuid($item)->firstOrFail();
        });

        Route::bind('completion_question', function($item) {
            return CompletionQuestion::whereUuid($item)->firstOrFail();
        });

        Route::bind('info_screen_question', function($item) {
            return InfoscreenQuestion::whereUuid($item)->firstOrFail();
        });

        Route::bind('question', function($item) {
            $question = Question::findByUuid($item);
            //sometimes the Answer UUID is used as the question_id
            //so also try to get the question using the Answer
            if (!$question == null) {
                return $question;
            }

            return Answer::whereUuid($item)->firstOrFail()->question;
        });

        Route::bind('multiple_choice_question', function($item) {
            return MultipleChoiceQuestion::whereUuid($item)->firstOrFail();
        });

        Route::bind('attainment', function($item) {
            return Attainment::whereUuid($item)->firstOrFail();
        });

        Route::bind('learning_goal', function($item) {
            return LearningGoal::whereUuid($item)->firstOrFail();
        });

        Route::bind('baseAttainment', function($item) {
            return BaseAttainment::whereUuid($item)->firstOrFail();
        });

        Route::bind('attachment', function($item) {
            return Attachment::whereUuid($item)->firstOrFail();
        });

        Route::bind('teacher', function($item) {
            return Teacher::whereUuid($item)->firstOrFail();
        });

        Route::bind('sales_organization', function($item) {
            return SalesOrganization::whereUuid($item)->firstOrFail();
        });

        Route::bind('umbrella_organization', function($item) {
            return UmbrellaOrganization::whereUuid($item)->firstOrFail();
        });

        Route::bind('school', function($item) {
            return School::whereUuid($item)->firstOrFail();
        });

        Route::bind('license', function($item) {
            return License::whereUuid($item)->firstOrFail();
        });

        Route::bind('message', function($item) {
            return Message::whereUuid($item)->firstOrFail();
        });

        Route::bind('grading_scale', function($item) {
            return GradingScale::whereUuid($item)->firstOrFail();
        });

        Route::bind('base_subject', function($item) {
            return BaseSubject::whereUuid($item)->firstOrFail();
        });

        Route::bind('tag', function($item) {
            return Tag::whereUuid($item)->firstOrFail();
        });

        Route::bind('file_management', function($item) {
            return FileManagement::whereUuid($item)->firstOrFail();
        });

        Route::bind('EmailConfirmation', function($item) {
            return EmailConfirmation::whereUuid($item)->firstOrFail();
        });

        Route::bind('deployment', function($item) {
            return Deployment::whereUuid($item)->firstOrFail();
        });

        Route::bind('maintenanceWhitelistIp', function($item) {
            return MaintenanceWhitelistIp::whereUuid($item)->firstOrFail();
        });

    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiCakeRoutes();

        $this->mapWebRoutes();

        if (!$this->app->environment('production') && app()->isLocal()){
            $this->mapTestingRoutes();
        }
//        $this->mapWebRoutes();
        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
//            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }


    /**
     * Define the "testing" routes for the application.
     *
     * These routes all receive "web" middleware.
     *
     * @return void
     */
    protected function mapTestingRoutes() {
        Route::middleware([
//            'web',
//            'auth',
//            'administrator',
        ])
            ->group(base_path('routes/testing.php'));
    }

    /**
     * Define the api cake routes for the application.
     *
     * @return void
     */
    protected function mapApiCakeRoutes()
    {
        Route::namespace($this->namespace)
            ->prefix('api-c') // added to make room for the urls from direct access
            ->middleware(['cakeLaravelFilter'])
            ->group(base_path('routes/apicake.php'));
    }

}
