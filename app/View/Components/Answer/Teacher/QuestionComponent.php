<?php

namespace tcCore\View\Components\Answer\Teacher;

use Illuminate\View\Component;
use tcCore\Answer;
use tcCore\Question;

abstract class QuestionComponent extends Component
{
    public function __construct(
        public Question $question,
        public Answer $answer,
    ) {}
}