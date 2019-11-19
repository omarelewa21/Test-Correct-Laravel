<?php


namespace tcCore\Http\Helpers\QtiImporter;
use Illuminate\Support\Facades\DB;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\Question\Factory;
use tcCore\TestQuestion;

class ArangeTextHelper extends QtiBaseQuestionHelper
{
    protected $type = 'RankingQuestion';
    protected $subType = 'ranking';

    public function validate($question)
    {
        if (!isset($question->question_content->question_body)) {
            throw new \Exception('question body niet gevonden');
        }

        if (!isset($question->question_content->question_answer)) {
            throw new \Exception('question answer niet gevonden');
        }

        if (!isset($question['type'])) {
            throw new \Exception('question type niet gevonden');
        }

        if (!isset($question->question_content->question_answer['score'])) {
            throw new \Exception('question score niet gevonden');
        }
    }

    public function convert()
    {
        $this->convertedAr = [
            'question' => $this->question->question_content->question_body,
            'answer' => $this->convertAnswers(),
            'type' => $this->type,
            'score' => $this->question->question_content->question_answer['score'],
            'order' => 0,
            'subtype' => $this->subType,
            'maintain_position' => '',
            'discuss' => '',
            'decimal_score' => '',
            'add_to_database' => '',
            'attainments' => '',
            'note_type' => '',
            'is_open_source_content' => '',
            'random_order' => 1
        ];
    }


    protected function convertAnswers()
    {
        $answerBlocks = $this->question->question_content->question_answer->text;
        $answers = [];
        $nr=0;
        foreach($answerBlocks as $answer){
            $nr++;
            $answers[] = [
                'answer' => (string) $answer,
                'order' => $nr,
                'correct_order' => $nr
            ];
        }


        return $answers;
    }
}