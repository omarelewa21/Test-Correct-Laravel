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
        //answerStruct contains student answer
        $this->answerStruct = collect(json_decode($answer->json ?? '{}', true));

        $this->questionStruct = Word::whereIn('id', $this->answerStruct->keys())->get()->keyBy('id');
    }
}