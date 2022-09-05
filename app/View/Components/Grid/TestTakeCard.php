<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;

class TestTakeCard extends Component
{
    public $testTake;

    public function __construct($testTake)
    {
        $this->testTake = $testTake;
    }

    public function render(): string
    {
        return 'components.grid.test-take-card';
    }
}
