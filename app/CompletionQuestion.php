<?php namespace tcCore;

use tcCore\Exceptions\QuestionException;
use tcCore\Lib\Question\QuestionInterface;

class CompletionQuestion extends Question implements QuestionInterface {

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'completion_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['rating_method', 'subtype'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function question() {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function completionQuestionAnswerLinks() {
        return $this->hasMany('tcCore\CompletionQuestionAnswerLink', 'completion_question_id');
    }

    public function completionQuestionAnswers() {
        return $this->belongsToMany(
            'tcCore\CompletionQuestionAnswer',
            'completion_question_answer_links',
            'completion_question_id',
            'completion_question_answer_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                $this->getDeletedAtColumn()
            ]
        )->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function loadRelated()
    {
        $this->load('completionQuestionAnswers');
    }

    public function duplicate(array $attributes, $ignore = null) {
        $question = $this->replicate();

        $question->parentInstance = $this->parentInstance->duplicate($attributes, $ignore);
        if ($question->parentInstance === false) {
            return false;
        }

        $question->fill($attributes);

        if ($question->save() === false) {
            return false;
        }

        foreach($this->completionQuestionAnswerLinks as $completionQuestionAnswerLink) {
            if ($ignore instanceof CompletionQuestionAnswer && $ignore->getKey() == $completionQuestionAnswerLink->getAttribute('completion_question_answer_id')) {
                continue;
            }

            if ($ignore instanceof CompletionQuestionAnswerLink
                && $ignore->getAttribute('completion_question_answer_id') == $completionQuestionAnswerLink->getAttribute('completion_question_answer_id')
                && $ignore->getAttribute('completion_question_id') == $completionQuestionAnswerLink->getAttribute('completion_question_id')) {
                continue;
            }

            if($completionQuestionAnswerLink->duplicate($question, []) === false) {
                return false;
            }
        }

        return $question;
    }

    public function canCheckAnswer() {
        if($this->isClosedQuestion()){
            return true;
        }

        $completionQuestionAnswers = $this->completionQuestionAnswers->groupBy('tag');
        $tags = [];

        if (!$completionQuestionAnswers) {
            return false;
        }

        foreach($completionQuestionAnswers as $tag => $choices) {
            if (count($choices) <= 1) {
                return false;
            }

            $hasCorrect = false;
            foreach ($choices as $choice) {
                if ($choice->getAttribute('correct') == 1) {
                    $hasCorrect = true;
                    break;
                }
            }

            if (!$hasCorrect) {
                return false;
            }
        }

        return true;
    }

    public function checkAnswer($answer) {
        $completionQuestionAnswers = $this->completionQuestionAnswers->groupBy('tag');
        foreach($completionQuestionAnswers as $tag => $choices) {
            $answers = [];
            foreach ($choices as $choice) {
                if ($choice->getAttribute('correct') == 1) {
                    $answers[] = $choice->getAttribute('answer');
                }
            }
            $completionQuestionAnswers[$tag] = $answers;
        }

        $answers = json_decode($answer->getAttribute('json'), true);
        if(!$answers) {
            return 0;
        }

        $correct = 0;
        foreach($completionQuestionAnswers as $tag => $tagAnswers) {
            if (!array_key_exists($tag, $answers)) {
                continue;
            }

            if (in_array($answers[$tag], $tagAnswers)) {
                $correct++;
            }
        }

        if($this->allOrNothingQuestion()){
            if($correct == count($completionQuestionAnswers)){
                return $this->score;
            } else {
                return 0;
            }
        }


        $score = $this->getAttribute('score') * ($correct / count($completionQuestionAnswers));
        if ($this->getAttribute('decimal_score') == true) {
            $score = floor($score * 2) / 2;
        } else {
            $score = floor($score);
        }

        return $score;
    }

    public function deleteAnswers(){
        $this->completionQuestionAnswerLinks->each(function($cQAL){
            if (!$cQAL->delete()) {
                throw new QuestionException('Failed to delete completion question answer link', 422);
            }

            if ($cQAL->completionQuestionAnswer->isUsed($cQAL)) {
                // all okay, this one should be kept
//                throw new QuestionException(sprintf('Failed to delete the question answer, completionQuestionAnswer with id %d is still used',$cQAL->completionQuestionAnswer->id),422);
            } else {
                if (!$cQAL->completionQuestionAnswer->delete()) {
                    throw new QuestionException('Failed to delete completion question answer', 422);
                }
            }
        });
        return true;
    }

    /**
     * @param $mainQuestion either TestQuestion or GroupQuestionQuestion
     * @param $answers
     * @return boolean
     * @throws \Exception
     */
    public function addAnswers($mainQuestion,$answers){
        $question = $this;
////        if ($question->isDirty() || $mainQuestion->isDirty() || $mainQuestion->isDirtyAttainments() || $mainQuestion->isDirtyTags() || ($question instanceof DrawingQuestion && $question->isDirtyFile())) {
////
////            if ($this->isUsed($mainQuestion)) {
//                $question = $this->duplicate([]);
//                if ($question === false) {
//                    throw new QuestionException('Failed to duplicate question', 422);
//                }
//                $mainQuestion->setAttribute('question_id', $question->getKey());
//
//                if (!$mainQuestion->save()) {
//                    throw new QuestionException('Failed to update test question', 422);
//                }
////            }
////        }

        if (!QuestionAuthor::addAuthorToQuestion($question)) {
            throw new QuestionException('Failed to attach author to question',422);
        }

        $returnAnswers = [];
        foreach($answers as $answerDetails) {
            $completionQuestionAnswer = new CompletionQuestionAnswer();

            $answerDetails['answer'] = html_entity_decode($answerDetails['answer']);//str_replace(['&eacute;','&euro;','&euml;','&nbsp;','&oacute;'],['é','€','ë',' ','ó'],$answerDetails['answer']);

            $completionQuestionAnswer->fill($answerDetails);
            if (!$completionQuestionAnswer->save()) {
                throw new QuestionException('Failed to create completion question answer',422);
            }

            $completionQuestionAnswerLink = new CompletionQuestionAnswerLink();
            $completionQuestionAnswerLink->setAttribute('completion_question_id', $question->getKey());
            $completionQuestionAnswerLink->setAttribute('completion_question_answer_id', $completionQuestionAnswer->getKey());

            if (!$completionQuestionAnswerLink->save()) {
                throw new QuestionException('Failed to create completion question answer link',422);
            }
        }
        return true;
    }

//    /**
//     * transform if needed for test, meaning that if there are no
//     * answers available and it is a completion question, it means
//     * that there's still some transformation needed towards
//     * [aa] => [1]
//     */
//    public function transformIfNeededForTest()
//    {
//        if($this->subtype == 'completion' && $this->completionQuestionAnswerLinks->count() === 0) {
//            $count = (object)['nr' => 0];
//            $this->getQuestionInstance()->question = preg_replace_callback(
//                '/\[(.*?)\]/i',
//                function ($matches) use ($count) {
//                    $count->nr++;
//                    return '[' . $count->nr . ']';
//                },
//                $this->getQuestionInstance()->question
//            );
//        }
//    }
}
