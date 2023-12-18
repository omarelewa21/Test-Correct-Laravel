<?php

namespace tcCore\View\Components\Answer\Student;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Http\Traits\Questions\WithCompletionConversion;
use tcCore\Question;
use tcCore\Word;

class RelationQuestion extends QuestionComponent
{
    public $answerStruct;
    public $questionStruct;

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
    }

    protected function setAnswerStruct($question, $answer): void
    {
        $studentAnswer = collect(json_decode($answer->json ?? '{}', true));

        $answerModelWords = Word::whereIn('id', $studentAnswer->keys())->get()->keyBy('id');
        $answerModelWordsCorrectWord = $answerModelWords->map->correctAnswerWord();

        $score_per_toggle = $answer->question->score / count($studentAnswer);

        $this->answerStruct = $studentAnswer->mapWithKeys(function($studentAnswerText, $wordId) use ($answerModelWords, $score_per_toggle, $answerModelWordsCorrectWord) {
            $questionPrefixTranslation = !in_array($answerModelWords[$wordId]?->type->value, ['subject', 'translation'])
                ? __('question.word_type_'.$answerModelWords[$wordId]?->type->value)
                : null;
//todo fix getInitialValueForWordId()
            return [
                $wordId => [
                    'answer'   => $studentAnswerText,
                    'question' => $answerModelWords[$wordId]->text,
                    'question_prefix' =>  $questionPrefixTranslation,
                    'not_answered' => $studentAnswerText === null || $studentAnswerText === '',
                    'initial_value' => !$this->inCoLearning ? $this->getInitialValueForWordId($wordId, $studentAnswerText, $answerModelWordsCorrectWord) : null,
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

        //todo add case or not case checking?
        //$question->auto_check_answer
        //$question->auto_check_answer_case_sensitive

//        if($question->auto_check_answer) {
//            return $question->auto_check_answer_case_sensitive
//                ? $answerModelWordsCorrectWord[$wordId]->text === $studentAnswer
//                : strtolower($answerModelWordsCorrectWord[$wordId]->text) === strtolower($studentAnswer);
//        }

        if($answerModelWordsCorrectWord[$wordId]->text === $studentAnswer) {
            return 1;
        }
//        if($question->auto_check_incorrect_answer) {

        return null; //$question->auto_check_incorrect_answer ? false : null;
    }
}