<?php

namespace tcCore\View\Components\Sidebar\Cms;

use Illuminate\View\Component;

abstract class AbstractDummyQuestionButton extends Component
{
    public $loop;

    public function __construct($loop)
    {
        $this->loop = $loop;
    }
}
