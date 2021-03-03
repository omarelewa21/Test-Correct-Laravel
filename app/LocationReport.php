<?php

namespace tcCore;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use tcCore\SchoolLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LocationReport extends Model
{

    protected $guarded = [];

    public static function updateAllLocationStats()
    {

        foreach (SchoolLocation::all('id') as $row) {

            self::updateLocationStats($row['id']);
        }
    }

    public static function updateLocationStats($location_id)
    {

        return self::updateOrCreate([
                    'location_id' => $location_id,
                        ], [
                    'nr_licenses' => self::nrLicenses($location_id),
                    'nr_activated_licenses' => self::nrActivatedLicenses($location_id),
                    'nr_browsealoud_licenses' => self::nrBrowseAloudLicenses($location_id),
                    'nr_approved_test_files_7' => self::nrApprovedTestFiles($location_id, 7),
                    'nr_approved_test_files_30' => self::nrApprovedTestFiles($location_id, 30),
                    'nr_approved_test_files_60' => self::nrApprovedTestFiles($location_id, 60),
                    'nr_approved_test_files_90' => self::nrApprovedTestFiles($location_id, 90),
                    'total_approved_test_files' => self::nrApprovedTestFiles($location_id, 0), // 2.a.2
                    'nr_added_question_items_7' => self::nrAddedQuestionItems($location_id, 7), // 2.a.3
                    'nr_added_question_items_30' => self::nrAddedQuestionItems($location_id, 30), // 2.a.3
                    'nr_added_question_items_60' => self::nrAddedQuestionItems($location_id, 60), // 2.a.3
                    'nr_added_question_items_90' => self::nrAddedQuestionItems($location_id, 90), // 2.a.3
                    'total_added_question_items_files' => self::nrAddedQuestionItems($location_id, 0), // 2.a.4
                    'nr_approved_classes_7' => self::nrApprovedClasses($location_id, 7), // 3.a.1
                    'nr_approved_classes_30' => self::nrApprovedClasses($location_id, 30), // 3.a.1
                    'nr_approved_classes_60' => self::nrApprovedClasses($location_id, 60), // 3.a.1
                    'nr_approved_classes_90' => self::nrApprovedClasses($location_id, 90), // 3.a.1
                    'total_approved_classes' => self::nrApprovedClasses($location_id, 0), // 3.a.2
                    'nr_tests_taken_7' => self::nrTestsTaken($location_id, 7), // 3.a.1
                    'nr_tests_taken_30' => self::nrTestsTaken($location_id, 30), // 3.a.1
                    'nr_tests_taken_60' => self::nrTestsTaken($location_id, 60), // 3.a.1
                    'nr_tests_taken_90' => self::nrTestsTaken($location_id, 90), // 3.a.1
                    'total_tests_taken' => self::nrTestsTaken($location_id, 0), // 3.a.2
                    'nr_tests_checked_7' => self::nrTestsChecked($location_id, 7), // 3.a.1
                    'nr_tests_checked_30' => self::nrTestsChecked($location_id, 30), // 3.a.1
                    'nr_tests_checked_60' => self::nrTestsChecked($location_id, 60), // 3.a.1
                    'nr_tests_checked_90' => self::nrTestsChecked($location_id, 90), // 3.a.1
                    'total_tests_checked' => self::nrTestsChecked($location_id, 0), // 3.a.2
                    'nr_tests_rated_7' => self::nrTestsRated($location_id, 7), // 3.a.1
                    'nr_tests_rated_30' => self::nrTestsRated($location_id, 30), // 3.a.1
                    'nr_tests_rated_60' => self::nrTestsRated($location_id, 60), // 3.a.1
                    'nr_tests_rated_90' => self::nrTestsRated($location_id, 90), // 3.a.1
                    'total_tests_rated' => self::nrTestsRated($location_id, 0), // 3.a.2
                    'nr_colearning_sessions_7' => self::nrColearningSessions($location_id, 7), // 3.a.1
                    'nr_colearning_sessions_30' => self::nrColearningSessions($location_id, 30), // 3.a.1
                    'nr_colearning_sessions_60' => self::nrColearningSessions($location_id, 60), // 3.a.1
                    'nr_colearning_sessions_90' => self::nrColearningSessions($location_id, 90), // 3.a.1
                    'total_colearning_sessions' => self::nrColearningSessions($location_id, 0), // 3.a.2
                    'in_browser_tests_allowed' => self::inBrowserTestsAllowed($location_id), // 3.a.2
                    'nr_active_teachers' => self::nrActiveTeachers($location_id), // 3.a.2
        ]);
    }

    private static function nrLicenses($location_id)
    {
        return SchoolLocation::where('id', $location_id)->value('count_licenses');
    }

    private static function nrActivatedLicenses($location_id)
    {

        return SchoolLocation::where('id', $location_id)->value('count_active_licenses');
    }

    private static function nrBrowseAloudLicenses($location_id)
    {

        return SchoolLocation::where('id', $location_id)->value('count_text2speech');
    }

    public static function nrApprovedTestFiles($location_id, $days)
    {

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);


            return Test::leftJoin('users', function($join) {
                                $join->on('tests.author_id', '=', 'users.id');
                            })->where('users.school_location_id', $location_id)
                            ->where('tests.published', 1)->whereBetween('tests.created_at', [$start_date, $end_date])->get()->count();
        } else {

            return Test::leftJoin('users', function($join) {
                                $join->on('tests.author_id', '=', 'users.id');
                            })->where('users.school_location_id', $location_id)
                            ->where('tests.published', 1)->count();
        }
    }

    public static function nrAddedQuestionItems($location_id, $days)
    {

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);


            return QuestionAuthor::
                            leftJoin('users', function($join) {
                                $join->on('question_authors.user_id', '=', 'users.id');
                            })->where('users.school_location_id', $location_id)
                            ->whereBetween('question_authors.created_at', [$start_date, $end_date])->get()->count();
        } else {

            return QuestionAuthor::leftJoin('users', function($join) {
                                $join->on('question_authors.user_id', '=', 'users.id');
                            })->where('users.school_location_id', $location_id)
                            ->count();
        }
    }

    public static function nrApprovedClasses($location_id, $days)
    {

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);


            return SchoolClass::where('school_location_id', $location_id)
                            ->whereBetween('created_at', [$start_date, $end_date])->count();
        } else {

            return SchoolClass::where('school_location_id', $location_id)->count();
        }
    }

    public static function nrTestsTaken($location_id, $days)
    {
        return self::nrTestTakeStatusForStatusLocationDays(6,$location_id, $days);
    }

    public static function nrTestsChecked($location_id, $days)
    {

        return self::nrTestTakeStatusForStatusLocationDays(8,$location_id, $days);
    }

    public static function nrTestsRated($location_id, $days)
    {
        return self::nrTestTakeStatusForStatusLocationDays(9,$location_id, $days);
    }

    public static function nrColearningSessions($location_id, $days)
    {
        return self::nrTestTakeStatusForStatusLocationDays(7,$location_id, $days);
    }   

    public static function nrTestTakeStatusForStatusLocationDays($status,$location_id, $days)
    {

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);
        
        $count = TestTake::leftJoin('users','users.id','=','test_takes.user_id')                      
                ->leftJoin('test_take_status_log','test_takes.id','=','test_take_status_log.test_take_id')
                ->where('users.school_location_id', $location_id)
                ->where('test_take_status_log.test_take_status',$status)
                ->whereBetween('test_takes.created_at', [$start_date, $end_date])
                ->groupBy('test_take_id')
                ->count();
        
        } else { 

        $count =  TestTake::leftJoin('users','users.id','=','test_takes.user_id')          
                ->leftJoin('test_take_status_log','test_takes.id','=','test_take_status_log.test_take_id')
                ->where('users.school_location_id', $location_id)
                ->where('test_take_status_log.test_take_status',$status)
                ->groupBy('test_take_id')
                ->count();
            
        }
        
        return $count;
               
    }

    private static function inBrowserTestsAllowed($location_id)
    {
        return SchoolLocation::where('id', $location_id)->value('allow_inbrowser_testing');
    }

    private static function nrActiveTeachers($location_id)
    {
        return SchoolLocation::where('id', $location_id)->value('count_active_teachers');
    }

}
