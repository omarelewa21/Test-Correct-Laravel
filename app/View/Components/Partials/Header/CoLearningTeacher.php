<?php

namespace tcCore\View\Components\Partials\Header;

use Illuminate\Support\Str;

class CoLearningTeacher extends HeaderComponent
{
    public readonly string $discussionTypeTranslation;

    public function __construct(
        public readonly string  $testName,
        public readonly bool    $atLastQuestion,
        private readonly string $discussionType,
    )
    {
        parent::__construct();
        $this->discussionTypeTranslation = $this->discussionType === 'OPEN_ONLY'
            ? Str::upper(__('co-learning.open_questions'))
            : Str::upper(__('co-learning.all_questions'));
    }

    public function render()
    {
        return view('components.partials.header.co-learning-teacher');
    }
}
