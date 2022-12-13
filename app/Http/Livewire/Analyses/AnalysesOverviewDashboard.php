<?php

namespace tcCore\Http\Livewire\Analyses;

use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Lib\Repositories\TaxonomyRankingRepostitory;
use tcCore\Subject;

class AnalysesOverviewDashboard extends AnalysesDashboard
{
    public function getTopItemsProperty()
    {
        return TaxonomyRankingRepostitory::getForSubjects(
            $this->getHelper()->getForUser(),
            [
                'periods'               => $this->getPeriodsByFilterValues(),
                'education_level_years' => $this->getEducationLevelYearsByFilterValues(),
                'teachers'              => $this->getTeachersByFilterValues(),
            ]
        );
    }

    public function getDataProperty()
    {}

    public function getDataForGraph()
    {
        $result = PValueRepository::getPValueForStudentBySubject(
            $this->getHelper()->getForUser(),
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues()
        );

        $this->showEmptyStateForPValueGraph = $result->filter(fn($item) => !is_null($item['score']))->isEmpty();

        $this->dataValues = $result->map(function ($pValue) {

            $link = false;
            if ($pValue->subject_id) {
                $link = $this->getHelper()->getRouteForSubjectShow(
                    Subject::findOrFail($pValue->subject_id)
                );
            }

            return (object) [
                'x'       => htmlspecialchars_decode($pValue->name),
                'title'   => htmlspecialchars_decode($pValue->name),
                'basedOn' => trans_choice('student.obv count questions', $pValue->cnt ?? 0),
                'value'   => number_format(($pValue->score > 0 ? $pValue->score : 0), 2),
                'link'    => $link,
            ];
        })->toArray();

        return [
            $this->showEmptyStateForPValueGraph,
            $this->dataValues,
        ];
    }

    public function render()
    {
        return view('livewire.analyses.analyses-overview-dashboard')->layout($this->getHelper()->getLayout());;
    }

    protected function getMillerData($subjectId)
    {
        return PValueTaxonomyMillerRepository::getPValueForStudentForSubject(
            $this->getHelper()->getForUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getRTTIData($subjectId)
    {
        return PValueTaxonomyRTTIRepository::getPValueForStudentForSubject(
            $this->getHelper()->getForUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getBloomData($subjectId)
    {
        return PValueTaxonomyBloomRepository::getPValueForStudentForSubject(
            $this->getHelper()->getForUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    public function redirectTeacherBack()
    {
        return CakeRedirectHelper::redirectToCake('analyses.teacher', $this->classUuid);
    }
}
