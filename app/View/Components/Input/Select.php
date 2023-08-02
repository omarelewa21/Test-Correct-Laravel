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
    ) {
        $this->containerId ??= "select-" . Uuid::uuid4();
    }

    public function render(): View
    {
        return view('components.input.select');
    }
}
