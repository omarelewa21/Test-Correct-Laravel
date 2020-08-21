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
        Schema::table('school_years', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolYear::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('periods', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Period::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Section::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Subject::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolClass::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('school_locations', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolLocation::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('school_location_ips', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolLocationIp::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('school_location_contacts', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolLocationContact::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Contact::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('school_location_school_years', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolLocationSchoolYear::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('school_location_addresses', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolLocationAddress::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('mentors', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Mentor::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('managers', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Manager::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        User::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Address::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Answer::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('onboarding_wizard_steps', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        OnboardingWizardStep::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Test::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('onboarding_wizards', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        OnboardingWizard::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('group_question_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        GroupQuestionQuestion::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('file_managements', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        FileManagement::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('test_takes', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        TestTake::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('test_participants', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        TestParticipant::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('test_take_events', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        TestTakeEvent::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('education_levels', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        EducationLevel::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('invigilators', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Invigilator::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Student::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('completion_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Schema::table('multiple_choice_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Schema::table('infoscreen_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Schema::table('open_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Schema::table('matching_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });
        
        Schema::table('ranking_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });
                
        Schema::table('answer_parent_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Schema::table('drawing_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Schema::table('test_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Schema::table('group_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });
      
        Schema::table('attainments', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Attainment::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Teacher::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });
        
        Schema::table('sales_organizations', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SalesOrganization::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('umbrella_organizations', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        UmbrellaOrganization::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });
        
        Schema::table('schools', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        School::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        License::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Message::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('grading_scales', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        GradingScale::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('base_subjects', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        BaseSubject::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Tag::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });
        
        OpenQuestion::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });
        
        MultipleChoiceQuestion::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        RankingQuestion::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        DrawingQuestion::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        MatchingQuestion::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        CompletionQuestion::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        InfoscreenQuestion::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        GroupQuestion::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });

        TestQuestion::withTrashed()->get()->each(function($item) {
            $item->uuid = Uuid::uuid4();
            $item->save();
        });
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
    }
}
