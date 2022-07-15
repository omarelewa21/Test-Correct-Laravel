<?php

namespace tcCore\View\Components\Actions;

use Illuminate\View\Component;
use tcCore\Test;

class TestOpenPreview extends Component
{
    public $test;
    public $variant;
    public $url;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($uuid, $variant='icon-button')
    {
        $this->test = Test::findByUuid($uuid);
        $this->variant = $variant;

        $this->url = route('teacher.test-preview', ['test'=> $uuid]);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.actions.test-open-preview');
    }
}
