<?php

namespace tcCore\View\Components\Grid;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use tcCore\WordList;

class WordListCard extends Component
{
    public string $wordsString = '';

    public function __construct(
        public WordList $wordList,
        public bool     $addable = false,
        public bool     $used = false,
    ) {
        $this->wordsString = $wordList->words->map->text->join(', ');
    }

    public function render(): View
    {
        return view('components.grid.word-list-card');
    }
}
