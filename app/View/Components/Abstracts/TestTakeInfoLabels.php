<?php

namespace tcCore\View\Components\Abstracts;

use Illuminate\View\Component;
use tcCore\TestTake;

abstract class TestTakeInfoLabels extends Component
{
    public array $icons;

    protected function __construct(protected TestTake $testTake)
    {
        $this->icons = $this->setUpIcons();
    }

    public function render()
    {
        return view('components.partials.test-take-info-labels');
    }

    protected function setUpIcons(): array
    {
        return [
            [
                'name'      => 'app-logo',
                'show'      => $this->showAppIcon(),
                'tooltip'   => $this->getTooltip('app'),
            ],
            [
                'name'      => 'web',
                'show'      => $this->showWebIcon(),
                'tooltip'   => $this->getTooltip('web'),
            ],
            [
                'name'      => 'test-direct',
                'show'      => $this->showTestDirectIcon(),
                'tooltip'   => $this->getTooltip('test-direct'),
            ],
            [
                'name'      => 'redo',
                'show'      => $this->showRedoIcon(),
                'tooltip'   => $this->getTooltip('redo'),
            ],
        ];
    }

    abstract protected function showAppIcon(): bool;
    abstract protected function showWebIcon(): bool;
    abstract protected function showTestDirectIcon(): bool;
    abstract protected function showRedoIcon(): bool;
    abstract protected function getTooltip(string $iconName): string;
}
