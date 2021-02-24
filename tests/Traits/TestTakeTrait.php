<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:04
 */

namespace Tests\Traits;

use tcCore\Test;
use tcCore\TestTake;
use tcCore\TestQuestion;
use tcCore\TestParticipant;
use tcCore\GroupQuestionQuestion;
use Carbon\Carbon;
use \stdClass;

trait TestTakeTrait
{

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;

	public function initDefaultTestTake($testId){
        $this->withoutExceptionHandling();
        $newTestTakeData = [
            'date'                => Carbon::now()->format('d-m-Y'),
            'period_id'           => 1,
            'invigilators'        => [1486],
            'class_id'            => 1,
            'test_id'             => $testId,
            'weight'              => 1,
            'invigilator_note'    => '',
            'time_start'          => Carbon::now()->format('Y-m-d H:i:s'),
            'retake'              => 0,
            'test_take_status_id' => 1,
            "school_classes"      => ["1"],
        ];
        $response = $this->post(
            'api-c/test_take',
            static::getTeacherOneAuthRequestData($newTestTakeData)
        );
        $response->assertStatus(200);
        return $response->decodeResponseJson()['id'];
	}

	public function initTestTakeForStudent($testTakeUuid,$testParticipantUuid){

        $data = [
            'test_take_status_id' => 3,
        ];
		$response = $this->put(
			sprintf('api-c/test_take/%s/test_participant/%s',$testTakeUuid,$testParticipantUuid),
            static::getStudentOneAuthRequestData($data)
		);
        $response->assertStatus(200);
	}

    public function initTestTakeForClass1($testTakeUuid){
        $testTake = TestTake::whereUuid($testTakeUuid)->firstOrFail();
        $testParticipants = $testTake->testParticipants();
        foreach ($testParticipants as $testParticipant) {
            $studentNumber = $this->getStudentNumber($testParticipant->user);
            $this->initTestTakeForStudentX($studentNumber,$testTakeUuid,$testParticipant->uuid);
            $this->makeFillAnswersScenario1($testTakeUuid,$testParticipant);
            $this->handInTestTakeForStudentX($studentNumber,$testTakeUuid,$testParticipant->uuid);
        }
        $this->TestTakeTaken($testTakeUuid);
    }

    public function initTestTakeForClass1WithSetAnswers($testTakeUuid,$answerArray)
    {
        $testTake = TestTake::whereUuid($testTakeUuid)->firstOrFail();
        $testParticipants = $testTake->testParticipants;
        foreach ($testParticipants as $testParticipant) {
            $studentNumber = $this->getStudentNumber($testParticipant->user);
            $this->initTestTakeForStudentX($studentNumber,$testTakeUuid,$testParticipant->uuid);
            $this->makeFillAnswersScenario1WithAnswers($testTakeUuid,$testParticipant,$answerArray[$studentNumber]);
            $this->handInTestTakeForStudentX($studentNumber,$testTakeUuid,$testParticipant->uuid);
        }
        $this->TestTakeTaken($testTakeUuid);
    }


    public function makeFillAnswersScenario1($testTakeUuid,$testParticipant){
        $studentNumber = $this->getStudentNumber($testParticipant->user);
        $answers = $testParticipant->answers;
        foreach ($answers as $answer) {
            $question = $answer->question;
            $mcAnswers = $question->multipleChoiceQuestionAnswers;
            $count = $mcAnswers->count();
            $count--; 
            $check = rand(0,$count);
            $obj = new stdClass();
            foreach ($mcAnswers as $key => $mcAnswer) {
                $value = 0;
                if($key==$check){
                    $value = 1;
                }
                $id = $mcAnswer->id;
                $obj->$id = $value;
            }
            $this->saveAnswer($studentNumber,$json,$question->id,$testTakeUuid,$testParticipant->uuid,$answer->uuid);
        }
    }

    public function makeFillAnswersScenario1WithAnswers($testTakeUuid,$testParticipant,$numberOfGoodAnswersArray){
        $studentNumber = $this->getStudentNumber($testParticipant->user);
        $answers = $testParticipant->answers;
        $numberOfGoodAnswersAll = $numberOfGoodAnswersArray['all'];
        $numberOfGoodAnswersCarousel = $numberOfGoodAnswersArray['carousel'];
        $numberOfGoodAnswersNormal = $numberOfGoodAnswersAll-$numberOfGoodAnswersCarousel;
        foreach ($answers as $answer) {
            $checkCarousel = false;
            $checkOther = false;
            if($numberOfGoodAnswersCarousel>0){
                $checkCarousel = true;
            }
            if($numberOfGoodAnswersOther>0){
                $checkOther = true;
            }
            $isCarousel = $this->answerBelongsToCarousel($answer);
            $question = $answer->question();
            $mcAnswers = $question->multipleChoiceQuestionAnswers;
            $obj = new stdClass();
            foreach ($mcAnswers as $key => $mcAnswer) {
                $value = 0;
                if($mcAnswer->score==5){
                    if($isCarousel&&$checkCarousel){
                        $value = 1;
                        $numberOfGoodAnswersCarousel--;
                    }elseif($checkOther){
                        $value = 1;
                        $numberOfGoodAnswersOther--;
                    }  
                }
                $id = $mcAnswer->id;
                $obj->$id = $value;
            }
            $this->saveAnswer($studentNumber,$json,$question->id,$testTakeUuid,$testParticipant->uuid,$answer->uuid);
        }
    }



    public function saveAnswer($studentNumber,$json,$questionId,$takeId,$testParticipantUuid,$answerUuid){
        $data = [
            "json"=> $json,
            "add_time"=> "20",
            "question_id"=> $questionId,
            "take_question_index"=> 0,
            "take_id"=> $takeId,
        ];
        $response = $this->put(
            sprintf('api-c/test_participant/%s/answer2019/%s?',$testParticipantUuid,$answerUuid),
            static::getStudentXAuthRequestData($data,$studentNumber)
        );
        $response->assertStatus(200);
    }

    public function initTestTakeForStudentX($studentNumber,$testTakeUuid,$testParticipantUuid){
        $data = [
            'test_take_status_id' => 3,
        ];
        dump(static::getStudentXAuthRequestData($data,$studentNumber));
        $response = $this->put(
            sprintf('api-c/test_take/%s/test_participant/%s',$testTakeUuid,$testParticipantUuid),
            static::getStudentXAuthRequestData($data,$studentNumber)
        );
        $response->assertStatus(200);
    }

    public function handInTestTakeForStudentX($studentNumber,$testTakeUuid,$testParticipantUuid){

        $data = [
            'test_take_status_id' => 4,
        ];
        $response = $this->put(
            sprintf('api-c/test_take/%s/test_participant/%s',$testTakeUuid,$testParticipantUuid),
            static::getStudentXAuthRequestData($data,$studentNumber)
        );
        $response->assertStatus(200);
    }

    public function TestTakeTaken($testTakeUuid){

        $data = [
            'test_take_status_id' => 6,
        ];
        $response = $this->put(
            sprintf('api-c/test_take/%s',$testTakeUuid),
            static::getTeacherOneAuthRequestData($data)
        );
        $response->assertStatus(200);
    }

    public function lost_focus_event($studentNumber,$testTakeUuid,$testParticipantId){
        $data = [
            'test_participant_id'=> $testParticipantId,
            'test_take_event_type_id'=> '3',
        ];
        $response = $this->post(
            sprintf('/api-c/test_take/%s/test_take_event?',$testTakeUuid),
            static::getStudentXAuthRequestData($data,$studentNumber)
        );
        $response->assertStatus(200);
    }

    public function getStudentNumber($user){
        switch($user->username){
            case 's1@test-correct.nl':
                return 1;
            break;
            case 's2@test-correct.nl':
                return 2;
            break;
            case 's3@test-correct.nl':
                return 3;
            break;
            case 's4@test-correct.nl':
                return 4;
            break;
            case 's5@test-correct.nl':
                return 5;
            break;
        }
    }

    public function setupToets1(){
        $attributes = $this->getTestAttributes();
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForCarouselGroupQuestion($this->originalTestId,10);
        $testQuestionId = $this->createGroupQuestion($attributes);
        $groupTestQuestion = TestQuestion::find($testQuestionId);
        $attributes = $this->getAttributesForMultipleChoiceQuestion($this->originalTestId);
        for ($i=0; $i < 20; $i++) {     
            $this->createMultipleChoiceQuestionInGroup($attributes,$groupTestQuestion->uuid);
        }
        for ($i=0; $i < 10; $i++) {     
            $this->createMultipleChoiceQuestion($attributes);
        }

        $testTakeId = $this->initDefaultTestTake($this->originalTestId);
        $testTake = TestTake::find($testTakeId);
        $this->toetsActiveren($testTake->uuid);
        return $testTake; 
    }

    public function answerBelongsToCarousel($answer)
    {
        $questionId = $answer->question_id;
        try{
            $groupQuestionQuestion = GroupQuestionQuestion::where('question_id',$questionId)->firstOrFail();
            $groupQuestion = $groupQuestionQuestion->groupQuestion();
            if(is_null($groupQuestion)){
                return false;
            }
            if($groupQuestion->groupquestion_type=='carousel'){
                return true;
            }
            return false;
        }catch(\Exception $e){
            return false;
        }

    }

}