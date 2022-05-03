<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Test;

class TestsOverview extends Component
{
    public $subjects = [];
    public $educationLevelYear = '';
    public $educationLevel = '';
    public $search = '';

    public $filters = [
        'name' => '',


    ];

    public $selected = [];

    public function render()
    {
       $results = Test::filtered(
          $this->filters
       )->take(10)->get();




        return view('livewire.teacher.tests-overview')->with(compact(['results']));
    }
}
