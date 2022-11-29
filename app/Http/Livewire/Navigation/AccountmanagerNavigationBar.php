<?php

namespace tcCore\Http\Livewire\Navigation;

use tcCore\Http\Livewire\NavigationBar;
use tcCore\Http\Traits\WithAccountmanagerMenu;

class AccountmanagerNavigationBar extends NavigationBar
{
    use WithAccountmanagerMenu;

    public function render()
    {
        return view('livewire.navigation.accountmanager-navigation-bar');
    }
}