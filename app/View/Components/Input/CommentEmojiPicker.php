<?php

namespace tcCore\View\Components\Input;

use Illuminate\View\Component;
use tcCore\Http\Enums\CommentEmoji;

class CommentEmojiPicker extends Component
{

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public ?string $commentUuid = '',
        public ?CommentEmoji $emoji = null,
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.input.comment-emoji-picker');
    }
}
