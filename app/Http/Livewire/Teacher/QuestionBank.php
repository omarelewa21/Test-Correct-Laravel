<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Question;

class QuestionBank extends Component
{
    public $filters = [
        'search' => ''
    ];

    public function mount()
    {

    }

    public function render()
    {
        return view('livewire.teacher.question-bank');
    }

    public function getQuestionsProperty()
    {
        return Question::filtered($this->getFilters())
            ->with('authors', 'subject:id,base_subject_id,name', 'subject.baseSubject:id,name')
            ->limit(6)
            ->get()->unique();
    }

    private function getFilters()
    {
        return collect($this->filters)->reject(function($filter) {
           return blank($filter);
        })->toArray();
    }
}