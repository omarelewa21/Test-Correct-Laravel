<?php

namespace tcCore\Http\Livewire\Navigation;

use tcCore\Http\Livewire\NavigationBar;
use tcCore\Http\Traits\WithTeacherMenu;

class TeacherNavigationBar extends NavigationBar
{
    use WithTeacherMenu;

    public function render()
    {
        return view('livewire.navigation.teacher-navigation-bar');
    }
}