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
        Schema::table('school_years', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolYear::withTrashed()->get()->each(function($item) {
            DB::table('school_years')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('periods', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Period::withTrashed()->get()->each(function($item) {
            DB::table('periods')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Section::withTrashed()->get()->each(function($item) {
            DB::table('sections')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Subject::withTrashed()->get()->each(function($item) {
            DB::table('subjects')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolClass::withTrashed()->get()->each(function($item) {
            DB::table('school_classes')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('school_locations', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolLocation::withTrashed()->get()->each(function($item) {
            DB::table('school_locations')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('school_location_ips', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolLocationIp::withTrashed()->get()->each(function($item) {
            DB::table('school_location_ips')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('school_location_contacts', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolLocationContact::withTrashed()->get()->each(function($item) {
            DB::table('school_location_contacts')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Contact::withTrashed()->get()->each(function($item) {
            DB::table('contacts')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('school_location_school_years', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolLocationSchoolYear::withTrashed()->get()->each(function($item) {
            DB::table('school_location_school_years')->where('school_location_id', $item->school_location_id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('school_location_addresses', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SchoolLocationAddress::withTrashed()->get()->each(function($item) {
            DB::table('school_location_addresses')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('mentors', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Mentor::withTrashed()->get()->each(function($item) {
            DB::table('mentors')->where('user_id', $item->user_id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('managers', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Manager::withTrashed()->get()->each(function($item) {
            DB::table('managers')->where('user_id', $item->user_id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        User::withTrashed()->get()->each(function($item) {
            DB::table('users')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Address::withTrashed()->get()->each(function($item) {
            DB::table('addresses')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Answer::withTrashed()->get()->each(function($item) {
            DB::table('answers')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('onboarding_wizard_steps', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        OnboardingWizardStep::withTrashed()->get()->each(function($item) {
            DB::table('onboarding_wizard_steps')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Test::withTrashed()->get()->each(function($item) {
            DB::table('tests')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('onboarding_wizards', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        OnboardingWizard::withTrashed()->get()->each(function($item) {
            DB::table('onboarding_wizards')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('group_question_questions', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        GroupQuestionQuestion::withTrashed()->get()->each(function($item) {
            DB::table('group_question_questions')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('file_managements', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        FileManagement::withTrashed()->get()->each(function($item) {
            DB::table('file_managements')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('test_takes', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        TestTake::withTrashed()->get()->each(function($item) {
            DB::table('test_takes')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('test_participants', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        TestParticipant::withTrashed()->get()->each(function($item) {
            DB::table('test_participants')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('test_take_events', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        TestTakeEvent::withTrashed()->get()->each(function($item) {
            DB::table('test_take_events')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('education_levels', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        EducationLevel::withTrashed()->get()->each(function($item) {
            DB::table('education_levels')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('invigilators', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Invigilator::withTrashed()->get()->each(function($item) {
            DB::table('invigilators')->where('user_id', $item->user_id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Student::withTrashed()->get()->each(function($item) {
            DB::table('students')->where('user_id', $item->user_id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
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
            DB::table('attainments')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Teacher::withTrashed()->get()->each(function($item) {
            DB::table('teachers')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });
        
        Schema::table('sales_organizations', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        SalesOrganization::withTrashed()->get()->each(function($item) {
            DB::table('sales_organizations')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('umbrella_organizations', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        UmbrellaOrganization::withTrashed()->get()->each(function($item) {
            DB::table('umbrella_organizations')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });
        
        Schema::table('schools', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        School::withTrashed()->get()->each(function($item) {
            DB::table('schools')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        License::withTrashed()->get()->each(function($item) {
            DB::table('licenses')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Message::withTrashed()->get()->each(function($item) {
            DB::table('messages')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('grading_scales', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        GradingScale::withTrashed()->get()->each(function($item) {
            DB::table('grading_scales')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('base_subjects', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        BaseSubject::withTrashed()->get()->each(function($item) {
            DB::table('base_subjects')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        Tag::withTrashed()->get()->each(function($item) {
            DB::table('tags')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });
        
        OpenQuestion::withTrashed()->get()->each(function($item) {
            DB::table('open_questions')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });
        
        MultipleChoiceQuestion::withTrashed()->get()->each(function($item) {
            DB::table('multiple_choice_questions')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        RankingQuestion::withTrashed()->get()->each(function($item) {
            DB::table('ranking_questions')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        DrawingQuestion::withTrashed()->get()->each(function($item) {
            DB::table('drawing_questions')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        MatchingQuestion::withTrashed()->get()->each(function($item) {
            DB::table('matching_questions')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        CompletionQuestion::withTrashed()->get()->each(function($item) {
            DB::table('completion_questions')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        InfoscreenQuestion::withTrashed()->get()->each(function($item) {
            DB::table('infoscreen_questions')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        GroupQuestion::withTrashed()->get()->each(function($item) {
            DB::table('group_questions')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        TestQuestion::withTrashed()->get()->each(function($item) {
            DB::table('test_questions')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
        });

        Schema::table('test_take_event_types', function (Blueprint $table) {
            $table->efficientUuid('uuid')->index();
        });

        testTakeEventType::withTrashed()->get()->each(function($item) {
            DB::table('test_take_event_types')->where('id', $item->id)->update(['uuid' => Uuid::uuid4()->getBytes()]);
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

        Schema::table('test_take_event_types', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
}
