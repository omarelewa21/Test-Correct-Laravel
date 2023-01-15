<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Composer\Package\Package;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Question;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;

class MatrixQuestion extends \tcCore\Http\Livewire\TestPrint\MatrixQuestion
{
    public function render()
    {
        return view('livewire.test_opgaven_print.matrix-question');
    }

}
