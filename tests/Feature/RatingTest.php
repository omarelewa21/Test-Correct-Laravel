<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Answer;
use tcCore\User;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\Question;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\MulipleChoiceQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\TestTakeTrait;
use tcCore\Traits\Dev\GroupQuestionTrait;
use tcCore\Traits\Dev\MultipleChoiceQuestionTrait;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Helpers\ActingAsHelper;
use Illuminate\Support\Str;

class RatingTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use TestTakeTrait;
    use GroupQuestionTrait;
    use MultipleChoiceQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;


     /** @test */
     public function can_start_test_scenario1(){
        $testTake = $this->setupToets1();     
        $this->initTestTakeForClass1($testTake->uuid); 
     }

     /** @test */
     public function can_start_test_scenariox1_with_answers(){
        $testTake = $this->setupToets1();
        $answerArray = $this->getCorrectAnswersScenario1();
        $this->initTestTakeForClass1WithSetAnswers($testTake->uuid,$answerArray);
        
     }

    /** @test */
    public function rating_is_corrected_for_disabling_questions_in_group()
    {
        $this->setupToets2();
    }

    private function setupToets2()
    {
        $attributes = $this->getAttributesForTest();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $groupId = $this->addQuestionGroupAndReturnId($this->originalTestId);
        $this->addOpenQuestionToGroup($groupId);
        $this->addOpenQuestionToGroup($groupId);
        $this->addOpenQuestionToGroup($groupId);
        $groupId = $this->addQuestionGroupAndReturnId($this->originalTestId);
        $this->addOpenQuestionToGroup($groupId);
        $this->addOpenQuestionToGroup($groupId);
        $this->addOpenQuestionToGroup($groupId);

    }


    private function getAttributesForTest(){

        return $this->getTestAttributes([
            'name'                   => 'TToets van GM7',
            'abbreviation'           => 'TTGM7',
            'subject_id'             => '6',
            'introduction'           => 'intro',
        ]);

    }



    private function addOpenQuestionToGroup(int $groupId)
    {

        $response = $this->post(
            sprintf('group_question_question/%d', $groupId),
            static::getTeacherOneAuthRequestData(
                $this->getGroupOpenQuestionAttributes()
            )
        );

        $response->assertStatus(200);

        return $response->decodeResponseJson()['id'];
    }

    private function getGroupOpenQuestionAttributes(array $overrides = []): array
    {
        return array_merge([
            'question'               => '<p>vraag</p>\r\n',
            'answer'                 => '<p>antoord</p>\r\n',
            'type'                   => 'OpenQuestion',
            'score'                  => '5',
            'order'                  => 0,
            'subtype'                => 'short',
            'maintain_position'      => 0,
            'discuss'                => '1',
            'decimal_score'          => '0',
            'add_to_database'        => 1,
            'attainments'            => [],
            'note_type'              => 'NONE',
            'is_open_source_content' => 1,
            'tags'                   => [],
            'rtti'                   => null,
        ], $overrides);
    }

     private function getCorrectAnswersScenario1(){
        return [
            1 => [  'all' => 20,
                    'carousel' => 10
                    ],
            2 => [  'all' => 15,
                    'carousel' => 7
                    ],
            3 => [  'all' => 10,
                    'carousel' => 5
                    ],
            4 => [  'all' => 5,
                    'carousel' => 2
                    ],
            5 => [  'all' => 0,
                    'carousel' => 0
                    ],
        ];
     }


}