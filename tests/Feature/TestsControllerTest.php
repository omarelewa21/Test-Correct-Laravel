<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\MultipleChoiceQuestionTrait;
use tcCore\Traits\Dev\OpenQuestionTrait;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\CompletionQuestionTrait;
use tcCore\Traits\Dev\GroupQuestionTrait;
use tcCore\Traits\Dev\TestTakeTrait;

class TestsControllerTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use MultipleChoiceQuestionTrait;
    use OpenQuestionTrait;
    use CompletionQuestionTrait;
    use GroupQuestionTrait;
    use TestTakeTrait;

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
    public function it_should_modify_questionsWhenModifyingTestSubjectIdCompletionQuestionInGroup()
    {
        $this->setupScenario6();
        $test = Test::find($this->originalTestId);
        $groupTestQuestion = TestQuestion::find($this->groupTestQuestionId);
        $originalQuestion = $groupTestQuestion->question->groupQuestionQuestions->first()->question->getQuestionHtml();
        $originalAnswers = $groupTestQuestion->question->groupQuestionQuestions->first()->question->completionQuestionAnswers()->get();
        $this->assertCount(10,$groupTestQuestion->question->groupQuestionQuestions);
        $test->subject_id = 6;
        $test->save();
        $groupTestQuestion = TestQuestion::find($this->groupTestQuestionId);
        $question = $groupTestQuestion->question->groupQuestionQuestions->first();
        $this->assertEquals(6,$question->question->subject_id);
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

    /** @test */
    public function it_should_modify_questions_in_group_in_copied_test_WhenModifyingTestSubjectId()
    {
        $this->setupScenario7();
        $test = Test::find($this->copyTestId);
        $test->subject_id = 6;
        $test->save();
        $this->checkGroupQuestionsOriginalAndCopyDoNotShareQuestions();
        $groupTestQuestion = $test->testQuestions->first();
        foreach ($groupTestQuestion->question->groupQuestionQuestions as $groupQuestionQuestion){
            $this->assertEquals(6,$groupQuestionQuestion->question->subject_id);
        }
    }

    /** @test */
    public function it_should_copy_test_when_test_is_taken()
    {
        $testId = 1;
        $questionId = 11;
        $test = Test::find($testId);
        $systemTestId = $test->system_test_id;
        $this->assertNull($systemTestId);
        $origCount = Test::where('name',$test->name)->count();
        $testQuestion = TestQuestion::where('question_id',$questionId)->where('test_id',$testId)->firstOrFail();
        $uuidTestQuestion = $testQuestion->uuid;
        $testTakeId = $this->initDefaultTestTake($testId);
        $testTake = TestTake::find($testTakeId);
        $testTakeUuid = $testTake->uuid;
        $this->initTestTakeForClass1($testTakeUuid);
//        $attributes = $this->getOpenQuestionAttributes(['test_id'=>$testId,'question'=>'open vraag van GM']);
//        $this->editOpenQuestion($uuidTestQuestion,$attributes);
//        $question = Question::where('derived_question_id',11)->first();
//        $this->assertNotNull($question);
//        $this->assertEquals('open vraag van GM',$question->getQuestionInstance()->question);
        $systemTestId = Test::find($testId)->system_test_id;
        $this->assertNotNull($systemTestId);
        $newCount = Test::where('name',$test->name)->count();
        $this->assertEquals(($origCount+1),$newCount);
//        $question = Question::find(11);
//        $this->assertEquals('<p>Open kort</p>',trim($question->getQuestionInstance()->question));
    }

    /** @test */

    public function itShouldExecuteTestsControllerIndexWithoutErrors()
    {
        $attributes = [ "results"    => "60",
                        "page"      => "1",
                        "order"     => [
                                        "id" => "desc"
                                        ]
                        ];
//        $getRequest = self::authUserGetRequest(
//            'api-c/test',
//            $attributes,
//            User::where('username', 'm.grunbauer@atscholen.nl')->first()
//        );
//        $response = $this->get($getRequest);
//        $response->assertStatus(200);
        $response = $this->get($this->authTeacherOneGetRequest('api-c/test',$attributes));
        $response->assertStatus(200);
        $user = User::where('username', 'd1@test-correct.nl')->first();

        $user->school_id = 1;
        $user->save();
        $response = $this->get($this->authTeacherOneGetRequest('api-c/test',$attributes));
        $response->assertStatus(200);
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

    private function setupScenario6(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForGroupQuestion($this->originalTestId);
        $groupTestQuestionId = $this->createGroupQuestion($attributes);
        $this->groupTestQuestionId = $groupTestQuestionId;
        $groupTestQuestion = TestQuestion::find($groupTestQuestionId);
        $attributes = $this->getCompletionQuestionAttributes(['test_id'=>$this->originalTestId]);
        for($i=0;$i<10;$i++){
            $this->createCompletionQuestionInGroup($attributes,$groupTestQuestion->uuid);
        }
        $this->checkScenario6Success('Test Title',$this->originalTestId);
    }

    private function setupScenario7(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForGroupQuestion($this->originalTestId);
        $groupTestQuestionId = $this->createGroupQuestion($attributes);
        $this->groupTestQuestionId = $groupTestQuestionId;
        $groupTestQuestion = TestQuestion::find($groupTestQuestionId);
        $attributes = $this->getAttributesForMultipleChoiceQuestion($this->originalTestId);
        $this->createMultipleChoiceQuestionInGroup($attributes,$groupTestQuestion->uuid);
        $attributes = $this->getCompletionQuestionAttributes(['test_id'=>$this->originalTestId]);
        $this->createCompletionQuestionInGroup($attributes,$groupTestQuestion->uuid);
        $this->duplicateTest($this->originalTestId);
        $this->checkScenario7Success('Test Title',$this->originalTestId);
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

    private function checkScenario6Success($name,$testId){
        $tests = Test::where('name',$name)->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($testId)->testQuestions;
        $this->assertCount(1,$questions);
        $this->assertEquals('GroupQuestion',$questions->first()->question->type);
        $groupQuestion = $questions->first()->question;
        $subQuestions = $groupQuestion->groupQuestionQuestions;
        $this->assertCount(10,$subQuestions);
        $this->assertEquals('CompletionQuestion',$subQuestions->first()->question->type);
    }

    private function checkScenario7Success($name,$testId){
        $tests = Test::where('name',$name)->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($testId)->testQuestions;
        $this->assertCount(1,$questions);
        $this->assertEquals('GroupQuestion',$questions->first()->question->type);
        $this->checkGroupQuestionsOriginalAndCopyShareQuestions();
    }

    private function checkGroupQuestionsOriginalAndCopyShareQuestions(){
        $subQuestionIds = $this->getOriginalQuestionIds(2);
        $copySubQuestionIds = $this->getCopyQuestionIds(2);
        $this->assertEquals($subQuestionIds,$copySubQuestionIds);
    }

    private function checkGroupQuestionsOriginalAndCopyDoNotShareQuestions(){
        $subQuestionIds = $this->getOriginalQuestionIds(2);
        $copySubQuestionIds = $this->getCopyQuestionIds(2);
        foreach ($copySubQuestionIds as $subQuestionId){
            $this->assertFalse(in_array($subQuestionId,$subQuestionIds));
        }
    }

    private function getOriginalQuestionIds($expectedNumberSubquestions)
    {
        $questions = Test::find($this->originalTestId)->testQuestions;
        $subQuestionIds = $this->getSubquestionIds($questions, $expectedNumberSubquestions);
        return $subQuestionIds;
    }

    private function getCopyQuestionIds($expectedNumberSubquestions)
    {
        $questions = Test::find($this->copyTestId)->testQuestions;
        $subQuestionIds = $this->getSubquestionIds($questions, $expectedNumberSubquestions);
        return $subQuestionIds;
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

    /**
     * @param $questions
     * @param $expectedNumberSubquestions
     * @return mixed
     */
    private function getSubquestionIds($questions, $expectedNumberSubquestions)
    {
        $groupQuestion = $questions->first()->question;
        $subQuestions = $groupQuestion->groupQuestionQuestions;
        $subQuestionIds = $groupQuestion->groupQuestionQuestions->map(function ($item, $key) {
            return $item->question->id;
        })->toArray();
        $this->assertCount($expectedNumberSubquestions, $subQuestions);
        return $subQuestionIds;
    }
}
