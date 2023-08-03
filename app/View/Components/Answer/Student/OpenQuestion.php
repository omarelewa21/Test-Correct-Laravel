<?php

namespace tcCore\View\Components\Answer\Student;

use Illuminate\Support\Str;
use tcCore\Http\Enums\AnswerFeedbackFilter;
use tcCore\Question;
use tcCore\Answer;
use function PHPUnit\Framework\stringContains;

class OpenQuestion extends QuestionComponent
{
    public string $answerValue;

    public function __construct(
        public Question             $question,
        public Answer               $answer,
        public string               $editorId,
        public bool                 $webSpellChecker = false,
        public string               $commentMarkerStyles = '',
        public bool                 $enableComments = false,
        public ?AnswerFeedbackFilter $answerFeedbackFilter = null,
    )
    {
        parent::__construct($question, $answer);
        $this->allowWsc = auth()->user()->schoolLocation->allow_wsc;
    }

    protected function setAnswerStruct($question, $answer): void
    {
        $this->answerValue = $answer->commented_answer ?? json_decode($this->answer->json)->value ?? '';

        $this->answerValue = Str::replace(
            chr(194).chr(160),
            " ".chr(194).chr(160),
            $this->answerValue
        );
    }
}