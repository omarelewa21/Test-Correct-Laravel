<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use Livewire\Component;
use tcCore\Attainment;
use tcCore\EducationLevel;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Period;
use tcCore\Subject;
use tcCore\User;

class AnalysesAttainmentDashboard extends AnalysesDashboard
{
    public $attainment;

    public $subject;

    public $showEmptyStateForPValueGraph = false;

    protected $queryString = 'subject';

    protected $topItems = [
        410 => 'Literaire ontwikkeling',
        411 => 'Literaire begrippen',
        412 => 'Literatuurgeschiedenis',
    ];

    public function mount(?Attainment $attainment = null)
    {
        parent::mount();

        $this->attainment = $attainment;
    }

    public function render()
    {
        $this->dispatchBrowserEvent('filters-updated');
        return view('livewire.student.analyses.analyses-attainment-dashboard')->layout('layouts.student');
    }

    public function getDataProperty()
    {
        $result = PValueRepository::getPValuePerSubAttainmentForStudentAndAttainment(
            auth()->user(),
            $this->attainment,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues()
        );
        //($result->toArray());//;->mapWithKey(fn($value, $key) => [$value->subject => $value->score]));

        $this->showEmptyStateForPValueGraph = $result->count() === 0;

        $this->dataValues = $result->map(function ($pValue, $key) {
            return (object)[
                'x'       => $key + 1,
                'title'   => __('student.subleerdoel', ['number' => $key + 1]),
                'count'   => $pValue->cnt,
                'value'   => number_format(($pValue->score > 0 ? $pValue->score : 0), 2),
                'text'    => $pValue->serie,
                'basedOn' => trans_choice('student.attainment_tooltip_title', $pValue->cnt, [
                    'basedOn' => $pValue->cnt
                ]),
                'link'    => route('student.analyses.subattainment.show', [
                    'attainment' => Attainment::find($pValue->attainment_id)->uuid,
                    'subject'    => $this->subject,
                ]),
            ];
        })->toArray();

        return $result;
    }


    protected function getMillerData($attainmentId)
    {
        return PValueTaxonomyMillerRepository::getPValueForStudentForAttainment(auth()->user(),
            $attainmentId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getRTTIData($attainmentId)
    {
        return PValueTaxonomyRTTIRepository::getPValueForStudentForAttainment(auth()->user(),
            $attainmentId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getBloomData($attainmentId)
    {
        return PValueTaxonomyBloomRepository::getPValueForStudentForAttainment(
            auth()->user(),
            $attainmentId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    public function redirectBack()
    {
        return redirect(route('student.analyses.subject.show', $this->subject));
    }
}
