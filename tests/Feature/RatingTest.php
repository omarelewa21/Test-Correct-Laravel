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
use Tests\Traits\TestTrait;
use Tests\Traits\TestTakeTrait;
use Tests\Traits\GroupQuestionTrait;
use Tests\Traits\MultipleChoiceQuestionTrait;
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