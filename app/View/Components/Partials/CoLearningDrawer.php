<?php

namespace tcCore\View\Components\Partials;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use tcCore\Question;

class CoLearningDrawer extends Component
{

    public function __construct(
        public string $uniqueKey,
    ) {}

    public function render(): View
    {
        return view('components.partials.co-learning-drawer');
    }
}
