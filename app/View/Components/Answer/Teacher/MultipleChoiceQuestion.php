<?php

namespace tcCore\View\Components\Answer\Teacher;


use tcCore\MultipleChoiceQuestionAnswer;

class MultipleChoiceQuestion extends QuestionComponent
{
    public mixed $answerStruct;
    public array $arqStructure = [];

    protected function setAnswerStruct($question): void
    {
        if ($question->isSubType('ARQ')) {
            $this->arqStructure = \tcCore\MultipleChoiceQuestion::getArqStructure();
        }
        $this->answerStruct = $question->getCorrectAnswerStructure()
            ->map(function ($link) {
                $link->active = $link->score > 0;
                return $link;
            });
    }
}