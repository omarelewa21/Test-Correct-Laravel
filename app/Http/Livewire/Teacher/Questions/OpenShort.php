<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Livewire\Component;

class OpenShort extends Component
{
//    public $openTab = 1;
//
//    protected $queryString = ['openTab' => ['except' => 1]];

    public $questionType = 'open';

    public $testName = 'test_name';

    public $question = [
        'score'             => 6,
        'closable'          => 0,
        'discuss'           => 1,
        'maintain_position' => 0,
        'decimal_score'     => 0,
        'add_to_database'   => 1,
        'note_type'         => 'NONE',
    ];

    public function save()
    {
        dd($this->question);
    }

    public function render()
    {
        return view('livewire.teacher.questions.open-short')->layout('layouts.base');
    }
}
