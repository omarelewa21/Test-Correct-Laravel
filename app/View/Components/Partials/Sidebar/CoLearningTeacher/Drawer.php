<?php

namespace tcCore\View\Components\Partials\Sidebar\CoLearningTeacher;

use Illuminate\View\Component;
use tcCore\AnswerRating;

class Drawer extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public ?AnswerRating $activeAnswerRating = null
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.partials.sidebar.co-learning-teacher.drawer');
    }
}
