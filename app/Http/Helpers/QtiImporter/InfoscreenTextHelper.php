<?php
/**
 * Created by PhpStorm.
 * User: Carmen
 * Date: 3-5-2019
 * Time: 11:20
 */
namespace tcCore\Http\Helpers\QtiImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\Question\Factory;
use tcCore\Http\Requests\Request;
use tcCore\TestQuestion;

class InfoscreenTextHelper extends QtiBaseQuestionHelper
{
    protected $type = 'InfoscreenQuestion';
    protected $subType = 'nvt';
    protected $convertedAr = [];

    public function validate($question)
    {
        if (!isset($question->question_content->question_body)) {
            throw new \Exception('question body niet gevonden');
        }

        if (!isset($question['type'])){
            throw new \Exception('question type niet gevonden');
        }

    }

    public function convert()
    {
        $this->convertedAr = [
            'question' => (string) $this->question->question_content->question_body,
            'answer' => $this->convertAnswers(),
            'type'=> (string) $this->type,
            'score'=> (int) 0,
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
        $answers = 'niet van toepassing';
        if(is_string($answers)){
            $string = (string) $answers;
        }
        else{
            $a = [];
            foreach($answers as $answer){
                $a[] = (string) $answer;
            }
            $string = implode('<br />',$a);
        }
        return $string;
    }
}