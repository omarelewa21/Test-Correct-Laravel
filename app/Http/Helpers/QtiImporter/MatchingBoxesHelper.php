<?php


namespace tcCore\Http\Helpers\QtiImporter;
use Illuminate\Support\Facades\DB;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\Question\Factory;
use tcCore\TestQuestion;

class MatchingBoxesHelper extends QtiBaseQuestionHelper
{
    protected $type = 'MatchingQuestion';
    protected $subType = 'Matching';
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
        $this->convertedAr = [
            'question' => (string) $this->question->question_content->question_body,
            'answer' => $this->convertAnswers(),
            'type'=> (string) $this->type,
            'score'=> (int) $this->question->question_content->question_answer['score'],
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

    protected function convertAnswers()
    {
        $answersLeft = $this->question->question_content->question_answer->textleft;
        $answersRight = $this->question->question_content->question_answer->textright;
        $answers = [];
        $nr = 0;
        foreach($answersLeft as $answer){
            $order = $nr+1;
            $answers[] = [
              'left' => (string) $answer,
              'right' => (string) $answersRight[$nr],
              'order' => 1
            ];
            $nr++;
        }

        return $answers;
}

}