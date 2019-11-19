<?php


namespace tcCore\Http\Helpers\QtiImporter;
use Illuminate\Support\Facades\DB;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\Question\Factory;
use tcCore\TestQuestion;


class SmallsourcesMultiplechoiceHelper extends QtiBaseQuestionHelper
{
    protected $type = 'CompletionQuestion';
    protected $subType = 'Multi';
    protected $convertedAr = [];

    public function validate($question)
    {
        if (!isset($question->question_content->question_body)) {
            throw new \Exception('question body niet gevonden');
        }

        if (!isset($question->question_content->question_answer)){
            throw new \Exception('question answer niet gevonden');
        }

        if (!isset($question['type'])){
            throw new \Exception('question type niet gevonden');
        }

        if (!isset($question->question_content->question_answer['score'])){
            throw new \Exception('question score niet gevonden');
        }
    }
    public function convert()
    {
        $obj = (object) $this->getQuestionAndAnswers();

        $this->convertedAr = [
            'question' => (string) $obj->question,
            'answer' => $obj->answers,
            'type'=> (string) $this->type,
            'score'=> (int) $obj->score,
            'order'=>0,
            'subtype'=> $this->subType,
            'maintain_position'=> '',
            'discuss'=> '',
            'decimal_score'=> '',
            'add_to_database'=> '',
            'attainments'=> '',
            'note_type'=> '',
            'is_open_source_content'=> ''
        ];
    }
    protected function getQuestionAndAnswers()
    {
        $question = (string) $this->question->question_content->question_body;
        $answerList = $this->question->question_content->question_answer;
        $nr = 1;
        $answers = collect([]);
        $tags = [];
        $maxScore = 0;
        foreach($answerList as $answerDetails){
            $score = (int) $answerDetails['score'];
            if($score > $maxScore) $maxScore = $score;
            if(strlen((string) $answerDetails->text) > 0) {
                $answers->push(collect([
                    'answer' => strip_tags((string)$answerDetails->text),
                    'tag' => $nr,
                    'correct' => ($score > 0 ) ? 1 : 0
                ]));
            }
        }

        if(substr_count($question,'<select') > 0){
            throw new \Exception('cann\'t handle this question '.$question);
        }
        else{
            $question = sprintf('%s<br />[%d]',$question,$nr);
        }

        return [
            'answers' => $this->orderAnswersByCorrect($answers)->toArray(),
            'question' => $question,
            'score' => $maxScore,
        ];
    }
}
