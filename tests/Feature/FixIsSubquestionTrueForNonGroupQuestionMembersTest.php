<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestTakeTrait;

class FixIsSubquestionTrueForNonGroupQuestionMembersTest extends TestCase
{
    use DatabaseTransactions;
    use TestTakeTrait;


    /** @test */
    public function testCommandFixIsSubquestionTrueNonGroupMember()
    {
//        $question = Question::where('derived_question_id',11)->first();
//        $this->assertNull($question);
//        $this->setUpScenario1();
//        Artisan::call('fix:isSubquestionTrueNonGroupMember');
//        $this->checkScenario1();

//        $this->setUpScenario2();
//        Artisan::call('fix:isSubquestionTrueNonGroupMember');
//        $this->checkScenario2();

//        Artisan::call('test:refreshdb');

            $this->setUpScenario3();
            Artisan::call('fix:isSubquestionTrueNonGroupMember');
            $this->checkScenario3();

//          $this->setUpScenario4();
//          Artisan::call('fix:isSubquestionTrueNonGroupMember');
//          $this->checkScenario4();
    }



    //Test not taken. Question not in other tests. Question is in answers
    private function setUpScenario1()
    {
        $questionId = 11;
        $testId = 1;
        $this->setQuestionIsSubQuestion($questionId);
        $this->removeOtherTestQuestions($questionId,$testId);
    }

    //question is copied. Test is not copied.
    private function checkScenario1()
    {
        $question = Question::where('derived_question_id',11)->first();
        $this->assertNotNull($question);
        $this->assertFalse((bool) $question->getQuestionInstance()->is_subquestion);
        $test = Test::where('derived_test_id',1)->first();
        $this->assertNull($test);
    }

    //Test not taken. Question not in other tests or answers
    private function setUpScenario2()
    {
        $questionId = 16;
        $testId = 7;
        $this->setIsSystemTestFalse($testId);
        $this->setQuestionIsSubQuestion($questionId);
        $this->removeOtherTestQuestions($questionId,$testId);
        $this->removeAllAnswers($questionId);
    }

    //question is not copied. Test is not copied. question is updated
    private function checkScenario2()
    {
        $question = Question::where('derived_question_id',16)->first();
        $this->assertNull($question);
        $test = Test::where('derived_test_id',7)->first();
        $this->assertNull($test);
        $question = Question::find(16);
        $this->assertFalse((bool) $question->getQuestionInstance()->is_subquestion);
    }

    //Test is taken. Question not in other tests or answers
    private function setUpScenario3()
    {
        $questionId = 16;
        $testId = 7;
        $this->setIsSystemTestFalse($testId);
        $this->setQuestionIsSubQuestion($questionId);
        $this->removeOtherTestQuestions($questionId,$testId);
        $testTakeId = $this->initDefaultTestTake($testId);
        $this->testTakeId = $testTakeId;
        $testTake = TestTake::find($testTakeId);
        $testTakeUuid = $testTake->uuid;
        $this->initTestTakeForClass1($testTakeUuid);
    }

    //question is copied. Test is copied.
    private function checkScenario3()
    {
        $question = Question::where('derived_question_id',16)->first();
        $this->assertNotNull($question);
        $this->assertFalse((bool) $question->getQuestionInstance()->is_subquestion);
        $question = Question::find(16);
        $this->assertTrue((bool) $question->getQuestionInstance()->is_subquestion);

    }

    //Test is taken. Question in other tests and answers
    private function setUpScenario4()
    {
        $questionId = 16;
        $testId = 7;
        $this->setIsSystemTestFalse($testId);
        $this->setQuestionIsSubQuestion($questionId);
        $this->setTestTakeStatus($testId,9);
    }

    //question is copied. Test is not copied.
    private function checkScenario4()
    {
        $question = Question::where('derived_question_id',16)->first();
        $this->assertNotNull($question);
        $test = Test::where('derived_test_id',7)->first();
        $this->assertNotNull($test);
        $question = Question::find(16);


        $this->assertFalse((bool) $question->getQuestionInstance()->is_subquestion);
    }

    private function setQuestionIsSubQuestion($questionId)
    {
        $question = Question::findOrFail($questionId);
        $question->getQuestionInstance()->is_subquestion = 1;
        $question->getQuestionInstance()->save();
    }

    private function removeOtherTestQuestions($questionId,$testId)
    {
        DB::table('test_questions')->where('test_id','!=',$testId)->where('question_id',$questionId)->delete();
    }

    private function setTestTakeStatus($testId,$status)
    {
        DB::table('test_takes')->where('test_id',$testId)->update(['test_take_status_id'=>$status]);
    }

    private function removeAllAnswers($questionId)
    {
        DB::table('answers')->where('question_id',$questionId)->delete();
    }

    private function setIsSystemTestFalse($testId)
    {
        DB::table('tests')->where('id',$testId)->update(['is_system_test'=>false]);
    }

}
