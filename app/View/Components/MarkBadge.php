<?php

namespace tcCore\View\Components;

use Illuminate\View\Component;

class MarkBadge extends Component
{
    public $rating;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($rating)
    {
        $this->rating = !$rating ? null : $rating;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.mark-badge');
    }
}
