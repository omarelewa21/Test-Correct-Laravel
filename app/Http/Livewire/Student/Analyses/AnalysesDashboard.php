<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use Livewire\Component;
use function view;

class AnalysesDashboard extends Component
{
    public function render()
    {
        return view('livewire.student.analyses.analyses-dashboard')->layout('layouts.student');;
    }
}
