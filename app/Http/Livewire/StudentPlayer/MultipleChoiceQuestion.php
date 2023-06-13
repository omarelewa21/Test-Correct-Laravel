<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\Questions\WithMultipleChoiceStructure;
use tcCore\Http\Traits\WithCloseable;

abstract class MultipleChoiceQuestion extends TCComponent
{
    use withCloseable;
    use WithMultipleChoiceStructure;

    public $question;
    public $answer = '';
    public $answers;
    public $answerStruct;
    public $shuffledKeys;
    public $number;
    public $arqStructure = [];
    public $answerText;

    public function mount()
    {
        $this->arqStructure = \tcCore\MultipleChoiceQuestion::getArqStructure();

        $this->setAnswerStruct();

        $this->shuffleKeys();

        $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
            $this->answerText[$answers->id] = $answers->answer;
        });
    }

    public function updatedAnswer($value)
    {
        $this->answerStruct = array_fill_keys(array_keys($this->answerStruct), 0);
        $this->answerStruct[$value] = 1;
    }

    /**
     * @return void
     */
    protected function shuffleKeys(): void
    {
        $this->shuffledKeys = array_keys($this->answerStruct);
        if (!$this->question->isCitoQuestion()) {
            if ($this->question->subtype != 'ARQ' && $this->question->subtype != 'TrueFalse' && !$this->question->fix_order) {
                shuffle($this->shuffledKeys);
            }
        }
    }

    protected function setAnswerStruct($whenHasAnswerCallback = null): void
    {
        if ($this->hasGivenAnswer()) {
            $this->answerStruct = $this->getStructFromAnswer();
            if (!is_null($whenHasAnswerCallback) && is_callable($whenHasAnswerCallback)) {
                $whenHasAnswerCallback();
            }
        } else {
            $this->setDefaultStruct();
        }
    }

    protected function getTemplateName(): string
    {
        return str($this->question->subtype)
            ->replace('ARQ', 'arq')
            ->kebab()
            ->append('-question');
    }

    final protected function hasGivenAnswer(): bool
    {
        return !empty(json_decode($this->answers[$this->question->uuid]['answer']));
    }

    final protected function getStructFromAnswer(): array
    {
        return json_decode($this->answers[$this->question->uuid]['answer'], true);
    }

    final protected function setDefaultStruct(): void
    {
        $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
            $this->answerStruct[$answers->id] = 0;
        });
    }
}
