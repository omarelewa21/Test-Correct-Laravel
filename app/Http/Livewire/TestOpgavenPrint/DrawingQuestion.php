<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class DrawingQuestion extends \tcCore\Http\Livewire\TestPrint\DrawingQuestion
{
    public function render()
    {
        return view('livewire.test_opgaven_print.drawing-question');
    }

}
