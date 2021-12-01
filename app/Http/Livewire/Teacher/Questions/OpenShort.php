<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Livewire\Component;

class OpenShort extends Component
{
    public $openTab = 2;
//
//    protected $queryString = ['openTab' => ['except' => 1]];

    public $questionType = 'open';

    public $testName = 'test_name';

    public $question = [
        'score'             => 6,
        'closable'          => 0,
        'discuss'           => 1,
        'maintain_position' => 1,
        'decimal_score'     => 0,
        'add_to_database'   => 1,
        'note_type'         => 0,
        'question'          => '',
        'answer'            => '',
        'rtti'              => '',
        'bloom'             => '',
        'miller'            => '',

    ];


    protected $rules = [
        'question.question' => 'required',
        'question.answer'   => 'required',
    ];

    public function save()
    {
        $this->validate();
        dd($this->question);
    }

    public function render()
    {
        return view('livewire.teacher.questions.open-short')->layout('layouts.base');
    }
}
