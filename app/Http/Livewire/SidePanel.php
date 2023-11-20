<?php

namespace tcCore\Http\Livewire;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;

class SidePanel extends TCComponent
{
    public bool $openSidePanel = false;
    public string $component = '';
    public array $componentAttributes = [];
    public array $sidePanelAttributes = [
        'offsetTop' => 0,
        'width'     => '100%'
    ];
    public int $componentCache = 0;

    protected $listeners = [
        'openPanel'
    ];

    public function openPanel(string $component, array $componentAttributes = [], array $sidePanelAttributes = []): void
    {
        $this->openSidePanel = true;
        $this->sidePanelAttributes = array_merge($this->sidePanelAttributes, $sidePanelAttributes);

        if ($this->component === $component) {
            $this->emitTo(
                $component,
                'newAttributes',
                ['attributes' => $componentAttributes]
            );
        }
        $this->component = $component;
        $this->componentAttributes = $componentAttributes;
    }

    public function render(): ViewContract
    {
        return View::make(
            view: 'livewire.side-panel',
        );
    }
}
