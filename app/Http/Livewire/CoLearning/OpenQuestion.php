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
    public function render()
    {
        return view('livewire.co-learning.open-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }

    protected function handleGetAnswerData()
    {
        $temp = (array) json_decode($this->answerRating->answer->json);
        if (key_exists('value', $temp)) {
            $this->answer = $temp['value'];
        }
    }
}
