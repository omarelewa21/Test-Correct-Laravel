<?php

namespace tcCore\Http\Livewire\Analyses;

use tcCore\BaseAttainment;
use tcCore\Http\Traits\WithAnalysesGeneralData;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Subject;

class AnalysesSubAttainmentDashboard extends AnalysesDashboard
{
    use WithAnalysesGeneralData;

    public $subject;

    protected $queryString = ['subject'];

    public $attainment;

    public $attainmentOrderNumber = 0;

    public $parentAttainmentOrderNumber = 0;

    public function mount(?BaseAttainment $baseAttainment = null)
    {
        $this->attainment = $baseAttainment;
        $this->taxonomyIdentifier = $this->attainment->id;


        parent::mount();
        if ($this->attainment) {
            $this->attainmentOrderNumber = $this->attainment->getOrderNumber();
//            dd($this->attainmentOrderNumber);
            if ($this->attainment->attainment) {
                $this->parentAttainmentOrderNumber = $this->attainment->attainment->getOrderNumber();
            }
        }

        $this->getFilterOptionsData();
    }

    public function getDataProperty()
    {
        $result = PValueRepository::getPValuePerSubAttainmentForStudentAndAttainment(
            $this->getHelper()->getForUser(),
            $this->attainment,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues()
        );

        $this->showEmptyStateForPValueGraph = $result->filter(fn($item) => !is_null($item['score']))->isEmpty();

        $this->dataValues = $result->map(function ($pValue, $key) {
            $link = false;
            if ($pValue->attainment_id) {
                $link = $this->getHelper()->getRouteForSubSubAttainmentShow($pValue, $this->subject);
            }

            return (object) [
                'x'       => $key + 1,
                'title'   => $this->attainment->getSubSubNameWithNumber($key + 1),
                'count'   => $pValue->cnt,
                'value'   => number_format(($pValue->score > 0 ? $pValue->score : 0), 2),
                'text'    => $pValue->description,
                'basedOn' => trans_choice('student.attainment_tooltip_title', $pValue->cnt, [
                    'basedOn' => $pValue->cnt
                ]),
                'link'    => $link,
            ];
        })->toArray();

        return $result;
    }

    public function render()
    {
        $this->dispatchBrowserEvent('filters-updated');
        return view('livewire.analyses.analyses-sub-attainment-dashboard')->layout('layouts.student');
    }

    public function redirectBack()
    {
        return redirect(
            $this->getHelper()->getRouteForAttainmentShow(
                BaseAttainment::findOrFail($this->attainment->attainment_id),
                Subject::whereUuid($this->subject)->first()
            )
        );
    }
}
