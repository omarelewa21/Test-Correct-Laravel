<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;

class TestCard extends Component
{
    public $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function render(): string
    {
        return 'components.grid.test-card';
    }
}
