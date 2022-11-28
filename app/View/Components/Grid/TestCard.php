<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;

class TestCard extends Component
{
    public $test;
    public $mode;

    public function __construct($test, $mode = 'page')
    {
        $this->test = $test;
        $this->mode = $mode;
    }

    public function render(): string
    {
        return 'components.grid.test-card';
    }
}
