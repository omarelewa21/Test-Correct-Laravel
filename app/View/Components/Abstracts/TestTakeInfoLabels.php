<?php

namespace tcCore\View\Components\Abstracts;

use Illuminate\View\Component;

abstract class TestTakeInfoLabels extends Component
{
    public array $icons;

    protected function __construct()
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
                'show' => $this->showAppIcon(),
                'path' => 'components.icon.arrow-last'
            ],
            [
                'icon-name' => 'web',
                'show' => $this->showWebIcon(),
                'path' => 'components.icon.arrow-last'
            ],
            [
                'icon-name' => 'test-direct',
                'show' => $this->showTestDirectIcon(),
                'path' => 'components.icon.arrow-last'
            ],
            [
                'icon-name' => 'redo',
                'show' => $this->showRedoIcon(),
                'path' => 'components.icon.arrow-last'
            ],
        ];
    }

    abstract protected function showAppIcon(): bool;
    abstract protected function showWebIcon(): bool;
    abstract protected function showTestDirectIcon(): bool;
    abstract protected function showRedoIcon(): bool;
}
