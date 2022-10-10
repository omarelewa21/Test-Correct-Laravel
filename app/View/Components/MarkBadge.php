<?php

namespace tcCore\View\Components;

use Illuminate\View\Component;

class MarkBadge extends Component
{
    public $rating;

    public $bgColor;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($rating)
    {
        $this->rating = $rating;
        $this->bgColor = $this->getBgColor();
    }

    private function getBgColor() {
        if ($this->rating > 5.5) {
            return 'bg-cta-primary text-white';
        }
        if ($this->rating < 5.5) {
            return 'bg-all-red text-white';
        }
        return 'bg-orange base';
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
