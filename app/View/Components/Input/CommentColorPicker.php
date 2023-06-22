<?php

namespace tcCore\View\Components\input;

use Illuminate\View\Component;
use tcCore\Http\Enums\CommentMarkerColor;

class CommentColorPicker extends Component
{
    public bool $disabled = false;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public ?string $commentThreadId = '',
        public string $uuid,
        public CommentMarkerColor|string|null $value = null,
    )
    {
        if(is_string($this->value)) {
            $this->value = CommentMarkerColor::tryFrom($this->value);
        }

        if($this->value === null) {
            $this->value = CommentMarkerColor::BLUE;
        }

        if($this->commentThreadId === null) {
            $this->disabled = true;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.input.comment-color-picker');
    }
}
