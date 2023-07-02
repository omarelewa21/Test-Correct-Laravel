<?php

namespace tcCore\Http\Traits\Modal;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use tcCore\EducationLevel;
use tcCore\Period;
use tcCore\Subject;
use tcCore\Test;
use tcCore\TestKind;
use Illuminate\Support\Str;

trait TestActions
{
    public $allowedTestKinds = [];

    public $allowedSubjects = [];

    public $allowedPeriods = [];

    public $allowedEductionLevels = [];

    protected function rules()
    {
        return [
            'request.name'                 => $this->getNameRulesDependingOnAction(),
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
            'request.name.unique' => __('validation.unique_test_name'),
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
        return Period::filtered([], [])->get(['id', 'name', 'start_date', 'end_date'])->keyBy('id');
    }

    protected function getAllowedEducationLevels()
    {
        return EducationLevel::filtered(['user_id' => auth()->id()], [])->select(['id', 'name', 'max_years', 'uuid'])->get()->keyBy('id');
    }

    public function getMaxEducationLevelYearProperty()
    {
        if ($this->request['education_level_id']) {
            $maxYears = $this->allowedEductionLevels->where('id', $this->request['education_level_id'])->first()?->max_years;
        }
        return $maxYears ?? 6;
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

    private function getNameRulesDependingOnAction()
    {
        return [
            'required',
            'min:3',
            Rule::unique('tests', 'name')
                ->where(function (Builder $query){
                    if (auth()->user()->isToetsenbakker()){
                        $query->whereRaw('1 = 0'); // This will return no results when the user is a Toetsenbakker to allow for duplicate test names
                    } else {
                        // This will return all tests that are not system tests and are not deleted and are owned by the current user's school location
                        $query->where(['is_system_test' => 0, 'deleted_at' => null])
                            ->whereExists(function (Builder $query){
                                $query->select(DB::raw(1))
                                    ->from('users')
                                    ->whereRaw('tests.author_id = users.id')
                                    ->where('users.school_location_id', auth()->user()->school_location_id);
                            });
                    }
                })
                ->when(isset($this->testUuid), fn ($query) =>
                    $query->ignore(Test::whereUuid($this->testUuid)->value('id'))  // When editing an existing test, ignore the current test ID for the uniqueness check
                )
        ];
    }
}