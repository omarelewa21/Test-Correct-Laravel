<?php


namespace tcCore\Http\Helpers\QtiImporter;
use Illuminate\Support\Facades\DB;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\Question\Factory;
use tcCore\TestQuestion;


class LargesourceComboHelper extends SmallsourcesComboHelper
{
//    protected $type = 'CompletionQuestion';
//    protected $subType = 'multi';
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
//        $_answerBlocks = $this->question->question_content->question_responseprocessing->response;
//        $answers = collect([]);
//        $tags = [];
//        if(!is_array($_answerBlocks)){
//         throw new \Exception(sprintf('no answers found for %s',$question));
//        }else {
//            foreach ($_answerBlocks as $answerBlock) {
//                $id = (string)$answerBlock['id'];
//                $tags[] = $id;
//                foreach ($answerBlock->answer as $answer) {
//                    $answers->push(collect([
//                        'answer' => (string)$answer,
//                        'tag' => $id,
//                        'correct' => ((int)$answer['weight'] === 1) ? 1 : 0
//                    ]));
//                }
//            }
//        }
//
//        $list = $this->everything_in_tags('select',$question);
//        foreach($list as $nr => $tagItem){
//            $nr++;
//            foreach($tags as $tag){
//                if(substr_count($tagItem,$tag) > 0){
//                    $question = str_replace($tagItem,'['.$nr.']',$question);
//                    $answers->each(function($answer) use ($nr,$tag){
//                        if($answer['tag'] == $tag){
//                            $answer['tag'] = $nr;
//                        }
//                    });
//                }
//            }
//        }
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