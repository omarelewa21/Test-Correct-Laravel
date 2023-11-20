<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;
use tcCore\Test;

class TestCard extends Component
{
    public function __construct(
        public Test    $test,
        public string  $mode = 'page',
        public ?string $openTab = 'personal'
    ) {}

    public function render(): string
    {
        return 'components.grid.test-card';
    }
}
