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
                'icon-name' => 'app',
                'show'      => $this->showAppIcon(),
                'path'      => 'components.icon.app-logo',
                'props'     => ['tooltip' => $this->getTooltip('app')],
            ],
            [
                'icon-name' => 'web',
                'show'      => $this->showWebIcon(),
                'path'      => 'components.icon.web',
                'props'     => ['tooltip' => $this->getTooltip('web')],
            ],
            [
                'icon-name' => 'test-direct',
                'show'      => $this->showTestDirectIcon(),
                'path'      => 'components.icon.test-direct',
                'props'     => ['tooltip' => $this->getTooltip('test-direct')],
            ],
            [
                'icon-name' => 'redo',
                'show'      => $this->showRedoIcon(),
                'path'      => 'components.icon.redo',
                'props'     => ['tooltip' => $this->getTooltip('redo')],
            ],
        ];
    }

    abstract protected function showAppIcon(): bool;
    abstract protected function showWebIcon(): bool;
    abstract protected function showTestDirectIcon(): bool;
    abstract protected function showRedoIcon(): bool;
    abstract protected function getTooltip(string $iconName): string;
}
