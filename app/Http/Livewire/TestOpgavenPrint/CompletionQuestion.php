<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Livewire\Component;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\WithCloseable;

class CompletionQuestion extends \tcCore\Http\Livewire\TestPrint\CompletionQuestion
{
    public function render()
    {
        if ($this->question->subtype == 'completion') {
            $html = $this->completionHelper($this->question);
            return view('livewire.test_opgaven_print.completion-question', ['html' => $html]);
        } elseif ($this->question->subtype == 'multi') {
            $html = $this->multiHelper($this->question);
            return view('livewire.test_opgaven_print.selection-question', ['html' => $html]);
        } else {
            throw new \Exception ('unknown type');
        }
    }
}
