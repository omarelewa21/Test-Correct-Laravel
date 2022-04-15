<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\TestQuestion;
use tcCore\Test;
use tcCore\MulipleChoiceQuestion;
use Tests\TestCase;
use tcCore\Traits\Dev\CompletionQuestionTrait;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\GroupQuestionTrait;


class CompletionQuestionTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use CompletionQuestionTrait;
    use GroupQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;

    /** @test */
    public function can_create_test_and_completion_question(){
        $attributes = $this->getAttributesForTest();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getCompletionQuestionAttributes(['test_id'=>$this->originalTestId]);
        $this->createCompletionQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->completionQuestionAnswers));
        $this->assertCount(2,$questions->first()->question->completionQuestionAnswers);
    }

    /** @test */
    public function can_update_completion_question_answer(){
        $attributes = $this->getAttributesForTest();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getCompletionQuestionAttributes(['test_id'=>$this->originalTestId]);
        $this->createCompletionQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->completionQuestionAnswers));
        $this->assertCount(2,$questions->first()->question->completionQuestionAnswers);
        $originalQuestion = Test::find($this->originalTestId)->testQuestions->first();
        $attributes = $this->getAttributesForEditQuestion($this->originalTestId);
        $this->editCompletionQuestion($originalQuestion->uuid,$attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertCount(3,$questions->first()->question->completionQuestionAnswers);
    }



    /** @test */
    public function it_should_not_copy_questions_for_completion_if_nothing_is_changed()
    {
        $this->setupScenario();
        $tests = Test::where('name','TToets van GM7')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM7')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $attributes = $this->getCompletionQuestionAttributes(['test_id'=>$this->copyTestId]);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editCompletionQuestion($copyQuestion->uuid,$attributes);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
    }


    /** @test */
    public function it_should_copy_questionsForCompletion()
    {
        $this->setupScenario();
        $tests = Test::where('name','TToets van GM7')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM7')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $attributes = $this->getAttributesForEditQuestion($this->copyTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editCompletionQuestion($copyQuestion->uuid,$attributes);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)>0);
    }

    /** @test */
    public function it_should_copy_questions_when_completion_is_changed_in_group()
    {
        $this->setupScenario1();
        $this->originalAndCopyShareGroupQuestion();
        $attributes = $this->getAttributesForEditQuestion1($this->copyTestId);
        $copyGroupTestQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $this->editCompletionQuestionInGroup($copyGroupTestQuestion->uuid,$copyQuestion->uuid,$attributes);
        $this->originalAndCopyDifferFromGroupQuestion();
    }

    /** @test */
    public function it_should_copy_questions_when_completion_answers_is_changed_in_group()
    {
        $this->setupScenario1();
        $this->originalAndCopyShareGroupQuestion();
        $attributes = $this->getAttributesForEditQuestion2($this->copyTestId);
        $copyGroupTestQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $this->editCompletionQuestionInGroup($copyGroupTestQuestion->uuid,$copyQuestion->uuid,$attributes);
        $this->originalAndCopyDifferFromGroupQuestion();
    }

    /** @test */
    public function it_can_update_completion_answers_in_group()
    {
        $this->setupScenario1();
        $this->originalAndCopyShareGroupQuestion();
        $attributes = $this->getAttributesForEditQuestion2($this->copyTestId);
        $copyGroupTestQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $this->editCompletionQuestionInGroup($copyGroupTestQuestion->uuid,$copyQuestion->uuid,$attributes);
        $this->originalAndCopyDifferFromGroupQuestion();
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $mcAnswer = $copyQuestion->question->completionQuestionAnswers()->first();
        $this->assertEquals('ab',$mcAnswer->answer);
    }


    private function setupScenario(){
        $attributes = $this->getAttributesForTest();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getCompletionQuestionAttributes(['test_id'=>$this->originalTestId]);
        $this->createCompletionQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
    }

    private function setupScenario1(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForGroupQuestion($this->originalTestId);
        $groupTestQuestionId = $this->createGroupQuestion($attributes);
        $groupTestQuestion = TestQuestion::find($groupTestQuestionId);
        $attributes = $this->getCompletionQuestionAttributes(['test_id'=>$this->originalTestId]);
        $this->createCompletionQuestionInGroup($attributes,$groupTestQuestion->uuid);
        $this->duplicateTest($this->originalTestId);

        $this->checkScenario1Success('Test Title',$this->originalTestId);
        $this->checkScenario1Success('Kopie #1 Test Title',$this->copyTestId);

    }

    private function getAttributesForEditQuestion1($testId){
        return array_merge($this->getCompletionQuestionAttributes(['test_id'=>$testId,'score'=>'10']));
    }

    private function getAttributesForEditQuestion2($testId){
        return array_merge($this->getCompletionQuestionAttributes(['test_id'=>$testId,"question"=> '<p>lorum [ab] dolor [sit] amet, consectetur adipiscing elit</p>']));
    }

    private function checkScenario1Success($name,$testId){
        $tests = Test::where('name',$name)->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($testId)->testQuestions;
        $this->assertCount(1,$questions);
        $this->assertEquals('GroupQuestion',$questions->first()->question->type);
        $groupQuestion = $questions->first()->question;
        $subQuestions = $groupQuestion->groupQuestionQuestions;
        $this->assertCount(1,$subQuestions);
        $this->assertEquals('CompletionQuestion',$subQuestions->first()->question->type);
    }

    private function getAttributesForTest(){

        return $this->getTestAttributes([
            'name'                   => 'TToets van GM7',
            'abbreviation'           => 'TTGM7',
            'subject_id'             => '6',
            'introduction'           => 'intro',
        ]);

    }



    private function getGetAttributes($testId){
        return [
            "filter"=> [
                "test_id"=> $testId
            ],
            "mode"=> "all",
            "order"=> [
                "order"=> "asc"
            ]
        ];
    }


    private function getAttributesForEditQuestion($testId){
        $attributes = $this->getCompletionQuestionAttributes(['test_id'=>$testId, 'question'=> '<p>lorum [ipsum] dolor [sit] amet, consectetur [adipiscing] elit</p>']);
        return $attributes;
    }


    private function checkCopyQuestionsAfterEdit($copyTestId,$originalQuestionArray){
        $copyQuestions = Test::find($copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)>0);
    }
}


