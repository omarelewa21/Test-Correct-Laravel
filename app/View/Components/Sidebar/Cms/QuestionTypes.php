<?php

namespace tcCore\View\Components\Sidebar\Cms;

use Illuminate\View\Component;
use tcCore\Http\Livewire\Teacher\Questions\CmsFactory;

class QuestionTypes extends Component
{
    public $questionTypes = [];

    public function __construct()
    {
        $this->questionTypes = CmsFactory::questionTypes();
    }

    public function render(): string
    {
        return 'components.sidebar.cms.question-types';
    }
}
