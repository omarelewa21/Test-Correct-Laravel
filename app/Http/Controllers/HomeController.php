<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Queue;
use tcCore\Answer;
use tcCore\MultipleChoiceQuestion;
use tcCore\Question;
use tcCore\TestQuestion;
use tcCore\Jobs\PValues\CalculatePValueForAnswer;
use tcCore\Jobs\Rating\CalculateRatingForTestParticipant;
use tcCore\Jobs\Rating\CalculateRatingForUser;
use tcCore\TestParticipant;
use tcCore\User;
use tcCore\Lib\Question\Factory;

class HomeController extends Controller {

    public function index(){
        /*Answer::chunk(200, function($answers)
        {
            foreach ($answers as $answer)
            {
                Queue::push(new CalculatePValueForAnswer($answer));
            }
        });

        TestParticipant::chunk(200, function($testParticipants)
        {
            foreach ($testParticipants as $testParticipant)
            {
                Queue::push(new CalculateRatingForTestParticipant($testParticipant));
            }
        });;*/

        return 'Human detected; This page is meant for computers!';
    }

    public function test($id) {

        ini_set('display_errors',true);
        error_reporting(-1);

         $questiondata =  array (
          "question"=> "<p>asd</p>",
          "type"=> "MultipleChoiceQuestion",
          "order"=>0,
          "maintain_position"=> "0",
          "discuss"=> "1",
          "score"=>1,
          "subtype"=> "MultipleChoice",
          "decimal_score"=> "0",
          "add_to_database"=> "1",
          "attainments"=> [],
          "selectable_answers"=>1,
          "note_type"=> "NONE",
          "tags"=> [],
          "rtti"=> "null",
          "subject_id" => 51,
          "education_level_id" => 1,
          "education_level_year" => 1,
          "test_id" => $id,
          "authors" => new User()
        );


        $question = Factory::makeQuestion($questiondata['type']);
        if (!$question) {
            return Response::make('Failed to create question with factory', 500);
        }

        $testQuestion = new TestQuestion();
        $testQuestion->fill($questiondata);

        $test = $testQuestion->test;

        $question->fill($questiondata);

        Question::setAttributesFromParentModel($question, $test);

        // dd($question);

        if ($question->save()) {
            $testQuestion->setAttribute('question_id', $question->getKey());

            if ($testQuestion->save()) {
                return Response::make($testQuestion, 200);
            } else {
                return Response::make('Failed to create test question', 500);
            }
        } else {
            return Response::make('Failed to create question', 500);
        }


        // var_dump($id);
        // $question = MultipleChoiceQuestion::find($id);

        // dd($question);

        // dd($question->multipleChoiceQuestionAnswers);
    }

}