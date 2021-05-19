<?php

namespace tcCore;

use Carbon\Carbon;
use tcCore\SchoolLocation;
use Illuminate\Database\Eloquent\Model;


set_time_limit(300);

class SchoolLocationReport extends Model
{

    protected $guarded = [];

    public static function updateAllLocationStats()
    {

        SchoolLocation::all()->each(function(SchoolLocation $l){
            self::updateLocationStats($l);
        });
    }

    public static function updateLocationStats(SchoolLocation $location)
    {

        return self::updateOrCreate([
                    'school_location_id' => $location->getKey(),
                        ], [
                    'school_location_name' => $location->name,
                    'nr_licenses' => $location->count_licenses,
                    'nr_activated_licenses' => self::getActiveLicenses($location),
                    'nr_browsealoud_licenses' => $location->count_text2speech,
                    'nr_approved_test_files_7' => self::nrApprovedTestFiles($location, 7),
                    'nr_approved_test_files_30' => self::nrApprovedTestFiles($location, 30),
                    'nr_approved_test_files_60' => self::nrApprovedTestFiles($location, 60),
                    'nr_approved_test_files_90' => self::nrApprovedTestFiles($location, 90),
                    'total_approved_test_files' => self::nrApprovedTestFiles($location, 0), // 2.a.2
                    'nr_added_question_items_7' => self::nrAddedQuestionItems($location, 7), // 2.a.3
                    'nr_added_question_items_30' => self::nrAddedQuestionItems($location, 30), // 2.a.3
                    'nr_added_question_items_60' => self::nrAddedQuestionItems($location, 60), // 2.a.3
                    'nr_added_question_items_90' => self::nrAddedQuestionItems($location, 90), // 2.a.3
                    'total_added_question_items_files' => self::nrAddedQuestionItems($location, 0), // 2.a.4
                    'nr_approved_classes_7' => self::nrApprovedClasses($location, 7), // 3.a.1
                    'nr_approved_classes_30' => self::nrApprovedClasses($location, 30), // 3.a.1
                    'nr_approved_classes_60' => self::nrApprovedClasses($location, 60), // 3.a.1
                    'nr_approved_classes_90' => self::nrApprovedClasses($location, 90), // 3.a.1
                    'total_approved_classes' => self::nrApprovedClasses($location, 0), // 3.a.2
                    'nr_tests_taken_7' => self::nrTestsTaken($location, 7), // 3.a.1
                    'nr_tests_taken_30' => self::nrTestsTaken($location, 30), // 3.a.1
                    'nr_tests_taken_60' => self::nrTestsTaken($location, 60), // 3.a.1
                    'nr_tests_taken_90' => self::nrTestsTaken($location, 90), // 3.a.1
                    'total_tests_taken' => self::nrTestsTaken($location, 0), // 3.a.2
                    'nr_tests_checked_7' => self::nrTestsChecked($location, 7), // 3.a.1
                    'nr_tests_checked_30' => self::nrTestsChecked($location, 30), // 3.a.1
                    'nr_tests_checked_60' => self::nrTestsChecked($location, 60), // 3.a.1
                    'nr_tests_checked_90' => self::nrTestsChecked($location, 90), // 3.a.1
                    'total_tests_checked' => self::nrTestsChecked($location, 0), // 3.a.2
                    'nr_tests_rated_7' => self::nrTestsRated($location, 7), // 3.a.1
                    'nr_tests_rated_30' => self::nrTestsRated($location, 30), // 3.a.1
                    'nr_tests_rated_60' => self::nrTestsRated($location, 60), // 3.a.1
                    'nr_tests_rated_90' => self::nrTestsRated($location, 90), // 3.a.1
                    'total_tests_rated' => self::nrTestsRated($location, 0), // 3.a.2
                    'nr_colearning_sessions_7' => self::nrColearningSessions($location, 7), // 3.a.1
                    'nr_colearning_sessions_30' => self::nrColearningSessions($location, 30), // 3.a.1
                    'nr_colearning_sessions_60' => self::nrColearningSessions($location, 60), // 3.a.1
                    'nr_colearning_sessions_90' => self::nrColearningSessions($location, 90), // 3.a.1
                    'total_colearning_sessions' => self::nrColearningSessions($location, 0), // 3.a.2
                    'in_browser_tests_allowed' => (int) $location->allow_inbrowser_testing, // 3.a.2
                    'nr_active_teachers' => $location->count_active_teachers, // 3.a.2
        ]);
        
             
    }

    public static function getActiveLicenses($location)
    {
        $date = Carbon::now();
        return $location->licenses()
            ->where('start', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('end')
                    ->orWhere('end', '>=', $date->format('Y-m-d'));
            })
            ->sum('amount');
    }

    public static function nrApprovedTestFiles($location, $days)
    {

        $builder = Test::leftJoin('users', function($join) {
            $join->on('tests.author_id', '=', 'users.id');
        })->where('users.school_location_id', $location->getKey())
            ->where('tests.published', 1);

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);


            $builder->whereBetween('tests.created_at', [$start_date, $end_date]);
        }

        return $builder->count();
    }

    public static function nrAddedQuestionItems($location, $days)
    {

        $builder = QuestionAuthor::leftJoin('users', function($join) {
            $join->on('question_authors.user_id', '=', 'users.id');
        })->where('users.school_location_id', $location->getKey());

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);

            $builder->whereBetween('question_authors.created_at', [$start_date, $end_date]);
        }

        return $builder->count();
    }

    public static function nrApprovedClasses($location, $days)
    {

        $builder = SchoolClass::where('school_location_id', $location->getKey());

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);

            $builder->whereBetween('created_at', [$start_date, $end_date]);
        }

        return $builder->count();
    }

    public static function nrTestsTaken($location, $days)
    {
        return self::nrTestTakesByStatusIdLocationAndDays(6,$location, $days);
    }

    /**
     * there should nothing be left to check (in the frontend the normeren button should show)
     * participants => answer => needs rating
     * tcCore\TestTake::join('test_participants','test_participants.id','=','test_takes.id')->join('answers','answers.test_participant_id','=','test_participants.id')->whereNotNull('answers.final_rating')->count('test_takes.id');
     */
    public static function nrTestsChecked($location, $days)
    {

        return self::nrTestTakesByStatusIdLocationAndDays(8,$location, $days);
    }

    public static function nrTestsRated($location, $days)
    {
        return self::nrTestTakesByStatusIdLocationAndDays(9,$location, $days);
    }

    public static function nrColearningSessions($location, $days)
    {
        return self::nrTestTakesByStatusIdLocationAndDays(7,$location, $days);
    }   

    public static function nrTestTakesByStatusIdLocationAndDays($statusId, $location, $days)
    {

        $builder = TestTake::leftJoin('test_take_status_logs','test_take_status_logs.test_take_id','test_takes.id')
                        ->where('test_takes.school_location_id',$location->getKey())
                        ->where('test_take_status_logs.test_take_status_id', $statusId);

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);

             $builder->whereBetween('test_take_status_logs.created_at', [$start_date, $end_date]);
        }

        return $builder->count();
    }
}
