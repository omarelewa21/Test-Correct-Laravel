<?php

namespace tcCore\View\Components\Partials\Evaluation;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use tcCore\Question;

class Drawer extends Component
{

    public function __construct(
        public bool       $feedbackTabDisabled,
        public bool       $coLearningEnabled,
        public string     $uniqueKey,
        public string|int $navigationValue,
        public Question   $question,
        public ?Question  $group = null,
        public bool       $inReview = false,
    ) {}

    public function render(): View
    {
        return view('components.partials.evaluation.drawer');
    }
}
