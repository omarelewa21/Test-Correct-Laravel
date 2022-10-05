<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Period;
use tcCore\Subject;
use tcCore\User;
use function view;

class AnalysesOverviewDashboard extends AnalysesDashboard
{
    protected $topItems = [
        11 => 'Biology',
        1  => 'Nederlands',
    ];

    public function mount()
    {
        parent::mount();
    }

    public function getDataProperty()
    {
        $result = PValueRepository::getPValueForStudentBySubject(
            auth()->user(),
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues()
        );

        //($result->toArray());//;->mapWithKey(fn($value, $key) => [$value->subject => $value->score]));

        $this->dataValues = $result->map(function ($pValue) {
            return (object)[
                'x'     => $pValue->serie,
                'title' => $pValue->serie,
                'basedOn' => trans_choice('student.obv count questions', $pValue->cnt),
                'value' => number_format(($pValue->score > 0 ? $pValue->score : 0), 2),
                'link'  => route('student.analyses.subject.show', Subject::find($pValue->subject_id)->uuid),
            ];
        })->toArray();

        return $result;
    }

    public function render()
    {
        $this->dispatchBrowserEvent('filters-updated');//, ['newName' => $value]);
        return view('livewire.student.analyses.analyses-overview-dashboard')->layout('layouts.student');;
    }

    protected function getMillerData($subjectId)
    {
        return PValueTaxonomyMillerRepository::getPValueForStudentForSubject(
            auth()->user(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getRTTIData($subjectId)
    {
        return PValueTaxonomyRTTIRepository::getPValueForStudentForSubject(
            auth()->user(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getBloomData($subjectId)
    {
        return PValueTaxonomyBloomRepository::getPValueForStudentForSubject(
            auth()->user(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }
}
