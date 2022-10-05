<?php

namespace tcCore\Http\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Helpers\NavigationBarHelper;

class NavigationBar extends Component
{
    public $activeRoute;

    protected $listeners = ['redirectToCake' => 'cakeRedirect'];

    public $showSchoolSwitcher = false;

    public function mount()
    {
        $this->activeRoute = NavigationBarHelper::getActiveRoute();
        $this->showSchoolSwitcher = Auth::user()->hasMultipleSchools();
    }

    public function render()
    {
        return view('livewire.navigation-bar')->layout('layouts.base');
    }

    public function cakeRedirect($cakeRouteName)
    {
        return CakeRedirectHelper::redirectToCake($cakeRouteName);
    }
}
