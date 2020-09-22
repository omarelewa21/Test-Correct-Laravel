<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\SchoolYear;
use Ramsey\Uuid\Uuid;
use tcCore\Address;
use tcCore\Answer;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\CompletionQuestion;
use tcCore\Contact;
use tcCore\DrawingQuestion;
use tcCore\EducationLevel;
use tcCore\FileManagement;
use tcCore\GradingScale;
use tcCore\GroupQuestion;
use tcCore\GroupQuestionQuestion;
use tcCore\InfoscreenQuestion;
use tcCore\Invigilator;
use tcCore\License;
use tcCore\Manager;
use tcCore\MatchingQuestion;
use tcCore\MatrixQuestion;
use tcCore\Mentor;
use tcCore\Message;
use tcCore\MultipleChoiceQuestion;
use tcCore\OnboardingWizard;
use tcCore\OnboardingWizardStep;
use tcCore\OpenQuestion;
use tcCore\Period;
use tcCore\Question;
use tcCore\RankingQuestion;
use tcCore\SalesOrganization;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationAddress;
use tcCore\SchoolLocationContact;
use tcCore\SchoolLocationIp;
use tcCore\SchoolLocationSchoolYear;
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
use tcCore\testTakeEventType;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class AddUuidColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        set_time_limit(10 * 60);
        ini_set('memory_limit','-1');

        $this->down();

        DB::beginTransaction();
        try {
            Schema::table('school_years', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update school_years set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('periods', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update periods set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('sections', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update sections set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('subjects', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update subjects set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('school_classes', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update school_classes set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');


            Schema::table('school_locations', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update school_locations set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('school_location_ips', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update school_location_ips set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('school_location_contacts', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update school_location_contacts set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('contacts', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update contacts set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('school_location_school_years', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update school_location_school_years set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('school_location_addresses', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update school_location_addresses set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('mentors', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update mentors set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('managers', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update managers set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('users', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update users set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('addresses', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update addresses set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('answers', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update answers set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('onboarding_wizard_steps', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update onboarding_wizard_steps set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('tests', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update tests set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('onboarding_wizards', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update onboarding_wizards set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('group_question_questions', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update group_question_questions set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('file_managements', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update file_managements set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('test_takes', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update test_takes set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('test_participants', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update test_participants set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('test_take_events', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update test_take_events set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('education_levels', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update education_levels set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('invigilators', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update invigilators set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('students', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update students set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('questions', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update questions set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            collect(['completion_questions','multiple_choice_questions','infoscreen_questions','open_questions','matching_questions','ranking_questions','drawing_questions','matrix_questions','group_questions'])->each(function($tableName){
                Schema::table($tableName, function (Blueprint $table) {
                    $table->efficientUuid('uuid')->index()->unique()->nullable();
                });

                DB::statement('update '.$tableName.' inner join questions on (questions.id = '.$tableName.'.id) set '.$tableName.'.uuid = questions.uuid');
            });

            Schema::table('test_questions', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update test_questions set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('attainments', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update attainments set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('teachers', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update teachers set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('sales_organizations', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update sales_organizations set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('umbrella_organizations', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update umbrella_organizations set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('schools', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update schools set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('licenses', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update licenses set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('messages', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update messages set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('grading_scales', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update grading_scales set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('base_subjects', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update base_subjects set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('tags', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update tags set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

            Schema::table('test_take_event_types', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::statement('update test_take_event_types set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at COLLATE utf8_general_ci),created_at COLLATE utf8_general_ci,""), "-",""))) where uuid is null');

        } catch (\Exception $e) {
            DB::rollback();
            logger('===== error with UUID migration' . $e->getMessage());
            throw $e;
        }
        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        collect(['users','periods','sections','subjects','school_classes','school_locations','school_location_ips','contacts','school_location_contacts','school_location_contacts',
            'school_location_school_years','school_location_addresses','mentors','managers','addresses','answers','onboarding_wizard_steps','tests',
            'test_questions','onboarding_wizards','group_question_questions','file_managements','test_takes','test_participants','test_take_events','education_levels',
            'invigilators','students','open_questions','attainments','teachers','sales_organizations','umbrella_organizations','schools','licenses','messages', 'grading_scales',
            'base_subjects', 'tags','group_questions','infoscreen_questions','completion_questions','multiple_choice_questions','ranking_questions','matching_questions',
            'drawing_questions','matrix_questions','questions','test_take_event_types',])->unique()->each(function($tableName){
            if (Schema::hasColumn($tableName, 'uuid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('uuid');
                });
            }
        });
    }
}
