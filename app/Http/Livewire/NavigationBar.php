<?php

namespace tcCore\Http\Livewire;

use Livewire\Component;

class NavigationBar extends Component
{
    public function render()
    {
        return view('livewire.navigation-bar')->layout('layouts.base');
    }
}
