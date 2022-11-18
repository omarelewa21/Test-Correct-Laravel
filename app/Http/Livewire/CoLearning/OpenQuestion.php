<?php

namespace tcCore\Http\Livewire\CoLearning;

use Livewire\Component;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class OpenQuestion extends CoLearningQuestion
{
    public bool $webSpellChecker = false;

    public function render()
    {
        if($this->question->subtype === 'short'){
            return view('livewire.co-learning.open-short-question');
        }

        if($this->question->subtype === 'writing'){
            $this->webSpellChecker = true;
        }

        return view('livewire.co-learning.open-long-writing-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return $this->answered;
    }

    protected function handleGetAnswerData()
    {
        $temp = (array) json_decode($this->answerRating->answer->json);
        if (key_exists('value', $temp)) {
            $this->answer = $temp['value'];
        }
    }
}
