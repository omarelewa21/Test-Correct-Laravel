<?php

namespace tcCore\Http\Livewire\ContextMenu;

use tcCore\Question;

class QuestionCard extends ContextMenuComponent
{
    public $isGroupQuestion = false;

    public function setContextValues($uuid, $contextData): bool
    {
        $this->isGroupQuestion = Question::whereUuid($uuid)->where('type','GroupQuestion')->exists();
        return true;
    }
}