<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Test;

class TestsOverviewContextMenu extends Component
{
    public $displayMenu = false;

    public $btnId;

    public $openTab = 'personal';

    protected $listeners = [
        'showMenu',
    ];

    public $top;
    public $left;


    public function showMenu($args)
    {
        $this->test = Test::whereUuid($args['testUuid'])->first();
        $this->openTab = $args['openTab'];
        $this->btnId = sprintf('test%s', $args['id']);
        $this->displayMenu = true;
        $this->top = $args['top'];
        $this->left = $args['left'];
    }

    public function render()
    {
        return view('livewire.teacher.tests-overview-context-menu');
    }
}
