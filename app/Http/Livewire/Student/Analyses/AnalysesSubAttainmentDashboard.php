<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use tcCore\Attainment;
use tcCore\BaseAttainment;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\AnalysesGeneralDataHelper;
use tcCore\Http\Traits\WithAnalysesGeneralData;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;

class AnalysesSubAttainmentDashboard extends AnalysesDashboard
{
    use WithAnalysesGeneralData;

    public $subject;

    public $showEmptyStateForPValueGraph = false;

    protected $queryString = ['subject'];

    public $attainment;

    public $attainmentOrderNumber = 0;

    public $parentAttainmentOrderNumber = 0;


    public function mount(?BaseAttainment $baseAttainment = null)
    {
        $this->attainment = $baseAttainment;
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
            auth()->user(),
            $this->attainment,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues()
        );

        $this->showEmptyStateForPValueGraph = $result->filter(fn($item) => !is_null($item['score']))->isEmpty();

        $this->dataValues = $result->map(function ($pValue, $key) {
            $link = false;
            if ($pValue->attainment_id) {
                $link = route('student.analyses.subsubattainment.show', [
                    'baseAttainment' => BaseAttainment::find($pValue->attainment_id)->uuid,
                    'subject'    => $this->subject,
                ]);
            }

            return (object)[
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
        return view('livewire.student.analyses.analyses-sub-attainment-dashboard')->layout('layouts.student');
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
        $parentAttainment  = BaseAttainment::find($this->attainment->attainment_id);

        return redirect(
            route(
                'student.analyses.attainment.show',
                ['baseAttainment' => $parentAttainment->uuid,
                'subject' => $this->subject]
            )
        );
    }
}
