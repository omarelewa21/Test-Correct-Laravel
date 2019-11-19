<?php


namespace tcCore\Http\Helpers\QtiImporter;
use Illuminate\Support\Facades\DB;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\Question\Factory;
use tcCore\TestQuestion;


class LargesourceWordHelper extends SmallsourcesWordHelper
{
//    protected $type = 'CompletionQuestion';
//    protected $subType = 'completion';
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
//        $obj = (object) $this->getQuestionAndAnswers();
//
//        $this->convertedAr = [
//            'question' => $obj->question,
//            'answer' => $obj->answers,
//            'type'=> (string) $this->type,
//            'score'=> (int) $this->question->question_content->question_answer['score'],
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
//
//    protected function getQuestionAndAnswers()
//    {
//        $question = (string) $this->question->question_content->question_body;
//        $_answerBlocks = $this->question->question_content->question_answer;
//        $answerBlocks = [];
//        $nr = 0;
//        $answers = collect([]);
//        $tags = [];
//        if(!is_array($_answerBlocks)){
//            $_answerBlocks = [$_answerBlocks];
//        }
//        foreach($_answerBlocks as $answerBlock){
////            $id = (string) $answerBlock['id'];
////            $tags[] = $id;
////            foreach($answerBlock->answer as $answer){
//            $answers ->push(collect([
//                'answer' => (string) $answerBlock->text,//$answer,
//                'tag' => 1,
//                'correct' => 1, // if there are multiple options they are all correct // ((int) $answer['weight'] === 1) ? 1 : 0
//            ]));
////            }
//        }
//
//        if(substr_count($question,'<span>`?`</span>') > 0) {
//            $question = str_replace('<span>`?`</span>', '[1]', $question);
//        }
//        else{
//            $question .= '[1]';
//        }
//
//        return [
//            'question' => $question,
//            'answers' => $answers->toArray()
//        ];
//    }
//
//    protected function everything_in_tags($tagname,$string )
//    {
//        $pattern = "#<\s*?$tagname\b[^>]*>(.*?)</$tagname\b[^>]*>#s";
//        preg_match_all($pattern, $string, $matches);
//        return $matches[0];
//    }
}