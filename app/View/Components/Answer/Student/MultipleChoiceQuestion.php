<?php

namespace tcCore\View\Components\Answer\Student;

class MultipleChoiceQuestion extends QuestionComponent
{
    public mixed $answerStruct;
    public array $arqStructure = [];
    public bool $allOrNothingToggleActive = false;

    protected function setAnswerStruct($question, $answer): void
    {
        $givenAnswerId = collect(json_decode($answer->json))
            ->filter()
            ->keys();

        if ($question->isSubType('ARQ')) {
            $this->arqStructure = \tcCore\MultipleChoiceQuestion::getArqStructure();
        }

        $this->answerStruct = $question->getCorrectAnswerStructure()
            ->map(function ($link) use ($givenAnswerId) {
                $link->active = $givenAnswerId->contains($link->multiple_choice_question_answer_id);
                return $link;
            });

        if ($question->all_or_nothing) {
            $this->allOrNothingToggleActive = $this->answerStruct->filter(fn($link) => $link->score > 0)
                ->map(fn($link) => $link->multiple_choice_question_answer_id)
                ->diff($givenAnswerId)
                ->isEmpty();
        }
    }
}