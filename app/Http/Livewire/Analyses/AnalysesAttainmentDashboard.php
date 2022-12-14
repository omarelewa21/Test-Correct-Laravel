<?php

namespace tcCore\Http\Livewire\Analyses;

use tcCore\BaseAttainment;
use tcCore\Http\Traits\WithAnalysesGeneralData;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\TaxonomyRankingRepostitory;
use tcCore\Subject;

class AnalysesAttainmentDashboard extends AnalysesDashboard
{
    use WithAnalysesGeneralData;

    public $attainment;

    public $subject;

    protected $queryString = 'subject';

    public function mount(?BaseAttainment $baseAttainment = null)
    {
        $this->attainment = $baseAttainment;

        $this->taxonomyIdentifier = $this->attainment->id;
        parent::mount();
    }

    public function getTopItemsProperty()
    {
        return TaxonomyRankingRepostitory::getForAttainment(
            $this->getHelper()->getForUser(),
            Subject::whereUuid($this->subject)->first(),
            $this->attainment,
            [
                'periods'               => $this->getPeriodsByFilterValues(),
                'education_level_years' => $this->getEducationLevelYearsByFilterValues(),
                'teachers'              => $this->getTeachersByFilterValues(),
                'isLearningGoal'        => $this->getIsLearningGoalFilter(),
            ]
        );
    }

    public function render()
    {
        return view('livewire.analyses.analyses-attainment-dashboard')->layout($this->getHelper()->getLayout());;
    }

    public function getDataProperty()
    {
    }

    public function getDataForGraph()
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
            $baseAttainment = BaseAttainment::find($pValue->attainment_id);

            $link = false;
            if ($pValue->attainment_id) {
                $link = $this->getHelper()->getRouteForSubAttainmentShow($baseAttainment, $this->subject);
            }

            return (object)[
                'x'       => $key + 1,
                'title'   => $this->attainment->getSubNameWithNumber($key + 1),
                'count'   => $pValue->cnt,
                'value'   => number_format(($pValue->score > 0 ? $pValue->score : 0), 2),
                'text'    => $pValue->description,
                'basedOn' => trans_choice('student.attainment_tooltip_title', $pValue->cnt, [
                    'basedOn' => $pValue->cnt
                ]),
                'link'    => $link,
            ];
        })->toArray();

        return [
            $this->showEmptyStateForPValueGraph,
            $this->dataValues,
        ];
    }

    public function redirectBack()
    {
        return redirect(
            $this->getHelper()->getRouteForSubjectShow(
                Subject::whereUuid($this->subject)->first()
            )
        );
    }
}
