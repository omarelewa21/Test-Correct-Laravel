<?php


namespace tcCore\Http\Helpers\QtiImporter;
use Illuminate\Support\Facades\DB;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\Question\Factory;
use tcCore\TestQuestion;


class SmallsourcesTrueFalseHelper extends SmallsourcesMultiplechoiceHelper
{
//    protected $type = 'MultipleChoiceQuestion';
//    protected $subType = 'TrueFalse';
//    protected $convertedAr = [];
//
//    public function validate($question)
//    {
//        if (!isset($question->question_content->question_body)) {
//            throw new \Exception('question body niet gevonden');
//        }
//
//        if (!isset($question->question_content->question_answer)){
//            throw new \Exception('question answer niet gevonden');
//        }
//
//        if (!isset($question['type'])){
//            throw new \Exception('question type niet gevonden');
//        }
//
//        if (!isset($question->question_content->question_answer['score'])){
//            throw new \Exception('question score niet gevonden');
//        }
//    }
//    public function convert()
//    {
//        $obj = (object) $this->convertAnswers();
//        $this->convertedAr = [
//            'question' => (string) $this->question->question_content->question_body,
//            'answer' => $obj->answers,
//            'type'=> (string) $this->type,
//            'score'=> (int) $obj->score,
//            'order'=>0,
//            'subtype'=> $this->subType,
//            'maintain_position'=> '',
//            'discuss'=> '',
//            'decimal_score'=> '',
//            'add_to_database'=> '',
//            'attainments'=> '',
//            'note_type'=> '',
//            'is_open_source_content'=> ''
//        ];
//    }
//    protected function convertAnswers()
//    {
//        $answerList = $this->question->question_content->question_answer;
//        $answers = [];
//        $nr = 0;
//        $maxScore = 0;
//        foreach($answerList as $answerDetails){
//            $nr++;
//            $score = (int) $answerDetails['score'];
//            if($score > $maxScore){
//                $maxScore = $score;
//            }
//            $answers[] = [
//                'selectable_answers' => 1,
//                'subtype' => $this->subType,
//                'score' => $score,
//                'answer' => (string) $answerDetails->text,
//                'order' => $nr
//            ];
//        }
//
//        return [
//            'answers' => $answers,
//            'score' => $maxScore
//        ];
//    }
}
