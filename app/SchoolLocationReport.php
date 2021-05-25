<?php

namespace tcCore;

use Carbon\Carbon;
use tcCore\Http\Helpers\ReportHelper;
use tcCore\SchoolLocation;
use Illuminate\Database\Eloquent\Model;


set_time_limit(300);

class SchoolLocationReport extends Model
{

    protected $guarded = [];

    public static function updateAllLocationStats($shouldTruncate = true)
    {

        if($shouldTruncate){
            SchoolLocationReport::truncate();
        }

        SchoolLocation::all()->each(function(SchoolLocation $l){
            self::updateLocationStats($l);
        });
    }

    public static function updateLocationStats(SchoolLocation $location)
    {

        $helper = new ReportHelper($location);

        return self::updateOrCreate([
                    'school_location_id' => $location->getKey(),
                        ], [
                    'school_location_name' => $location->name,
                    'nr_licenses' => $location->count_licenses,
                    'nr_activated_licenses' => $helper->getActiveLicenses(),
                    'nr_browsealoud_licenses' => $location->count_text2speech,
                    'nr_approved_test_files_7' => $helper->nrApprovedTestFiles(7),
                    'nr_approved_test_files_30' => $helper->nrApprovedTestFiles(30),
                    'nr_approved_test_files_60' => $helper->nrApprovedTestFiles( 60),
                    'nr_approved_test_files_90' => $helper->nrApprovedTestFiles(90),
                    'nr_approved_test_files_365' => $helper->nrApprovedTestFiles(365),
                    'total_approved_test_files' => $helper->nrApprovedTestFiles(0), // 2.a.2
                    'nr_added_question_items_7' => $helper->nrAddedQuestionItems(7), // 2.a.3
                    'nr_added_question_items_30' => $helper->nrAddedQuestionItems(30), // 2.a.3
                    'nr_added_question_items_60' => $helper->nrAddedQuestionItems(60), // 2.a.3
                    'nr_added_question_items_90' => $helper->nrAddedQuestionItems(90), // 2.a.3
                    'nr_added_question_items_365' => $helper->nrAddedQuestionItems(365), // 2.a.3
                    'total_added_question_items_files' => $helper->nrAddedQuestionItems(0), // 2.a.4
                    'nr_approved_classes_7' => $helper->nrApprovedClassFiles(7), // 3.a.1
                    'nr_approved_classes_30' => $helper->nrApprovedClassFiles(30), // 3.a.1
                    'nr_approved_classes_60' => $helper->nrApprovedClassFiles(60), // 3.a.1
                    'nr_approved_classes_90' => $helper->nrApprovedClassFiles(90), // 3.a.1
                    'nr_approved_classes_365' => $helper->nrApprovedClassFiles(365), // 3.a.1
                    'total_approved_classes' => $helper->nrApprovedClassFiles(0), // 3.a.2
                    'nr_tests_taken_7' => $helper->nrTestsTaken(7), // 3.a.1
                    'nr_tests_taken_30' => $helper->nrTestsTaken(30), // 3.a.1
                    'nr_tests_taken_60' => $helper->nrTestsTaken(60), // 3.a.1
                    'nr_tests_taken_90' => $helper->nrTestsTaken(90), // 3.a.1
                    'nr_tests_taken_365' => $helper->nrTestsTaken(365), // 3.a.1
                    'total_tests_taken' => $helper->nrTestsTaken(0), // 3.a.2
                    'nr_tests_checked_7' => $helper->nrTestsChecked(7), // 3.a.1
                    'nr_tests_checked_30' => $helper->nrTestsChecked(30), // 3.a.1
                    'nr_tests_checked_60' => $helper->nrTestsChecked(60), // 3.a.1
                    'nr_tests_checked_90' => $helper->nrTestsChecked(90), // 3.a.1
                    'nr_tests_checked_365' => $helper->nrTestsChecked(365), // 3.a.1
                    'total_tests_checked' => $helper->nrTestsChecked(0), // 3.a.2
                    'nr_tests_rated_7' => $helper->nrTestsRated(7), // 3.a.1
                    'nr_tests_rated_30' => $helper->nrTestsRated(30), // 3.a.1
                    'nr_tests_rated_60' => $helper->nrTestsRated(60), // 3.a.1
                    'nr_tests_rated_90' => $helper->nrTestsRated(90), // 3.a.1
                    'nr_tests_rated_365' => $helper->nrTestsRated(365), // 3.a.1
                    'total_tests_rated' => $helper->nrTestsRated(0), // 3.a.2
                    'nr_colearning_sessions_7' => $helper->nrColearningSessions( 7), // 3.a.1
                    'nr_colearning_sessions_30' => $helper->nrColearningSessions( 30), // 3.a.1
                    'nr_colearning_sessions_60' => $helper->nrColearningSessions( 60), // 3.a.1
                    'nr_colearning_sessions_90' => $helper->nrColearningSessions( 90), // 3.a.1
                    'nr_colearning_sessions_365' => $helper->nrColearningSessions( 365), // 3.a.1
                    'total_colearning_sessions' => $helper->nrColearningSessions( 0), // 3.a.2

                    'nr_unique_students_taken_test_7' => $helper->nrUniqueStudentsTakenTest(7), // 4.a.9
                    'nr_unique_students_taken_test_30' => $helper->nrUniqueStudentsTakenTest(30), // 4.a.9
                    'nr_unique_students_taken_test_60' => $helper->nrUniqueStudentsTakenTest(60), // 4.a.9
                    'nr_unique_students_taken_test_90' => $helper->nrUniqueStudentsTakenTest(90), // 4.a.9
                    'nr_unique_students_taken_test_365' => $helper->nrUniqueStudentsTakenTest(365), // 4.a.9
                    'total_unique_students_taken_test' => $helper->nrUniqueStudentsTakenTest(0), // 4.a.10

                    'in_browser_tests_allowed' => $location->allow_inbrowser_testing ? 1 : 0, // 3.a.2
                    'nr_active_teachers' => $helper->nrActiveTeachers(4,60), // 3.a.2
        ]);
        
             
    }


}
