<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;

class TestTakeCard extends Component
{
    public $testTake;
    public $questions;

    public function __construct($testTake)
    {
        $this->testTake = $testTake;
        $this->questions = $testTake->test->getQuestionCount();
    }

    public function render(): string
    {
        return 'components.grid.test-take-card';
    }
}
