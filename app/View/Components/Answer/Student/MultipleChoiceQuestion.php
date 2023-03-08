<?php

namespace tcCore\View\Components\Answer\Student;

class MultipleChoiceQuestion extends QuestionComponent
{
    public mixed $answerStruct;
    public array $arqStructure = [];

    protected function setAnswerStruct($question, $answer): void
    {
        $givenAnswerId = collect(json_decode($answer->json))
            ->filter()
            ->flip()
            ->values();

        if ($question->isSubType('ARQ')) {
            $this->arqStructure = \tcCore\MultipleChoiceQuestion::getArqStructure();
        }

        $this->answerStruct = $question->getCorrectAnswerStructure()
            ->map(function ($link) use ($givenAnswerId) {
                $link->active = $givenAnswerId->contains($link->multiple_choice_question_answer_id);
                return $link;
            });
    }
}