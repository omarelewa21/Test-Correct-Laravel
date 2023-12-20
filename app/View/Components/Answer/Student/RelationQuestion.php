<?php

namespace tcCore\View\Components\Answer\Student;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Traits\Questions\WithCompletionConversion;
use tcCore\Question;
use tcCore\Word;

class RelationQuestion extends QuestionComponent
{
    public $answerStruct;
    public $questionStruct;
    public bool $inReview;

    public function __construct(
        public Question      $question,
        public Answer        $answer,
        public bool          $disabledToggle = false,
        public bool          $showToggles = true,
        public bool          $inAssessment = false,
        public bool          $inCoLearning = false,
        public ?AnswerRating $answerRating = null,
    ) {
        parent::__construct($question, $answer);
        
        $this->inReview = !$this->inAssessment && !$this->inCoLearning;
    }

    protected function setAnswerStruct($question, $answer): void
    {
        $studentAnswer = collect(json_decode($answer->json ?? '{}', true));

        $answerModelWords = Word::whereIn('id', $studentAnswer->keys())->get()->keyBy('id');
        $answerModelWordsCorrectWord = $answerModelWords->map->correctAnswerWord();

        $score_per_toggle = $answer->question->score / count($studentAnswer);

        $question->canCreateSystemRatingForAnswer($answer);

        $this->answerStruct = $studentAnswer->mapWithKeys(function($studentAnswerText, $wordId) use ($answerModelWords, $score_per_toggle, $answerModelWordsCorrectWord) {
            $questionPrefixTranslation = !in_array($answerModelWords[$wordId]?->type->value, ['subject', 'translation'])
                ? __('question.word_type_'.$answerModelWords[$wordId]?->type->value)
                : null;

            return [
                $wordId => [
                    'answer'   => $studentAnswerText,
                    'question' => $answerModelWords[$wordId]->text,
                    'question_prefix' =>  $questionPrefixTranslation,
                    'not_answered' => $studentAnswerText === null || $studentAnswerText === '',
                    'initial_value' => $this->getInitialValueForWordId($wordId, $studentAnswerText, $answerModelWordsCorrectWord),
                    'toggle_value' => $score_per_toggle,
                ]
            ];

        });
    }

    protected function getInitialValueForWordId($wordId, $studentAnswer, $answerModelWordsCorrectWord)
    {
        if(isset($this->answerRating->json[$wordId]) && ($this->answerRating->json[$wordId] !== null || $this->answerRating->json[$wordId] !== '')) {
            return $this->answerRating->json[$wordId];
        }

        if(
            (!$this->inAssessment && !isset($this->answer->allAnswerFieldsCorrect) && !$this->answer->allAnswerFieldsCorrect)
            || $this->inCoLearning
            || !$studentAnswer
        ) {
            return;
        }

        return QuestionHelper::compareTextAnswers(
            $studentAnswer,
            $answerModelWordsCorrectWord[$wordId]->text,
            $this->question->auto_check_answer_case_sensitive
        ) ? true : null;
    }
}