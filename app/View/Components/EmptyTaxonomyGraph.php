<?php

namespace tcCore\View\Components;

use Illuminate\View\Component;

class EmptyTaxonomyGraph extends Component
{
    public $show = false;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($show = false)
    {
        $this->show = $show;
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.empty-taxonomy-graph');
    }
}
