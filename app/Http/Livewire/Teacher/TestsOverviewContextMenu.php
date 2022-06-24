<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Test;

class TestsOverviewContextMenu extends Component
{
    public $displayMenu = false;

    public $btnId;

    public $openTab = 'personal';

    protected $listeners = [
        'showMenu',
    ];
    public $x;
    public $y;


    public function showMenu($args)
    {
        $this->test = Test::whereUuid($args['testUuid'])->first();
        $this->openTab = $args['openTab'];
        $this->btnId = sprintf('test%s', $args['id']);
        $this->displayMenu = true;
        $this->x = $args['x'];
        $this->y = $args['y'];
    }

    public function render()
    {
        return view('livewire.teacher.tests-overview-context-menu');
    }
}
