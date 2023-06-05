<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Http\Traits\Questions\WithClassifyAnswers;

abstract class MatchingQuestionClassify extends MatchingQuestion
{
    use WithClassifyAnswers;
}