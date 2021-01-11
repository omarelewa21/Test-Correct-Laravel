<?php

namespace tcCore\Http\Livewire;

use Livewire\Component;


class Dashboard extends Component
{

    public function mount()
    {
    }

    public function render()
    {
        return view('student.index');
    }

}