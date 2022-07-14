<?php

namespace tcCore\View\Components\Actions;

use Illuminate\View\Component;
use tcCore\Test;

class TestDelete extends Component
{
    public $test;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($uuid)
    {

        $this->test = Test::findByUuid($uuid);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.actions.test-delete');
    }
}
