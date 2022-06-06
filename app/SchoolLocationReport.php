<?php

namespace tcCore;

use Carbon\Carbon;
use tcCore\Http\Helpers\ReportHelper;
use tcCore\Jobs\UpdateSchoolLocationReportRecord;
use tcCore\SchoolLocation;
use Illuminate\Database\Eloquent\Model;


set_time_limit(300);

class SchoolLocationReport extends Model
{

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'keep_out_of_school_location_report' => 'boolean',
    ];

    protected $guarded = [];

    public static function updateAllLocationStats($shouldTruncate = true)
    {

        if ($shouldTruncate) {
            SchoolLocationReport::truncate();
        }

        SchoolLocation::where('keep_out_of_school_location_report',true)->get()->each(function (SchoolLocation $l) {
            dispatch((new UpdateSchoolLocationReportRecord($l)));
            // self::updateLocationStats($l);
        });
    }

    public static function updateLocationStats(SchoolLocation $location)
    {

        $helper = new ReportHelper($location);

        return self::updateOrCreate([
            'school_location_id' => $location->getKey(),
        ], [
            'company_id' => $location->company_id,
            'school_location_name' => $location->name,
            'nr_licenses' => $helper->nrLicenses(),
            'nr_activated_licenses_total' => $helper->nrActivatedAccounts(0),//$helper->getActiveLicenses(),
            'nr_activated_licenses_30' => $helper->nrActivatedAccounts(30),
            'nr_activated_licenses_60' => $helper->nrActivatedAccounts(60),
            'nr_activated_licenses_90' => $helper->nrActivatedAccounts(90),
            'nr_uploaded_test_files_30' => $helper->nrUploadedTestFiles(30),
            'nr_uploaded_test_files_60' => $helper->nrUploadedTestFiles(60),
            'nr_uploaded_test_files_90' => $helper->nrUploadedTestFiles(90),
            'nr_uploaded_test_files_365' => $helper->nrUploadedTestFiles(365),
            'nr_uploaded_test_files_total' => $helper->nrUploadedTestFiles(0), // 2.a.2
            'nr_added_question_items_7' => $helper->nrAddedQuestionItems(7), // 2.a.3
            'nr_added_question_items_30' => $helper->nrAddedQuestionItems(30), // 2.a.3
            'nr_added_question_items_60' => $helper->nrAddedQuestionItems(60), // 2.a.3
            'nr_added_question_items_90' => $helper->nrAddedQuestionItems(90), // 2.a.3
            'nr_added_question_items_365' => $helper->nrAddedQuestionItems(365), // 2.a.3
            'nr_added_question_items_total' => $helper->nrAddedQuestionItems(0), // 2.a.4
            'nr_uploaded_class_files_30' => $helper->nrUploadedClassFiles(30), // 3.a.1
            'nr_uploaded_class_files_60' => $helper->nrUploadedClassFiles(60), // 3.a.1
            'nr_uploaded_class_files_90' => $helper->nrUploadedClassFiles(90), // 3.a.1
            'nr_uploaded_class_files_365' => $helper->nrUploadedClassFiles(365), // 3.a.1
            'nr_uploaded_class_files_total' => $helper->nrUploadedClassFiles(0), // 3.a.2
            'nr_tests_taken_7' => $helper->nrTestsTaken(7), // 3.a.1
            'nr_tests_taken_30' => $helper->nrTestsTaken(30), // 3.a.1
            'nr_tests_taken_60' => $helper->nrTestsTaken(60), // 3.a.1
            'nr_tests_taken_90' => $helper->nrTestsTaken(90), // 3.a.1
            'nr_tests_taken_365' => $helper->nrTestsTaken(365), // 3.a.1
            'nr_tests_taken_total' => $helper->nrTestsTaken(0), // 3.a.2
            'nr_tests_rated_7' => $helper->nrTestsRated(7), // 3.a.1
            'nr_tests_rated_30' => $helper->nrTestsRated(30), // 3.a.1
            'nr_tests_rated_60' => $helper->nrTestsRated(60), // 3.a.1
            'nr_tests_rated_90' => $helper->nrTestsRated(90), // 3.a.1
            'nr_tests_rated_365' => $helper->nrTestsRated(365), // 3.a.1
            'nr_tests_rated_total' => $helper->nrTestsRated(0), // 3.a.2
            'nr_colearning_sessions_7' => $helper->nrColearningSessions(7), // 3.a.1
            'nr_colearning_sessions_30' => $helper->nrColearningSessions(30), // 3.a.1
            'nr_colearning_sessions_60' => $helper->nrColearningSessions(60), // 3.a.1
            'nr_colearning_sessions_90' => $helper->nrColearningSessions(90), // 3.a.1
            'nr_colearning_sessions_365' => $helper->nrColearningSessions(365), // 3.a.1
            'nr_colearning_sessions_total' => $helper->nrColearningSessions(0), // 3.a.2
            'nr_unique_students_taken_test_7' => $helper->nrUniqueStudentsTakenTest(7), // 4.a.9
            'nr_unique_students_taken_test_30' => $helper->nrUniqueStudentsTakenTest(30), // 4.a.9
            'nr_unique_students_taken_test_60' => $helper->nrUniqueStudentsTakenTest(60), // 4.a.9
            'nr_unique_students_taken_test_90' => $helper->nrUniqueStudentsTakenTest(90), // 4.a.9
            'nr_unique_students_taken_test_365' => $helper->nrUniqueStudentsTakenTest(365), // 4.a.9
            'nr_unique_students_taken_test_total' => $helper->nrUniqueStudentsTakenTest(0), // 4.a.10
            'nr_participants_taken_test_7' => $helper->nrParticipantsTakenTest(7), // 4.a.9
            'nr_participants_taken_test_30' => $helper->nrParticipantsTakenTest(30), // 4.a.9
            'nr_participants_taken_test_60' => $helper->nrParticipantsTakenTest(60), // 4.a.9
            'nr_participants_taken_test_90' => $helper->nrParticipantsTakenTest(90), // 4.a.9
            'nr_participants_taken_test_365' => $helper->nrParticipantsTakenTest(365), // 4.a.9
            'nr_participants_taken_test_total' => $helper->nrParticipantsTakenTest(0), // 4.a.10
            'lvs_type' => $helper->getLVSType($location), // 4.a.10
            'lvs_active' => $helper->getLVSActive($location),
            'sso_type' => $helper->getSSOType($location),
            'sso_active' => $helper->getSSOActive($location), // 4.a.10
            'intense' => $helper->getIntense($location),
            'allow_inbrowser_testing' => $helper->getAllowInbrowserTesting($location),
            'klantcode_schoollocatie' => $helper->getCustomerCode($location),
            'lvs_active_no_mail_allowed' => $helper->getLVSActiveNoMailAllowed($location),

        ]);


    }


}
