<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Http\Interfaces\CollapsableHeader;

class Assessment extends Component implements CollapsableHeader
{
    public bool $headerCollapsed = false;
    public string $testName = 'Kaas';
    public function render()
    {
        return view('livewire.teacher.assessment')
            ->layout('layouts.assessment');
    }

    public function handleHeaderCollapse($args)
    {
        logger($args);
        $this->headerCollapsed = true;
        return true;
    }

    public function redirectBack()
    {
        // TODO: Implement redirectBack() method.
    }
}