<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Livewire\Component;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\WithCloseable;

class RelationQuestion extends \tcCore\Http\Livewire\TestPrint\RelationQuestion
{
    public function render()
    {
        return view('livewire.test_opgaven_print.relation-question');
    }
}
