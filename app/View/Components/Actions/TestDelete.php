<?php

namespace tcCore\View\Components\Actions;

use Illuminate\View\Component;
use tcCore\Test;

class TestDelete extends Component
{
    public $test;
    public $variant;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($uuid, $variant='icon-button')
    {

        $this->test = Test::findByUuid($uuid);
        $this->variant = $variant;

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
