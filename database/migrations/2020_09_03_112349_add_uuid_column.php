<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
use tcCore\TestTakeEventType;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class AddUuidColumn extends Migration
{

    protected $tables = ['users','periods','sections','subjects','school_classes','school_locations','school_location_ips','contacts','school_location_contacts','school_location_contacts',
        'school_location_school_years','school_location_addresses','mentors','managers','addresses','answers','onboarding_wizard_steps','tests',
        'test_questions','onboarding_wizards','group_question_questions','file_managements','test_takes','test_participants','test_take_events','education_levels',
        'invigilators','students','open_questions','attainments','teachers','sales_organizations','umbrella_organizations','schools','licenses','messages', 'grading_scales',
        'base_subjects', 'tags','group_questions','infoscreen_questions','completion_questions','multiple_choice_questions','ranking_questions','matching_questions',
        'drawing_questions','matrix_questions','questions','test_take_event_types','school_years','test_kinds'];

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


//        DB::beginTransaction();
        try {

            collect($this->tables)->unique()->each(function($tableName){
                if (!Schema::hasColumn($tableName, 'uuid')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->efficientUuid('uuid')->index()->unique()->nullable();
                    });
                }
            });

        } catch (\Exception $e) {
//            DB::rollback();
            logger('===== error with UUID STRUCTURE migration' . $e->getMessage());
            throw $e;
        }
//        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        collect($this->tables)->unique()->each(function($tableName){
            if (Schema::hasColumn($tableName, 'uuid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('uuid');
                });
            }
        });
    }
}
