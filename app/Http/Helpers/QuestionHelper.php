<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:22
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Facades\Response;
use tcCore\CompletionQuestion;
use tcCore\CompletionQuestionAnswer;
use tcCore\CompletionQuestionAnswerLink;
use tcCore\Exceptions\QuestionException;
use tcCore\QuestionAuthor;
use tcCore\TestQuestion;

class QuestionHelper extends BaseHelper
{

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
                $answerItems = explode('|',$matches[1]);
                foreach($answerItems as $id => $answerItem) {
                    $obj->answers[] = [
                        'answer' => $answerItem,
                        'tag' => $obj->nr,
                        'correct' => ($id === 0) ? 1 : 0
                    ];
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