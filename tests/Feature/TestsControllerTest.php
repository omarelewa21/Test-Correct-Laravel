<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\MultipleChoiceQuestionTrait;
use Tests\Traits\OpenQuestionTrait;
use Tests\Traits\TestTrait;
use Tests\Traits\CompletionQuestionTrait;
use Tests\Traits\GroupQuestionTrait;

class TestsControllerTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use MultipleChoiceQuestionTrait;
    use OpenQuestionTrait;
    use CompletionQuestionTrait;
    use GroupQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;
    /**
     * @var mixed
     */
    private $groupTestQuestionId;

    /** @test */

    public function it_should_copy_questionsWhenModifyingTestSubjectId()
    {
        $this->setupScenario1();
        $tests = Test::where('name','TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $this->assertEquals(6,$copyQuestions->first()->question->subject_id);
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $copyTest = Test::find($this->copyTestId);
        $copyTest->subject_id = 1;
        $copyTest->save();
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)>0);
        $this->assertEquals(1,$copyQuestions->first()->question->subject_id);
    }

    /** @test */
    public function it_should_copy_questionsWhenModifyingTestEducationLevelId()
    {
        $this->setupScenario1();
        $tests = Test::where('name','TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $this->assertEquals(1,$copyQuestions->first()->question->education_level_id);
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $copyTest = Test::find($this->copyTestId);
        $copyTest->education_level_id = 3;
        $copyTest->save();
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)>0);
        $this->assertEquals(3,$copyQuestions->first()->question->education_level_id);
    }

    /** @test */
    public function it_should_copy_questionsWhenModifyingTestEducationLevelYear()
    {
        $this->setupScenario1();
        $tests = Test::where('name','TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $this->assertEquals(6,$copyQuestions->first()->question->subject_id);
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $copyTest = Test::find($this->copyTestId);
        $copyTest->education_level_year = 4;
        $copyTest->save();
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)>0);
        $this->assertEquals(4,$copyQuestions->first()->question->education_level_year);
    }

    /** @test */
    public function it_should_modify_questionsWhenModifyingTestSubjectIdOpenQuestion()
    {
        $this->setupScenario2();
        $tests = Test::where('name','TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $test = $tests->first();
        $test->subject_id = 1;
        $test->save();
        $questions = Test::find($this->originalTestId)->testQuestions;
        $question = $questions->first();
        $this->assertEquals(1,$question->question->subject_id);
    }

    /** @test */
    public function it_should_modify_questionsWhenModifyingTestSubjectIdMCQuestion()
    {
        $this->setupScenario3();
        $tests = Test::where('name','TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $test = $tests->first();
        $test->subject_id = 1;
        $test->save();
        $questions = Test::find($this->originalTestId)->testQuestions;
        $question = $questions->first();
        $this->assertEquals(1,$question->question->subject_id);
    }

    /** @test */
    public function it_should_modify_questionsWhenModifyingTestSubjectIdCompletionQuestion()
    {
        $this->setupScenario4();
        $tests = Test::where('name','TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $originalQuestion = $questions->first()->question->getQuestionHtml();
        $originalAnswers = $questions->first()->question->completionQuestionAnswers()->get();
        $this->assertTrue(count($questions)==1);
        $test = $tests->first();
        $test->subject_id = 1;
        $test->save();
        $questions = Test::find($this->originalTestId)->testQuestions;
        $question = $questions->first();
        $this->assertEquals(1,$question->question->subject_id);
        $this->assertEquals($originalQuestion,$question->question->getQuestionHtml());
        $modifiedAnswers = $question->question->completionQuestionAnswers()->get();
        foreach($originalAnswers as $key => $answer){
            $this->assertEquals($answer->answer,$modifiedAnswers[$key]->answer);
        }
    }

    /** @test */
    public function it_should_modify_questions_in_group_WhenModifyingTestSubjectId()
    {
        $this->setupScenario5();
        $test = Test::find($this->originalTestId);
        $test->subject_id = 6;
        $test->save();
        $groupTestQuestion = TestQuestion::find($this->groupTestQuestionId);
        foreach ($groupTestQuestion->question->groupQuestionQuestions as $groupQuestionQuestion){
            $this->assertEquals(6,$groupQuestionQuestion->question->subject_id);
        }
    }

    private function setupScenario1(){
        $attributes = $this->getAttributesForTest1();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForQuestion1($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
    }

    private function setupScenario2(){
        $attributes = $this->getAttributesForTest1();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $this->addOpenQuestionAndReturnQuestionId($this->originalTestId);
     }

    private function setupScenario3(){
        $attributes = $this->getAttributesForTest1();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForQuestion1($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
    }

    private function setupScenario4(){
        $attributes = $this->getAttributesForTest1();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getCompletionQuestionAttributes(['test_id'=>$this->originalTestId]);
        $this->createCompletionQuestion($attributes);
    }

    private function setupScenario5(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForGroupQuestion($this->originalTestId);
        $groupTestQuestionId = $this->createGroupQuestion($attributes);
        $this->groupTestQuestionId = $groupTestQuestionId;
        $groupTestQuestion = TestQuestion::find($groupTestQuestionId);
        $attributes = $this->getAttributesForMultipleChoiceQuestion($this->originalTestId);
        for($i=0;$i<10;$i++){
            $this->createMultipleChoiceQuestionInGroup($attributes,$groupTestQuestion->uuid);
        }
        $this->checkScenario5Success('Test Title',$this->originalTestId);
    }

    private function checkScenario5Success($name,$testId){
        $tests = Test::where('name',$name)->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($testId)->testQuestions;
        $this->assertCount(1,$questions);
        $this->assertEquals('GroupQuestion',$questions->first()->question->type);
        $groupQuestion = $questions->first()->question;
        $subQuestions = $groupQuestion->groupQuestionQuestions;
        $this->assertCount(10,$subQuestions);
        $this->assertEquals('MultipleChoiceQuestion',$subQuestions->first()->question->type);
    }

    private function getAttributesForTest1(){

        return $this->getTestAttributes([
            'name'                   => 'TToets van GM1',
            'abbreviation'           => 'TTGM1',
            'subject_id'             => '6',
            'introduction'           => 'intro',
            'education_level_year'   => 2,
            'education_level_id'   => 1
        ]);

    }

    private function getAttributesForEditQuestion1($testId){
        $attributes = array_merge($this->getAttributesForQuestion1($testId),[   "answers"=> [
            [
                "answer"=> "Juist",
                "score"=> 0,
                "order"=> 0
            ],
            [
                "answer"=> "Onjuist",
                "score"=> "5",
                "order"=> 0
            ]
        ],
        ]);
        unset($attributes["test_id"]);
        return $attributes;
    }

    private function getAttributesForQuestion1($testId){
        return [
            "type"=> "MultipleChoiceQuestion",
            "score"=> "5",
            "question"=> "<p>GM1</p> ",
            "order"=> 0,
            "maintain_position"=> "0",
            "discuss"=> "1",
            "subtype"=> "TrueFalse",
            "decimal_score"=> "0",
            "add_to_database"=> 1,
            "attainments"=> [
            ],
            "note_type"=> "NONE",
            "is_open_source_content"=> 1,
            "answers"=> [
                [
                    "answer"=> "Juist",
                    "score"=> "5",
                    "order"=> 0
                ],
                [
                    "answer"=> "Onjuist",
                    "score"=> 0,
                    "order"=> 0
                ]
            ],
            "tags"=> [
            ],
            "rtti"=> "R",
            "bloom"=> "Onthouden",
            "miller"=> "Weten",
            "test_id"=> $testId,
            "closeable"=> 0,
            "subject_id"=> 6,
            "education_level_year"=> 2,
            'education_level_id'   => 1
        ];
    }

    private function getOpenQuestionAttributes(array $overrides = []): array
    {
        return array_merge([
            'question'               => '<p>Question tekst</p>\r\n',
            'answer'                 => '<p>Answer Text</p>\r\n',
            'type'                   => 'OpenQuestion',
            'score'                  => '5',
            'order'                  => 0,
            'subtype'                => 'short',
            'maintain_position'      => '0',
            'discuss'                => '1',
            'decimal_score'          => '0',
            'add_to_database'        => 1,
            'attainments'            => [],
            'note_type'              => 'NONE',
            'is_open_source_content' => 1,
            'tags'                   => [],
            'rtti'                   => null,
            'test_id'                => '9',
            "closeable"=> 0,
            "subject_id"=> 6,
            "education_level_year"=> 2,
            'education_level_id'   => 1
        ], $overrides);
    }
}
