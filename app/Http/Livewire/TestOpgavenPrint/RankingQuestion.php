<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;

class RankingQuestion extends \tcCore\Http\Livewire\TestPrint\RankingQuestion
{
    public function render()
    {
        return view('livewire.test_opgaven_print.ranking-question');
    }
}
