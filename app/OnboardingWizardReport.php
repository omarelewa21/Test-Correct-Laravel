<?php

namespace tcCore;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OnboardingWizardReport extends Model
{
    protected $guarded = [];

    public static function updateForUser(User $user)
    {
        $wizardData = self::getStepsCollection($user);

        self::updateOrCreate([
            'user_id' => $user->getKey(),
        ], [
            'user_email'                                  => $user->username,
            'user_name_first'                             => $user->name_first,
            'user_name_suffix'                            => $user->name_suffix,
            'user_name'                                   => $user->name,
            'first_test_created_date'                     => optional(Test::where('author_id', $user->getKey())->where('demo', 0)->where('system_test_id', null)->orderBy('created_at', 'asc')->first())->created_at,
            'last_test_created_date'                      => optional(Test::where('author_id', $user->getKey())->where('demo', 0)->where('system_test_id', null)->orderBy('created_at', 'desc')->first())->created_at,
            'user_created_at'                             => $user->created_at,
            'user_last_login'                             => $user->last_login,
            'school_location_name'                        => $user->schoolLocation->name,
            'school_location_customer_code'               => $user->schoolLocation->customer_code,
            'test_items_created_amount'                   => $user->questionAuthors()->count(),
            'tests_created_amount'                        => $user->tests()->where('demo', 0)->count(),
            'first_test_planned_date'                     => optional($user->testTakes()->where('demo', 0)->orderBy('created_at', 'desc')->first())->time_start,
            'last_test_planned_date'                      => optional($user->testTakes()->where('demo', 0)->orderBy('created_at', 'asc')->first())->time_start,
            'first_test_taken_date'                       => optional($user->testTakes()->where('demo', 0)->orderBy('created_at', 'asc')->first())->created_at,
            'last_test_taken_date'                        => optional($user->testTakes()->where('demo', 0)->orderBy('created_at', 'desc')->first())->created_at,
            'tests_taken_amount'                          => $user->testTakes()->where('demo', 0)->where('test_take_status_id', '>', 5)->count(),
            // deze kunnen we niet want kan niet via updated en niet via created_at
            'first_test_discussed_date'                   => '',
            'last_test_discussed_date'                    => '',
            'tests_discussed_amount'                      => $user->testTakes()->where('demo', 0)->where('is_discussed', 1)->count(),
            // wat is checked
            'first_test_checked_date'                     => '',
            'last_test_checked_date'                      => '',
            'tests_checked_amount'                        => '',
            'first_test_rated_date'                       => optional(AnswerRating::whereIn('test_take_id', $user->testTakes->pluck('id'))->orderBy('created_at', 'asc')->first())->created_at,
            'last_test_rated_date'                        => optional(AnswerRating::whereIn('test_take_id', $user->testTakes->pluck('id'))->orderBy('created_at', 'desc')->first())->created_at,
            'tests_rated_amount'                          => $user->testTakes()->where('demo', 0)->where('test_take_status_id', 9)->count(),
            'finished_demo_tour'                          => $wizardData->progress === 100 ? 'Ja' : 'Nee',
            'finished_demo_steps_percentage'              => self::stepsPercentage($user),
            'finished_demo_substeps_percentage'           => self::subStepsPercentage($user),
            'current_demo_tour_step'                      => self::getActiveStep($user),
            'current_demo_tour_step_since_date'           => optional($user->onboardingWizardUserSteps()->orderByDesc('created_at')->first())->created_at,
            'current_demo_tour_step_since_hours'          => optional(optional($user->onboardingWizardUserSteps()->orderByDesc('created_at')->first())->created_at)->diffForHumans(),
            'average_time_finished_demo_tour_steps_hours' => self::getOnboardingWizardMeanTimeCompletingStep($user),
            'user_sections'                               => sprintf(',%s,', $user->sections()->orderBy('name')
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
            ),
            'user_login_amount'                           => $user->loginLogs()->count(),
        ]);
    }

    public static function subStepsPercentage(User $user)
    {
        $result = DB::select(
            DB::raw(
                sprintf('select 
  (SELECT count(*)
   FROM onboarding_wizard_user_steps
   WHERE `onboarding_wizard_step_id` IN
       (SELECT id
        FROM onboarding_wizard_steps
        WHERE parent_id IS NOT NULL)
     AND user_id = %d) /
  (SELECT count(*)
   FROM onboarding_wizard_steps
   WHERE parent_id IS NOT NULL) * 100 AS percentage', $user->getKey())));
        return $result[0]->percentage;
    }

    public static function stepsPercentage(User $user)
    {
        $result = DB::select(
            DB::raw(
                sprintf('select 
  (SELECT count(*)
   FROM onboarding_wizard_user_steps
   WHERE `onboarding_wizard_step_id` IN
       (SELECT id
        FROM onboarding_wizard_steps
        WHERE parent_id IS  NULL)
     AND user_id = %d) /
  (SELECT count(*)
   FROM onboarding_wizard_steps
   WHERE parent_id IS  NULL) * 100 AS percentage', $user->getKey()
                )
            )
        );
        return $result[0]->percentage;
    }

    public static function getActiveStep(User $user)
    {
        $result = DB::select(
            DB::raw(
                sprintf('
               SELECT t2.title AS title
FROM onboarding_wizard_steps AS t1
LEFT JOIN onboarding_wizard_steps AS t2 ON (t1.parent_id = t2.id)
LEFT JOIN
  (SELECT *
   FROM onboarding_wizard_user_steps
   WHERE user_id=%d) AS t3 ON (t1.id = t3.onboarding_wizard_step_id)
WHERE t1.parent_id IS NOT NULL
  AND t3.user_id IS NULL
ORDER BY t2.displayorder,
         t1.displayorder LIMIT 1',
                    $user->getKey()
                )
            )
        );
        if(isset($result) && isset($result[0])) {
            return $result[0]->title;
        }
        return 'no steps yes';
    }


    public static function updateForAllTeachers()
    {
        User::whereIn('id', Teacher::pluck('user_id'))->each(function ($teacher) {
            if($teacher->isA('teacher')){
                \tcCore\OnboardingWizardReport::updateForUser($teacher);
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
    //
}
