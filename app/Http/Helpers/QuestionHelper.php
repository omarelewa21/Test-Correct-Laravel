<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:22
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\CompletionQuestion;
use tcCore\CompletionQuestionAnswer;
use tcCore\CompletionQuestionAnswerLink;
use tcCore\Exceptions\QuestionException;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\QuestionAuthor;
use tcCore\TestQuestion;

class QuestionHelper extends BaseHelper
{

    /**
     * method to get all related data for this question
     * @param $question
     * @return mixed
     */
    public function getTotalQuestion($question){
        $question->getQuestionInstance()->load(['attachments', 'attainments', 'authors', 'tags', 'pValue' => function($query) {
            $query->select('question_id', 'education_level_id', 'education_level_year', DB::raw('(SUM(score) / SUM(max_score)) as p_value'), DB::raw('count(1) as p_value_count'))->groupBy('education_level_id')->groupBy('education_level_year');
        }, 'pValue.educationLevel']);

        if($question instanceof QuestionInterface) {
            $question->loadRelated();
        }
        if($question->type == 'GroupQuestion' && $question->groupquestion_type=='carousel'){
            $question->score = $this->getTotalScoreForCarouselQuestion($question);
        }
        return $question;
    }

    public function getTotalScoreForCarouselQuestion($question)
    {
        $numberOfSubquestions = $question->number_of_subquestions;
        $groupQuestionQuestions = $question->groupQuestionQuestions()->orderBy('order', 'asc')->with('question')->get();
        if($groupQuestionQuestions->count() > 0) {
            $questionScore = $groupQuestionQuestions->first()->question->score;
            return (int) ($questionScore * $numberOfSubquestions);
        }
        return 0;
    }

    public function getQuestionStringAndAnswerDetailsForSavingCompletionQuestion($question)
    {
        $obj = (object) [
            'answers'   => [],
            'nr'        => 0
        ];
        $question = preg_replace_callback(
            '/\[(.*?)\]/i',
            function ($matches) use ($obj) {
                $obj->nr++;
                $questionMarkUsed = (bool) collect(explode('|',$matches[1]))->first(function($answer) {
                    return strpos($answer, '?') === 0;
                });

                $answerItems = explode('|',$matches[1]);
                foreach($answerItems as $id => $answerItem) {
                    if ($questionMarkUsed) {
                        $isCorrect = false;
                        if ($isCorrect = strpos($answerItem, '?') === 0) {
                            $answerItem = substr($answerItem, 1);
                        }

                        $obj->answers[] = [
                            'answer' => $answerItem,
                            'tag' => $obj->nr,
                            'correct' =>  $isCorrect
                        ];
                    } else {
                        $obj->answers[] = [
                            'answer' => $answerItem,
                            'tag' => $obj->nr,
                            'correct' => ($id === 0) ? 1 : 0
                        ];
                    }
                }
                return sprintf('[%d]',$obj->nr);
            },
            $question
        );
        return [
            'question'  => $question,
            'answers'   => $obj->answers
        ];
    }

    /**
     * @param $mainQuestion either TestQuestion or GroupQuestionQuestion
     * @param $answers
     * @return array
     * @throws \Exception
     */
    public function storeAnswersForCompletionQuestion($mainQuestion, $answers)
    {

        $question = $mainQuestion->question;
        if (($response = $this->validateCompletionQuestion($question)) !== true) {
            throw new QuestionException($response);
        } else {

            if ($question->isUsed($mainQuestion)) {
                $question = $question->duplicate([]);
                if ($question === false) {
                    throw new QuestionException('Failed to duplicate question');
                }
                $mainQuestion->setAttribute('question_id', $question->getKey());

                if (!$mainQuestion->save()) {
                    throw new QuestionException('Failed to update test question');
                }
            }

            if (!QuestionAuthor::addAuthorToQuestion($question)) {
                throw new QuestionException('Failed to attach author to question');
            }

            $returnAnswers = [];
            foreach($answers as $answerDetails) {
                $completionQuestionAnswer = new CompletionQuestionAnswer();

                $completionQuestionAnswer->fill($answerDetails);
                if (!$completionQuestionAnswer->save()) {
                    throw new QuestionException('Failed to create completion question answer');
                }

                $completionQuestionAnswerLink = new CompletionQuestionAnswerLink();
                $completionQuestionAnswerLink->setAttribute('completion_question_id', $question->getKey());
                $completionQuestionAnswerLink->setAttribute('completion_question_answer_id', $completionQuestionAnswer->getKey());

                if ($completionQuestionAnswerLink->save()) {
                    $returnAnswers[] = $completionQuestionAnswerLink;
                } else {
                    throw new QuestionException('Failed to create completion question answer link');
                }
            }
            return $returnAnswers;
        }
    }

    public function deleteCompletionQuestionAnswers(CompletionQuestion $question)
    {
        $question->completionQuestionAnswerLinks->each(function($cQAL){
            if (!$cQAL->delete()) {
                throw new QuestionException('Failed to delete completion question answer link', 500);
            }

            if ($cQAL->completionQuestionAnswer->isUsed($cQAL, false)) {
                throw new QuestionException(sprintf('Failed to delete the question answer, completionQuestionAnswer with id %d is still used',$cQAL->completionQuestionAnswer->id),500);
            } else {
                if (!$cQAL->completionQuestionAnswer->delete()) {
                    throw new QuestionException('Failed to delete completion question answer', 500);
                }
            }
        });
        return true;
    }

    /**
     * Perform pre-action checks
     * @param TestQuestion $question
     * @return bool
     */
    protected function validateCompletionQuestion($question) {
        if (!method_exists($question, 'completionQuestionAnswers')) {
            throw new QuestionException('Question does not allow completion question answers.', 404);
        }

        return true;
    }
}
