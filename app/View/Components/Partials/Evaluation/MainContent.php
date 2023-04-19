<?php

namespace tcCore\View\Components\Partials\Evaluation;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use tcCore\Question;

class MainContent extends Component
{
    public function __construct(
        public Question $question,
        public string $uniqueKey,
        public string|int $navigationValue,
        public bool $groupPanel,
        public bool $questionPanel,
        public bool $answerModelPanel,
        public bool $showCorrectionModel,
        public ?Question $group = null,
    ) {}

    public function render(): View
    {
        return view('components.partials.evaluation.main-content');
    }
}
