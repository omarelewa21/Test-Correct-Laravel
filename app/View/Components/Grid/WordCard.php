<?php

namespace tcCore\View\Components\Grid;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use tcCore\Word;

class WordCard extends Component
{
    public string $wordsString = '';

    public function __construct(
        public Word $word,
        public bool $addable = false,
        public bool $used = false,
    ) {
        $this->wordsString = $word->associations->map->text->join(', ');
    }

    public function render(): View
    {
        return view('components.grid.word-card');
    }
}
