<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;

class TestTakeCard extends Component
{
    public $testTake;
    public $author;
    public $schoolClasses;

    public function __construct($testTake, $schoolClasses)
    {
        $this->testTake = $testTake;
        $this->author = $testTake->user->getFullNameWithAbbreviatedFirstName();
        $this->schoolClasses = $schoolClasses->map(function ($class) {
                return $class->label;
            })
            ->join(', ');
    }

    public function render(): string
    {
        return 'components.grid.test-take-card';
    }
}
