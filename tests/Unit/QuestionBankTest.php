<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\Attainment;
use tcCore\EckidUser;
use tcCore\OpenQuestion;
use tcCore\Question;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use Tests\Feature\OpenQuestionTest;
use Tests\TestCase;

class QuestionBankTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_create_a_clean_copy_from_a_question_and_update_education_level_year()
    {
        $startQuestionCount = Question::count();
        $startOpenQuestionCount = OpenQuestion::count();
        $this->actingAs($this->getTeacherOne());
        $question = Question::find(10);
        $questionInstance = $question->getQuestionInstance();

        $this->assertEquals(1, $questionInstance->education_level_year);
        $newQuestion = $question->createCleanCopy($questionInstance->education_level_id, 2, $questionInstance->subject_id, auth()->user());

        $this->assertNotEquals($question->fresh()->id, $newQuestion->id);
        $this->assertEquals(($startQuestionCount+1), Question::count());
        $this->assertEquals(($startOpenQuestionCount+1), OpenQuestion::count());
        $this->assertEquals(2, $newQuestion->education_level_year);

        // check that the original still has the old education_level_id
        $this->assertEquals(1, $question->fresh()->education_level_year);
    }

    /** @test */
    public function it_can_create_a_clean_copy_from_a_question_and_update_education_level()
    {
        $startQuestionCount = Question::count();
        $startOpenQuestionCount = OpenQuestion::count();
        $this->actingAs($this->getTeacherOne());
        $question = Question::find(10);
        $questionInstance = $question->getQuestionInstance();

        $this->assertEquals(1, $questionInstance->education_level_id);
        $newQuestion = $question->createCleanCopy(
            2,
            $questionInstance->education_level_year,
            $questionInstance->subject_id, auth()->user());

        $this->assertNotEquals($question->fresh()->id, $newQuestion->id);
        $this->assertEquals(($startQuestionCount+1), Question::count());
        $this->assertEquals(($startOpenQuestionCount+1), OpenQuestion::count());
        $this->assertEquals(2, $newQuestion->education_level_id);

        // check that the original still has the old education_level_id
        $this->assertEquals(1, $question->fresh()->education_level_year);
    }

    /** @test */
    public function it_can_create_a_clean_copy_from_a_question_and_update_subject()
    {
        $startQuestionCount = Question::count();
        $startOpenQuestionCount = OpenQuestion::count();
        $this->actingAs($this->getTeacherOne());
        $question = Question::find(10);
        $questionInstance = $question->getQuestionInstance();

        $this->assertEquals(1, $questionInstance->subject_id);
        $newQuestion = $question->createCleanCopy(
            $questionInstance->education_level_id,
            $questionInstance->education_level_year,
            2,
            auth()->user()
        );

        $this->assertNotEquals($question->fresh()->id, $newQuestion->id);
        $this->assertEquals(($startQuestionCount+1), Question::count());
        $this->assertEquals(($startOpenQuestionCount+1), OpenQuestion::count());
        $this->assertEquals(2, $newQuestion->subject_id);

        // check that the original still has the old education_level_id
        $this->assertEquals(1, $question->fresh()->education_level_year);
    }

    /** @test */
    public function when_making_a_clean_copy_the_author_of_the_question_should_be_the_current_user()
    {
        $teacherTwo = User::whereUsername(self::USER_TEACHER_TWO)->first();
        $this->actingAs($teacherTwo);
        $question = Question::find(10);
        $this->assertTrue($question->authors->contains($this->getTeacherOne()));
        $this->assertFalse($question->authors->contains($teacherTwo));
        $questionInstance = $question->getQuestionInstance();
        $newQuestion = $question->createCleanCopy(
            $questionInstance->education_level_id,
            $questionInstance->education_level_year,
            $questionInstance->subject_id,
            $teacherTwo
        );
        $this->assertTrue($newQuestion->authors->contains($this->getTeacherOne()));
        $this->assertTrue($newQuestion->authors->contains($teacherTwo));

        $this->assertTrue($question->authors->contains($this->getTeacherOne()));
        $this->assertfalse($question->authors->contains($teacherTwo));

    }

    /** @test */
    public function it_can_create_a_clean_copy_from_a_group_question()
    {
        $startQuestionCount = Question::count();
        $this->actingAs($this->getTeacherOne());
        $groupQuestion = Question::find(14);
        $questionInstance = $groupQuestion->getQuestionInstance();

        $this->assertEquals('GroupQuestion', $questionInstance->type);

        $groupQuestion->createCleanCopy(
            $questionInstance->education_level_id,
            $questionInstance->education_level_year,
            $questionInstance->subject_id,
            $this->getTeacherOne()
        );

        $this->assertEquals(($startQuestionCount+2), Question::count());
    }

    /** @test */
    public function it_should_add_the_new_author_to_all_the_questions_in_the_group()
    {
        $teacherTwo = User::whereUsername(self::USER_TEACHER_TWO)->first();
        $this->actingAs($teacherTwo);
        $groupQuestion = Question::find(14);
        $questionInstance = $groupQuestion->getQuestionInstance();

        $newGroupQuestion = $groupQuestion->createCleanCopy(
            $questionInstance->education_level_id,
            $questionInstance->education_level_year,
            $questionInstance->subject_id,
            $this->getTeacherOne()
        );

        $this->assertTrue($newGroupQuestion->authors->contains($this->getTeacherOne()));
        $this->assertTrue($newGroupQuestion->authors->contains($teacherTwo));

        $this->assertTrue($groupQuestion->refresh()->authors->contains($this->getTeacherOne()));
        $this->assertFalse($groupQuestion->refresh()->authors->contains($teacherTwo));

        $newGroupQuestion->groupQuestionQuestions->each(function($gqq) use ($teacherTwo) {
            $this->assertTrue($gqq->question->authors->contains($this->getTeacherOne()));
            $this->assertTrue($gqq->question->authors->contains($teacherTwo));
        });

        $groupQuestion->refresh()->groupQuestionQuestions->each(function($gqq) use ($teacherTwo) {
            $this->assertTrue($gqq->question->authors->contains($this->getTeacherOne()));
            $this->assertFalse($gqq->question->authors->contains($teacherTwo));
        });
    }
}
