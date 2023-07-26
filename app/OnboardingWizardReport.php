<?php

namespace tcCore;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Helpers\ReportHelper;
use tcCore\Jobs\UpdateOnboardingWizardReportRecord;
use tcCore\Scopes\ArchivedScope;

set_time_limit(300);

class OnboardingWizardReport extends Model
{
    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public static function updateForUser(User $user)
    {
        if(!$user->schoolLocation || $user->schoolLocation->keep_out_of_school_location_report){
            return;
        }

        $helper = new ReportHelper($user);

        $wizardData = self::getStepsCollection($user);

        $updated_data_array = [
            'user_email'                                  => $user->username,
            'user_name_first'                             => $user->name_first,
            'user_name_suffix'                            => $user->name_suffix,
            'user_name'                                   => $user->name,
            'first_test_created_date'                     => self::getFirstTestCreatedDate($user),
            'last_test_created_date'                      => self::getLastTestCreatedDate($user),
            'user_created_at'                             => $user->created_at,
            'user_last_login'                             => $user->last_login,
            'school_location_name'                        => ($user->schoolLocation) ? $user->schoolLocation->name : sprintf('SCHOOLLOCATIE VERWIJDERD: %s', $user->schoolLocation()->withTrashed()->first()->name),
            'school_location_customer_code'               => ($user->schoolLocation) ? $user->schoolLocation->customer_code : sprintf('SCHOOLLOCATIE VERWIJDERD: %s', $user->schoolLocation()->withTrashed()->first()->customer_code),
            'test_items_created_amount'                   => $helper->nrAddedQuestionItems(0),//self::getTestItemsCreatedAmount($user),
            'tests_created_amount'                        => self::getTestsCreatedAmount($user),
            'first_test_planned_date'                     => self::getFirstTestPlannedDate($user),
            'last_test_planned_date'                      => self::getLastTestPlannedDate($user),
            'first_test_taken_date'                       => self::getFirstTestTakenDate($user),
            'last_test_taken_date'                        => self::getLastTestTakenDate($user),
            'tests_taken_amount'                          => self::getTestsTakenAmount($user),
            // deze kunnen we niet want kan niet via updated en niet via created_at
            'first_test_discussed_date'                   => self::getFirstTestDiscussedDate($user),
            'last_test_discussed_date'                    => self::getLastTestDiscussedDate($user),
            'tests_discussed_amount'                      => $user->testTakes()->where('demo', 0)->where('is_discussed', 1)->count(),
            // wat is checked
            'first_test_checked_date'                     => self::getFirstTestCheckedDate($user),
            'last_test_checked_date'                      => self::lastTestCheckedDate($user),
            'tests_checked_amount'                        => self::getTestsCheckedAmount($user),
            'first_test_rated_date'                       => self::getFirstTestRatedDate($user),
            'last_test_rated_date'                        => self::getLastTestRatedDate($user),
            'tests_rated_amount'                          => $user->testTakes()->where('demo', 0)->where('test_take_status_id', 9)->count(),
//            'finished_demo_tour'                          => $wizardData->progress == 100 ? 'Ja' : 'Nee',
//            'finished_demo_steps_percentage'              => self::stepsPercentage($user),
//            'finished_demo_substeps_percentage'           => self::subStepsPercentage($user),
//            'current_demo_tour_step'                      => self::getActiveStep($user),
//            'current_demo_tour_step_since_date'           => optional($user->onboardingWizardUserSteps()->orderByDesc('created_at')->first())->created_at,
//            'current_demo_tour_step_since_hours'          => optional(optional($user->onboardingWizardUserSteps()->orderByDesc('created_at')->first())->created_at)->diffForHumans(),
//            'average_time_finished_demo_tour_steps_hours' => self::getOnboardingWizardMeanTimeCompletingStep($user),
            'user_sections'                               => self::getUserSections($user),
            'user_login_amount'                           => $user->loginLogs()->count(),
            'last_updated_from_TC'                        => Carbon::now(),
            'invited_by'                                  => self::invitedBy($user),
            'invited_users_amount'                        => self::invitedUsersAmount($user),
            'invited_users'                               => self::invitedUsers($user),
            'account_verified'                            => $user->account_verified,
            'nr_uploaded_test_files_7'                    => $helper->nrUploadedTestFiles(7),
            'nr_uploaded_test_files_30'                   => $helper->nrUploadedTestFiles(30),
            'nr_uploaded_test_files_60'                   => $helper->nrUploadedTestFiles(60),
            'nr_uploaded_test_files_90'                   => $helper->nrUploadedTestFiles(90),
            'nr_uploaded_test_files_365'                  => $helper->nrUploadedTestFiles(365),
            'nr_uploaded_test_files_total'                => $helper->nrUploadedTestFiles(0),
            'nr_added_question_items_7'                   => $helper->nrAddedQuestionItems(7),
            'nr_added_question_items_30'                  => $helper->nrAddedQuestionItems(30),
            'nr_added_question_items_60'                  => $helper->nrAddedQuestionItems(60),
            'nr_added_question_items_90'                  => $helper->nrAddedQuestionItems(90),
            'nr_added_question_items_365'                 => $helper->nrAddedQuestionItems(365),
            'nr_added_question_items_total'               => $helper->nrAddedQuestionItems(0),
            'nr_uploaded_classes_7'                       => $helper->nrUploadedClassFiles(7),
            'nr_uploaded_classes_30'                      => $helper->nrUploadedClassFiles(30),
            'nr_uploaded_classes_60'                      => $helper->nrUploadedClassFiles(60),
            'nr_uploaded_classes_90'                      => $helper->nrUploadedClassFiles(90),
            'nr_uploaded_classes_365'                     => $helper->nrUploadedClassFiles(365),
            'nr_uploaded_classes_total'                   => $helper->nrUploadedClassFiles(0),
            'nr_tests_taken_7'                            => $helper->nrTestsTaken(7), // 3.a.1
            'nr_tests_taken_30'                           => $helper->nrTestsTaken(30), // 3.a.1
            'nr_tests_taken_60'                           => $helper->nrTestsTaken(60), // 3.a.1
            'nr_tests_taken_90'                           => $helper->nrTestsTaken(90), // 3.a.1
            'nr_tests_taken_365'                          => $helper->nrTestsTaken(365), // 3.a.1
            'nr_test_taken_total'                         => self::getTestsTakenAmount($user), // 3.a.2
//            'nr_tests_checked_7'                          => $helper->nrTestsChecked(7), // 3.a.1
//            'nr_tests_checked_30'                         => $helper->nrTestsChecked(30), // 3.a.1
//            'nr_tests_checked_60'                         => $helper->nrTestsChecked(60), // 3.a.1
//            'nr_tests_checked_90'                         => $helper->nrTestsChecked(90), // 3.a.1
//            'nr_tests_checked_365'                        => $helper->nrTestsChecked(365), // 3.a.1
//            'nr_tests_checked_total'                      => $helper->nrTestsChecked(0), // 3.a.2
            'nr_tests_rated_7'                            => $helper->nrTestsRated(7), // 3.a.1
            'nr_tests_rated_30'                           => $helper->nrTestsRated(30), // 3.a.1
            'nr_tests_rated_60'                           => $helper->nrTestsRated(60), // 3.a.1
            'nr_tests_rated_90'                           => $helper->nrTestsRated(90), // 3.a.1
            'nr_tests_rated_365'                          => $helper->nrTestsRated(365), // 3.a.1
            'nr_tests_rated_total'                        => $helper->nrTestsRated(0), // 3.a.2
            'nr_colearning_sessions_7'                    => $helper->nrColearningSessions(7), // 3.a.1
            'nr_colearning_sessions_30'                   => $helper->nrColearningSessions(30), // 3.a.1
            'nr_colearning_sessions_60'                   => $helper->nrColearningSessions(60), // 3.a.1
            'nr_colearning_sessions_90'                   => $helper->nrColearningSessions(90), // 3.a.1
            'nr_colearning_sessions_365'                  => $helper->nrColearningSessions(365), // 3.a.1
            'nr_colearning_sessions_total'                => $helper->nrColearningSessions(0), // 3.a.2
            'accepted_general_terms'                      => $helper->dateGeneralTermsAccepted(), // 3.a.2
            'trial_period_end'                            => $helper->dateTrialPeriodEnds(), // 3.a.2
        ];

        self::updateOrCreate([
            'user_id' => $user->getKey(),
        ], $updated_data_array);
    }

    public static function subStepsPercentage(User $user)
    {
        $last_updated_wizard_id = OnboardingWizardUserState::where('user_id', $user->getKey())->orderBy('updated_at', 'desc')->first()->onboarding_wizard_id;
        if ($last_updated_wizard_id === null) {
            return 'no wizard was attached to this user';
        }

        $result = DB::select(
            DB::raw(
                sprintf("SELECT
  (SELECT count(distinct(onboarding_wizard_user_steps.`onboarding_wizard_step_id`))
   FROM onboarding_wizard_user_steps
   INNER JOIN onboarding_wizard_steps ON (onboarding_wizard_user_steps.`onboarding_wizard_step_id` = onboarding_wizard_steps.id)
   WHERE user_id=%d
     AND parent_id IS NOT NULL
     AND onboarding_wizard_id = '%s')/
  (SELECT count(*)
   FROM onboarding_wizard_steps
   WHERE parent_id IS NOT NULL
     AND onboarding_wizard_id = '%s') * 100 AS percentage",
                    $user->getKey(),
                    $last_updated_wizard_id,
                    $last_updated_wizard_id
                )));
        return $result[0]->percentage;
    }

    public static function stepsPercentage(User $user)
    {
        $last_updated_wizard_id = OnboardingWizardUserState::where('user_id', $user->getKey())->orderBy('updated_at', 'desc')->first()->onboarding_wizard_id;
        if ($last_updated_wizard_id === null) {
            return 'no wizard was attached to this user';
        }

        $result = DB::select(
            DB::raw(
                sprintf("SELECT
  (SELECT count(*)
   FROM onboarding_wizard_user_steps
   INNER JOIN onboarding_wizard_steps ON (onboarding_wizard_user_steps.`onboarding_wizard_step_id` = onboarding_wizard_steps.id)
   WHERE user_id=%d
     AND parent_id IS NULL
     AND onboarding_wizard_id = '%s')/
  (SELECT count(*)
   FROM onboarding_wizard_steps
   WHERE parent_id IS NULL
     AND onboarding_wizard_id = '%s') * 100 AS percentage",
                    $user->getKey(),
                    $last_updated_wizard_id,
                    $last_updated_wizard_id
                )
            )
        );
        return $result[0]->percentage;
    }

    public static function getActiveStep(User $user)
    {
        $last_updated_wizard_id = OnboardingWizardUserState::where('user_id', $user->getKey())->orderBy('updated_at', 'desc')->first()->onboarding_wizard_id;
        if ($last_updated_wizard_id === null) {
            return 'no wizard was attached to this user';
        }

        $result = DB::select(
            DB::raw(
                sprintf("
                   SELECT t2.title AS title
FROM onboarding_wizard_steps AS t1
LEFT JOIN onboarding_wizard_steps AS t2 ON (t1.parent_id = t2.id)
LEFT JOIN
  (SELECT onboarding_wizard_user_steps.*
   FROM onboarding_wizard_user_steps

   INNER JOIN onboarding_wizard_steps ON (onboarding_wizard_user_steps.`onboarding_wizard_step_id` = onboarding_wizard_steps.id)

   WHERE user_id=%d
   AND onboarding_wizard_id = '%s'
   ) AS t3 ON (t1.id = t3.onboarding_wizard_step_id)
WHERE t1.parent_id IS NOT NULL
  AND t3.user_id IS NULL
  AND t1.onboarding_wizard_id = '%s'
  AND t2.onboarding_wizard_id ='%s'
ORDER BY t2.displayorder,
         t1.displayorder LIMIT 1
               ",
                    $user->getKey(),
                    $last_updated_wizard_id,
                    $last_updated_wizard_id,
                    $last_updated_wizard_id
                )
            )
        );
        if (isset($result) && isset($result[0])) {
            $str = $result[0]->title;
            return substr($str, 0, strpos($str, '<'));
        }
        return 'no steps yes';
    }

    public static function updateForAllTeachers($shouldTruncate = true)
    {

        if($shouldTruncate) {
            OnboardingWizardReport::truncate();
        }

        User::whereIn('id', Teacher::pluck('user_id')->unique())
            ->where('demo', 0)
            ->where('username', 'not like', '%@teachandlearncompany.com')
            ->where('username', 'not like', '%@test-correct.nl')
            ->each(function ($teacher) {
                if ($teacher->isA('teacher')) {
                    dispatch((new UpdateOnboardingWizardReportRecord($teacher)));
//                    \tcCore\OnboardingWizardReport::updateForUser($teacher);
                };
            });
    }

    private static function getOnboardingWizardMeanTimeCompletingStep(User $user)
    {
        $startTime = optional($user->onboardingWizardUserSteps()->orderBy('created_at')->first())->created_at;
        $endTime = optional($user->onboardingWizardUserSteps()->orderByDesc('created_at')->first())->created_at;
        $countSteps = $user->onboardingWizardUserSteps()->count();

        if ($startTime === $endTime) {
            return 'not enough data';
        }
        try {
            $value = CarbonInterval::seconds(($mean = $endTime->diffInSeconds($startTime) / $countSteps))->cascade()->forHumans();
        } catch (\Exception $e) {
            $value = 'not a valid value';
        }

        return $value;
    }

    public static function getStepsCollection(User $user)
    {
        $steps = $user->getOnboardingWizardSteps();

        $sub_steps = $steps->map(function ($step) {
            return $step->sub;
        })->flatten(1);

        $count_sub_steps = $sub_steps->count();
        $count_sub_steps_done = $sub_steps->filter(function ($substep) {
            return $substep->done;
        })->count();

        return (object)[
            'steps'                => $steps,
            'count_main_steps'     => count($steps),
            'count_sub_steps'      => $count_sub_steps,
            'count_sub_steps_done' => $count_sub_steps_done,
            'progress'             => floor($count_sub_steps_done / $count_sub_steps * 100),
            'show'                 => $user->onboardingWizardUserState->show ?? 0,
            'active_step'          => $user->onboardingWizardUserState->active_step ?? 0,
        ];
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getFirstTestDiscussedDate(User $user)
    {
        if (($testTakeIdsForUser = $user->testTakes()->withoutGlobalScope(ArchivedScope::class)->where('demo', 0)->pluck('id')) === []) {
            return 'no results';
        }

        return optional(
            AnswerRating::where('type', 'student')
                ->whereIn('test_take_id', $testTakeIdsForUser)
                ->orderBy('created_at', 'asc')->first()
        )->created_at;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getLastTestDiscussedDate(User $user)
    {
        if (($testTakeIdsForUser = $user->testTakes()->withoutGlobalScope(ArchivedScope::class)->where('demo', 0)->pluck('id')) === []) {
            return 'no results';
        }

        return optional(
            AnswerRating::where('type', 'student')
                ->whereIn('test_take_id', $testTakeIdsForUser)
                ->orderBy('created_at', 'desc')
                ->first()
        )->created_at;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getFirstTestCheckedDate(User $user)
    {
        if (($testTakeIdsForUser = $user->testTakes()->withoutGlobalScope(ArchivedScope::class)->where('demo', 0)->pluck('id')) === []) {
            return 'no results';
        }

        return optional(
            AnswerRating::where('type', 'teacher')
                ->whereIn('test_take_id', $testTakeIdsForUser)
                ->orderBy('created_at', 'asc')
                ->first()
        )->created_at;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function lastTestCheckedDate(User $user)
    {
        return optional(
            AnswerRating::where('type', 'teacher')
                ->whereIn('test_take_id', $user->testTakes()->withoutGlobalScope(ArchivedScope::class)->where('demo', 0)->select('id')
                )->orderBy('created_at', 'desc')
                ->first()
        )->created_at;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getTestsCheckedAmount(User $user)
    {
        if (($testTakeIdsForUser = $user->testTakes()->withoutGlobalScope(ArchivedScope::class)->where('demo', 0)->groupBy('id')->pluck('id')) === []) {
            return 0;
        }

        if (($testTakesWithRatings = AnswerRating::where('type', 'teacher')
                ->whereIn('test_take_id', $testTakeIdsForUser)
                ->groupBy('test_take_id')
                ->orderBy('created_at', 'desc')->pluck('test_take_id')) === []) {
            return 0;
        }

        return TestTake::whereIn('id', $testTakesWithRatings)->count();
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getFirstTestRatedDate(User $user)
    {
        // via testPartcipant rating collumn where test_take_status_id = 9
//        return optional(AnswerRating::whereIn('test_take_id', $user->testTakes()->where('demo', 0)->pluck('id'))->orderBy('created_at', 'asc')->first())->created_at;

        return optional(
            TestParticipant::whereIn('test_take_id',
                ($user->testTakes()->where('demo', 0)->where('test_take_status_id', 9)->select('id'))
            )->where(function ($query) {
                return $query->orWhereNotNull('rating')->orWhereNotNull('retake_rating');
            })->orderBy('updated_at', 'asc')->first()
        )->updated_at;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getLastTestRatedDate(User $user)
    {
//        return optional(AnswerRating::whereIn('test_take_id', $user->testTakes()->where('demo', 0)->pluck('id'))->orderBy('created_at', 'desc')->first())->created_at;
        return optional(
            TestParticipant::whereIn('test_take_id',
                ($user->testTakes()->where('demo', 0)->where('test_take_status_id', 9)->select('id'))
            )->where(function ($query) {
                return $query->orWhereNotNull('rating')->orWhereNotNull('retake_rating');
            })->orderBy('updated_at', 'desc')->first()
        )->updated_at;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getFirstTestCreatedDate(User $user)
    {
        return optional(Test::where('author_id', $user->getKey())->where('demo', 0)->where('system_test_id', null)->orderBy('created_at', 'asc')->first())->created_at;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getLastTestCreatedDate(User $user)
    {
        return optional(Test::where('author_id', $user->getKey())->where('demo', 0)->where('system_test_id', null)->orderBy('created_at', 'desc')->first())->created_at;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getFirstTestPlannedDate(User $user)
    {
        return optional($user->testTakes()->where('demo', 0)->orderBy('test_takes.time_start', 'asc')->first())->time_start;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getLastTestPlannedDate(User $user)
    {
        return optional($user->testTakes()->where('demo', 0)->orderBy('test_takes.time_start', 'desc')->first())->time_start;
    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getFirstTestTakenDate(User $user)
    {
        // return optional(TestParticipant::whereIn('test_take_id', $user->testTakes()->where('demo', 0)->pluck('id'))->orderBy('created_at', 'asc')->first())->created_at;
        return optional($user->testTakes()->where('demo', 0)->where('test_take_status_id', '>=', 6)->orderBy('time_start', 'asc')->first())->time_start;

    }

    /**
     * @param User $user
     * @return mixed
     */
    private static function getLastTestTakenDate(User $user)
    {
        //return optional(TestParticipant::whereIn('test_take_id', $user->testTakes()->where('demo', 0)->pluck('id'))->orderBy('created_at', 'desc')->first())->created_at;
        return optional($user->testTakes()->where('demo', 0)->where('test_take_status_id', '>=', 6)->orderBy('time_start', 'desc')->first())->time_start;
    }

    /**
     * @param User $user
     * @return int
     */
    public static function getTestsTakenAmount(User $user): int
    {
        return $user->testTakes()->where('demo', 0)->where('test_take_status_id', '>', 5)->count();
    }

    private static function getUserSections(User $user)
    {
        return sprintf(',%s,', $user->sections()->orderBy('name')
            ->pluck('name')
            ->map(function ($item) {
                return
                    str_replace(',', '_',
                        ucfirst(
                            strtolower(
                                trim($item)
                            )
                        )
                    );
            })->implode(',')
        );
    }

    public static function invitedBy(User $user)
    {
        return optional($user->invitedBy)->username;
    }

    public static function invitedUsersAmount(User $user)
    {
        return User::where('invited_by', $user->id)->count();
    }

    public static function invitedUsers(User $user)
    {
        return sprintf(',%s,', User::where('invited_by', $user->id)
            ->pluck('username')
            ->implode(',')
        );
    }

    public static function getTestsCreatedAmount(User $user)
    {
        return $user->tests()->where('is_system_test', 0)->where('demo', 0)->count();
    }

    public static function getTestItemsCreatedAmount(User $user)
    {
        return Question::whereIn('id', $user->questionAuthors()->select('question_id'))->where('type', '<>', 'GroupQuestion')->count();
    }
}
