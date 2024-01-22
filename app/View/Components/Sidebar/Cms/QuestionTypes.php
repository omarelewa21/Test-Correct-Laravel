<?php

namespace tcCore\View\Components\Sidebar\Cms;

use Illuminate\View\Component;
use tcCore\Http\Livewire\Teacher\Cms\TypeFactory;
use tcCore\Subject;

class QuestionTypes extends Component
{
    public array $questionTypes = [];
    public array $questionGroups = [];

    public function __construct(Subject $subject, public bool $confirmRelationQuestion = false)
    {
        $this->questionTypes = TypeFactory::questionTypes($subject);
        $this->questionGroups = [
            'open'   => __('cms.open-questions'),
            'closed' => __('cms.closed-questions'),
            'extra'  => __('cms.extra'),
        ];
    }

    public function render(): string
    {
        return 'components.sidebar.cms.question-types';
    }
}
