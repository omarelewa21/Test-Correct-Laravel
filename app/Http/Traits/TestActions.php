<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Facades\Auth;
use tcCore\EducationLevel;
use tcCore\Period;
use tcCore\Subject;
use tcCore\TestKind;

trait TestActions
{
    public $allowedTestKinds = [];

    public $allowedSubjects = [];

    public $allowedPeriods = [];

    public $allowedEductionLevels = [];

    protected function rules()
    {
        return [
            'request.name'                 => 'required|min:3|unique:tests,name,NULL,id,author_id,' . Auth::id() . ',deleted_at,NULL,is_system_test,0',
            'request.abbreviation'         => 'required|max:5',
            'request.test_kind_id'         => ['required', 'integer', $this->getAllowedTestKindsRule()],
            'request.subject_id'           => ['required', 'integer', $this->getAllowedSubjectsRule()],
            'request.education_level_id'   => ['required', 'integer', $this->getAllowedEducationLevelsRule()],
            'request.education_level_year' => 'required|integer|between:1,6',
            'request.period_id'            => ['required', 'integer', $this->getAllowedPeriodsRule()],
            'request.shuffle'              => 'required|boolean',
            'request.introduction'         => 'sometimes',
        ];
    }

    protected function getMessages()
    {
        return [
            'request.name.unique' => __('validation.unique', ['attribute' => __('validation.test name')]),
        ];
    }

    protected function getAllowedSubjects()
    {
        return Subject::filtered(['user_current' => auth()->id()], ['name' => 'asc'])->get(['id', 'name'])->keyBy('id');
    }

    protected function getAllowedTestKinds()
    {
        return TestKind::orderBy('name', 'asc')->get(['name', 'id']);
    }

    protected function getAllowedPeriods()
    {
        return Period::filtered(['current_school_year' => 1], [])->get(['id', 'name', 'start_date', 'end_date'])->keyBy('id');
    }

    protected function getAllowedEducationLevels()
    {
        return EducationLevel::filtered(['user_id' => auth()->id()], [])->select(['id', 'name', 'max_years', 'uuid'])->get()->keyBy('id');
    }

    public function getMaxEducationLevelYearProperty()
    {
        $maxYears = 6;
        if ($this->request['education_level_id']) {
            $level = $this->allowedEductionLevels->first(function ($level) {
                $compareWith = property_exists($level, 'id') ? $level->id : $level['id'];
                return $compareWith == $this->request['education_level_id'];
            });

            return is_array($level) ? $level['id'] : $level->id;
        }
        return $maxYears;
    }

    private function getAllowedSubjectsRule(): string
    {
        return 'in:' . collect($this->allowedSubjects)->map(function ($subject) {
                return property_exists($subject, 'id') ? $subject->id : $subject['id'];
            })->join(',');
    }

    private function getAllowedEducationLevelsRule(): string
    {
        return 'in:' . collect($this->allowedEductionLevels)->map(function ($educationLevel) {
                return property_exists($educationLevel, 'id') ? $educationLevel->id : $educationLevel['id'];
            })->join(',');
    }

    private function getAllowedPeriodsRule(): string
    {
        return 'in:' . collect($this->allowedPeriods)->map(function ($period) {
                return property_exists($period, 'id') ? $period->id : $period['id'];
            })->join(',');
    }

    private function getAllowedTestKindsRule(): string
    {
        return 'in:' . collect($this->allowedTestKinds)->map(function ($testKind) {
                return property_exists($testKind, 'id') ? $testKind->id : $testKind['id'];
            })->join(',');
    }

}