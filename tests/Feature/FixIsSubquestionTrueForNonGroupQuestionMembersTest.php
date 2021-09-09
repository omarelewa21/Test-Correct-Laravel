<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use tcCore\Question;
use tcCore\Test;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixIsSubquestionTrueForNonGroupQuestionMembersTest extends TestCase
{
    //use DatabaseTransactions;


    /** @test */
    public function testCommandFixIsSubquestionTrueNonGroupMember()
    {
        $question = Question::where('derived_question_id',11)->first();
        $this->assertNull($question);
        //$this->setUpScenario1();
        //Artisan::call('fix:isSubquestionTrueNonGroupMember');
        //$this->checkScenario1();
//        $question  = Question::find(23);
//        dump($question);
//        dump([env('DB_DATABASE'),env('DB_HOST'),config('database.default')]);
        $this->setUpScenario2();
        Artisan::call('fix:isSubquestionTrueNonGroupMember');
        $this->checkScenario2();
    }

    //Test not taken. Question not in other tests
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

    //Test not taken. Question not in other tests
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

    private function removeAllAnswers($questionId)
    {
        DB::table('answers')->where('question_id',$questionId)->delete();
    }

    private function setIsSystemTestFalse($testId)
    {
        DB::table('tests')->where('id',$testId)->update(['is_system_test'=>false]);
    }

}
