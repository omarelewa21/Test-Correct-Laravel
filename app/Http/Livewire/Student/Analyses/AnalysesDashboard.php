<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\Period;
use tcCore\User;
use function view;

class AnalysesDashboard extends Component
{
    public $educationLevelYears = [];

    public $periods = [];

    public $teachers = [];

    public $filters = [];

    public function mount()
    {
        $this->clearFilters();
        $this->periods = Period::all(['uuid', 'name'])->map(
            function ($period) {
                return [
                    'value' => $period->uuid,
                    'label' => $period->name,
                ];
            })
            ->toArray();
        $this->teachers = User::teachersForStudent(auth()->user())
            ->get()
            ->map(
            function($teacher) {
                return [
                    'value' => $teacher->uuid,
                    'label' => $teacher->name_full,
                ];
            }
        );
        $this->educationLevelYears = EducationLevel::yearsForStudent(auth()->user())
            ->map(
                function($year) {
                    return [
                        'value' => $year,
                        'label' => $year,
                    ];
                }
            );
    }

    public function render()
    {
        return view('livewire.student.analyses.analyses-dashboard')->layout('layouts.student');;
    }

    public function hasActiveFilters()
    {
        return false;
    }


    public function clearFilters()
    {
        $this->filters = [
            'periods'  => [],
            'subjects' => [],
            'teachers' => [],
        ];
    }
}
