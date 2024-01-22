<?php

namespace tcCore\View\Components\Input;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Ramsey\Uuid\Uuid;

class Select extends Component
{
    public function __construct(
        public ?string $placeholder = null,
        public ?string $containerId = null,
        public ?bool   $error = null,
        public bool   $emptyOption = false,
        public bool   $disabled = false,
    ) {
        $this->containerId ??= "select-" . Uuid::uuid4();
        $this->placeholder ??= __('test-take.Selecteer...');
    }

    public function render(): View
    {
        return view('components.input.select');
    }
}
