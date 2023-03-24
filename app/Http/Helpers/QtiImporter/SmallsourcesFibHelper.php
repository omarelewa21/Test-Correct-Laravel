<?php


namespace tcCore\Http\Helpers\QtiImporter;

use DOMDocument;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\Question\Factory;
use tcCore\TestQuestion;


class SmallsourcesFibHelper extends QtiBaseQuestionHelper
{
    protected $type = 'CompletionQuestion';
    protected $subType = 'completion';
    protected $convertedAr = [];

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
        $obj = (object) $this->getQuestionAndAnswers();

        $this->convertedAr = [
            'question'               => $obj->question,
            'answer'                 => $obj->answers,
            'type'                   => (string) $this->type,
            'score'                  => (int) $this->question->question_content->question_answer['score'],
            'order'                  => 0,
            'subtype'                => $this->subType,
            'maintain_position'      => '',
            'discuss'                => '',
            'decimal_score'          => '',
            'add_to_database'        => '',
            'attainments'            => '',
            'note_type'              => 'NONE',
            'is_open_source_content' => ''
        ];
    }

    protected function getQuestionAndAnswers()
    {
        $question = (string) $this->question->question_content->question_body;
        $question = str_replace('</input></input>', '</input>', $question);
        $_answerBlocks = $this->question->question_content->question_responseprocessing->response;

        $answers = collect([]);
        $tags = [];


        $list = $this->everything_in_tags('select', $question);
        if (count($list) > 0) {
            if (is_array($_answerBlocks) || is_object($_answerBlocks)) {
                foreach ($_answerBlocks as $answerBlock) {
                    $id = (string) $answerBlock['id'];
                    $tags[] = $id;
                    foreach ($answerBlock->answer as $answer) {
                        if (strlen((string) $answer) > 0) {
                            $answers->push(collect([
                                'answer'  => strip_tags((string) $answer),
                                'tag'     => $id,
                                'correct' => ((int) $answer['weight'] === 1) ? 1 : 0
                            ]));
                        }
                    }
                    $answers = $this->orderAnswersByCorrect($answers);
                }
            } else {
                throw new \Exception(sprintf('no answers found for %s', $question));
            }

            foreach ($list as $nr => $tagItem) {
                $nr++;
                foreach ($tags as $tag) {
                    if (substr_count($tagItem, $tag) > 0) {
                        $question = str_replace($tagItem, '['.$nr.']', $question);
                        $answers->each(function ($answer) use ($nr, $tag) {
                            if ($answer['tag'] == $tag) {
                                $answer['tag'] = $nr;
                            }
                        });
                    }
                }
            }
        } else {
            $dom = new DOMDocument;
            if (substr($question, 0, 1) != '<') {
                $question = sprintf('<div>%s</div>', $question);
            }

            try {
                $dom->loadXML($question);
            } catch (\Exception $e) {
                throw new \Exception($e);
            }

//            $dom->loadXML($question);//HTML($question, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $list = $dom->getElementsByTagName('input');

            for ($n = $list->length - 1; $n >= 0; --$n) {
                $nr = $n + 1;
                $select = $list[$n];
                $answerList = $select->getElementsByTagName('answer');


                foreach ($answerList as $a) {
//                    if(strlen($a->nodeValue) > 0) {
                    $answers->push(collect([
                        'answer'  => $a->nodeValue,
                        'tag'     => $nr,
                        'correct' => 1
                    ]));
//                    }
                }

                $answers = $this->orderAnswersByCorrect($answers);

                $t = $dom->createElement('span');
                $t->appendChild($dom->createTextNode('['.$nr.']'));
                $select->parentNode->replaceChild($t, $select);
            }
            $question = $dom->saveHTML();
          //  $dom = null;

        }
        if ($answers->count() < 1) {
            throw new \Exception((string) $this->question->question_content->question_body);
        }

        foreach ($this->question->question_content->children() as $tag) {
            if (get_class($tag) == 'SimpleXMLElement' && $tag->getName() === 'question_large_sourcetext') {
                $blockNote = (string) $this->question->question_content->question_large_sourcetext;
                if (strlen($blockNote)) {
                    $question .= sprintf('<p style="font-size:10px">%s</p>', $blockNote);
                }
            }
        }

        $question = $this->appendQuestionSourceAsImageToQuestion($question);

        return [
            'question' => $question,
            'answers'  => $answers->toArray()
        ];
    }

    protected function everything_in_tags($tagname, $string)
    {
        $pattern = "#<\s*?$tagname\b[^>]*>(.*?)</$tagname\b[^>]*>#s";
        preg_match_all($pattern, $string, $matches);
        return $matches[0];
    }
}
