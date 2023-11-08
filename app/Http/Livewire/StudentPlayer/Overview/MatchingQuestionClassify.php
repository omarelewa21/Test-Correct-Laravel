<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\Questions\WithClassifyAnswers;
use tcCore\Http\Traits\WithAttachments;

class MatchingQuestionClassify extends MatchingQuestion
{
    use WithClassifyAnswers;
    use WithAttachments;
}