<?php

namespace tcCore\Http\Livewire\StudentPlayer;

abstract class RelationQuestion extends StudentPlayerQuestion
{
    public $uuid;
    public $answerStruct;
    public $answerText = [];

    public function mount()
    {

    }
}
