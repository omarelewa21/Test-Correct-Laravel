<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\GroupQuestionTrait;
use tcCore\Traits\Dev\OpenQuestionTrait;

class OpenQuestionTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use GroupQuestionTrait;
    use OpenQuestionTrait;


    /** @test */
    public function a_teacher_can_add_a_open_question_to_a_test_o()
    {
        $test = $this->createNewTest();
        $response = $this->post(
            '/api-c/test_question',
            static::getTeacherOneAuthRequestData([
                'question'               => '<p>aa</p>',
                'answer'                 => '<p>bb</p>',
                'type'                   => 'OpenQuestion',
                'score'                  => 5,
                'order'                  => 0,
                'subtype'                => 'short',
                'maintain_position'      => 0,
                'discuss'                => 1,
                'decimal_score'          => 0,
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => 'R',
                'test_id'                => $test['id'],
                'closeable'=> 0,
            ])
        );
        $response->assertStatus(200);
        $this->deleteTest($test);
    }

    /** @test */
    public function a_teacher_can_add_a_open_question_to_a_test_with_rtti_is_null()
    {
        $test = $this->createNewTest();

        $response = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData([
                'question'               => '<p>aa</p>',
                'answer'                 => '<p>bb</p>',
                'type'                   => 'OpenQuestion',
                'score'                  => 5,
                'order'                  => 0,
                'subtype'                => 'short',
                'maintain_position'      => 0,
                'discuss'                => 1,
                'decimal_score'          => 0,
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => null,
                'test_id'                => $test['id'],
                'closeable'=> 0,
            ])
        );
//        print_r($response->decodeResponseJson());die;

        $response->assertStatus(200);
        $this->deleteTest($test);
    }

    /** @test */
    public function a_teacher_cannot_add_a_open_question_to_a_test_with_an_invalid_rtti_value()
    {
        $test = $this->createNewTest();

        $response = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData([
                'question'               => '<p>aa</p>',
                'answer'                 => '<p>bb</p>',
                'type'                   => 'OpenQuestion',
                'score'                  => 5,
                'order'                  => 0,
                'subtype'                => 'short',
                'maintain_position'      => 0,
                'discuss'                => 1,
                'decimal_score'          => 0,
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => 'invalid',
                'test_id'                => $test['id'],
                'closeable'=> 0,
            ])
        );

        $response->assertStatus(422);
        $this->deleteTest($test);
    }

    /** @test */
    public function a_teacher_can_add_a_open_question_to_a_test_with_null_string_as_rtti_value()
    {
        $test = $this->createNewTest();
        $response = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData([
                'question'               => '<p>aa</p>',
                'answer'                 => '<p>bb</p>',
                'type'                   => 'OpenQuestion',
                'score'                  => 5,
                'order'                  => 0,
                'subtype'                => 'short',
                'maintain_position'      => 0,
                'discuss'                => 1,
                'decimal_score'          => 0,
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => '',
                'test_id'                => $test['id'],
                'closeable'=> 0,
            ])
        );
        $response->assertStatus(200);
        $this->deleteTest($test);
    }

    /** @test */
    public function it_should_copy_questions_when_open_question_is_changed_in_group()
    {
        $this->setupScenario1();
        $this->originalAndCopyShareGroupQuestion();
        $attributes = $this->getAttributesForEditQuestion1($this->copyTestId);
        $copyGroupTestQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $this->editOpenQuestionInGroup($copyGroupTestQuestion->uuid,$copyQuestion->uuid,$attributes);
        $this->originalAndCopyDifferFromGroupQuestion();
    }

    /** @test */
    public function it_should_copy_and_update_questions_when_open_question_is_changed_in_group()
    {
        $this->setupScenario1();
        $this->originalAndCopyShareGroupQuestion();
        $attributes = $this->getAttributesForEditQuestion1($this->copyTestId);
        $copyGroupTestQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $this->editOpenQuestionInGroup($copyGroupTestQuestion->uuid,$copyQuestion->uuid,$attributes);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $originalQuestion = Test::find($this->originalTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $this->assertEquals('<p>Question tekst</p>\r\n',$originalQuestion->question->getQuestionHtml());
        $this->assertEquals('Hoe dan?',$copyQuestion->question->getQuestionHtml());
        $this->assertEquals('Zo dus!',$copyQuestion->question->answer);
    }

    /** @test */
    public function it_should_not_copy_questions_when_open_question_is_reordered()
    {
        $this->setupScenario2();
        $this->originalAndCopyShareQuestion();
        $attributes = $this->getAttributesForEditQuestion2($this->copyTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->reorderQuestion($attributes,$copyQuestion);
        $this->originalAndCopyShareQuestion();
    }

    private function createNewTest($overrides = [])
    {
        $attributes = array_merge([
            'name'                   => 'Test Title 1abc',
            'abbreviation'           => 'TT',
            'test_kind_id'           => '3',
            'subject_id'             => '1',
            'education_level_id'     => '1',
            'education_level_year'   => '1',
            'period_id'              => '1',
            'shuffle'                => '0',
            'is_open_source_content' => '1',
            'introduction'           => 'Hello this is the intro txt',
        ], $overrides);

        $response = $this->post(
            'api-c/test',
            static::getTeacherOneAuthRequestData($attributes)
        );

        return $response->decodeResponseJson();
    }

    private function setupScenario1(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForGroupQuestion($this->originalTestId);
        $groupTestQuestionId = $this->createGroupQuestion($attributes);
        $groupTestQuestion = TestQuestion::find($groupTestQuestionId);
        $attributes = $this->getAttributesForOpenQuestion($this->originalTestId);
        $this->createOpenQuestionInGroup($attributes,$groupTestQuestion->uuid);
        $this->duplicateTest($this->originalTestId);

        $this->checkScenario1Success('Test Title',$this->originalTestId);
        $this->checkScenario1Success('Kopie #1 Test Title',$this->copyTestId);

    }

    private function checkScenario1Success($name,$testId){
        $test = Test::find($testId);
        $this->assertEquals($name,$test->name);
        $tests = Test::where('name',$name)->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($testId)->testQuestions;
        $this->assertCount(1,$questions);
        $this->assertEquals('GroupQuestion',$questions->first()->question->type);
        $groupQuestion = $questions->first()->question;
        $subQuestions = $groupQuestion->groupQuestionQuestions;
        $this->assertCount(1,$subQuestions);
        $this->assertEquals('OpenQuestion',$subQuestions->first()->question->type);
    }

    private function getAttributesForEditQuestion1($testId){
        return array_merge($this->getAttributesForOpenQuestion($testId),['question'=>'Hoe dan?','answer'=>'Zo dus!']);
    }

    private function setupScenario2(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForOpenQuestion($this->originalTestId);
        $this->originalQuestionId = $this->createGroupQuestion($attributes);
        $this->duplicateTest($this->originalTestId);

        $this->checkScenario2Success('Test Title',$this->originalTestId);
        $this->checkScenario2Success('Kopie #1 Test Title',$this->copyTestId);

    }

    private function checkScenario2Success($name,$testId){
        $test = Test::find($testId);
        $this->assertEquals($name,$test->name);
        $tests = Test::where('name',$name)->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($testId)->testQuestions;
        $this->assertCount(1,$questions);
        $this->assertEquals('OpenQuestion',$questions->first()->question->type);

    }

    private function getAttributesForEditQuestion2($testId){
        return array_merge($this->getAttributesForOpenQuestion($testId),['order'=>'5']);
    }
}
