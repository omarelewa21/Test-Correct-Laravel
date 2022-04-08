<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Question;
use tcCore\Subject;

class QuestionBank extends Component
{
    public $filters = [
        'search' => '',
        'subject_id' => ''
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
            ->where(function($query) {
                $query->where('scope', '!=', 'cito')
                ->orWhereNull('scope');
            })
            ->with(['questionAttainments', 'questionAttainments.attainment', 'tags','authors', 'subject:id,base_subject_id,name', 'subject.baseSubject:id,name'])
            ->distinct()
            ->get();
    }

    private function getFilters()
    {
        return collect($this->filters)->reject(function($filter) {
           return blank($filter);
        })->toArray();
    }

    public function getSubjectsProperty()
    {
        return Subject::filtered([], ['name' => 'asc'])
            ->with('baseSubject')
            ->get()
            ->map(function ($subject) {
                return [
                    'value' => $subject->getKey(),
                    'label' => $subject->name
                ];
            })->toArray();

    }

    public function updatedFilters($value, $name)
    {
        $this->filters[$name] = $this->extractValue($value);
    }

    private function extractValue($value)
    {
        return is_array($value) ? $value['value'] : $value;
    }
}