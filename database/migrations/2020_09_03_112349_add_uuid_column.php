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

        DB::beginTransaction();
        try {
            Schema::table('school_years', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update school_years set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('periods', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update periods set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('sections', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update sections set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('subjects', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update subjects set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('school_classes', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update school_classes set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');


            Schema::table('school_locations', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update school_locations set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('school_location_ips', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update school_location_ips set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('school_location_contacts', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update school_location_contacts set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('contacts', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update contacts set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('school_location_school_years', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update school_location_school_years set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('school_location_addresses', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update school_location_addresses set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('mentors', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update mentors set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('managers', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update managers set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('users', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update users set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('addresses', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update addresses set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('answers', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update answers set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('onboarding_wizard_steps', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update onboarding_wizard_steps set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('tests', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update tests set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('onboarding_wizards', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update onboarding_wizards set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('group_question_questions', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update group_question_questions set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('file_managements', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update file_managements set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('test_takes', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update test_takes set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('test_participants', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update test_participants set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('test_take_events', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update test_take_events set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('education_levels', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update education_levels set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('invigilators', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update invigilators set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('students', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update students set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('questions', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update questions set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            collect(['completion_questions','multiple_choice_questions','infoscreen_questions','open_questions','matching_questions','ranking_questions','drawing_questions','matrix_questions','group_questions'])->each(function($tableName){
                Schema::table($tableName, function (Blueprint $table) {
                    $table->efficientUuid('uuid')->index()->unique()->nullable();
                });

                DB::raw('update '.$tableName.' inner join questions on (questions.id = '.$tableName.'.id) set '.$tableName.'.uuid = questions.uuid');
            });

            Schema::table('test_questions', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update test_questions set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('attainments', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update attainments set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('teachers', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update teachers set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('sales_organizations', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update sales_organizations set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('umbrella_organizations', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update umbrella_organizations set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('schools', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update schools set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('licenses', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update licenses set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('messages', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update messages set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('grading_scales', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update grading_scales set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('base_subjects', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update base_subjects set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('tags', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update tags set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

            Schema::table('test_take_event_types', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });

            DB::raw('update test_take_event_types set uuid = (select UNHEX(REPLACE(REPLACE(CONCAT(UUID(),created_at),created_at,""), "-",""))) where uuid is null');

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
        Schema::table('school_years', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('periods', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('school_location_ips', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('school_location_contacts', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('school_location_school_years', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('school_location_addresses', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('mentors', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('managers', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('onboarding_wizard_steps', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('test_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('onboarding_wizards', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('group_question_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('file_managements', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('test_participants', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('test_take_events', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('education_levels', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('invigilators', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('open_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('attainments', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('sales_organizations', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        
        Schema::table('umbrella_organizations', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('grading_scales', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('base_subjects', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('group_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('infoscreen_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('completion_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('multiple_choice_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('ranking_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('matching_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('drawing_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('matrix_questions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('test_take_event_types', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
}
