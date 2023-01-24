<?php

namespace tcCore\Http\Livewire\Analyses;

use tcCore\Attainment;
use tcCore\BaseAttainment;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\AnalysesGeneralDataHelper;
use tcCore\Http\Traits\WithAnalysesGeneralData;
use tcCore\LearningGoal;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Lib\Repositories\TaxonomyRankingRepository;
use tcCore\Scopes\AttainmentScope;
use tcCore\Subject;

class AnalysesSubjectDashboard extends AnalysesDashboard
{
    use WithAnalysesGeneralData;

    public $subject;

    public $attainmentMode;

    public function mount(?Subject $subject = null)
    {
        parent::mount();
        $this->subject = $subject;

        $this->taxonomyIdentifier = $this->subject->id;

        $this->setDefaultAttainmentMode();
    }

    public function getTopItemsProperty()
    {
        return TaxonomyRankingRepository::getForSubject(
            $this->getHelper()->getForUser(),
            $this->subject,
            [
                'periods'               => $this->getPeriodsByFilterValues(),
                'education_level_years' => $this->getEducationLevelYearsByFilterValues(),
                'teachers'              => $this->getTeachersByFilterValues(),
                'isLearningGoal'        => $this->getIsLearningGoalFilter(),
            ]
        );
    }

    public function getAttainmentModeOptionsProperty()
    {
        return [
            Attainment::TYPE   => ucfirst(__('student.eindterm')),
            LearningGoal::TYPE => ucfirst(__('student.leerdoel')),
        ];
    }

    private function setDefaultAttainmentMode()
    {
        if (session()->has('STUDENT_ANALYSES_ATTAINMENT_MODE')) {
            $this->attainmentMode = session()->get('STUDENT_ANALYSES_ATTAINMENT_MODE');
        } else {
            $this->attainmentMode = EducationLevel::getAttainmentType($this->getHelper()->getForUser());
        }
    }

    public function updatedAttainmentMode($value)
    {
        session(['STUDENT_ANALYSES_ATTAINMENT_MODE' => $value]);
        $this->dispatchBrowserEvent('filters-updated');
    }

    private function setGeneralStats()
    {
        $analysesHelper = new AnalysesGeneralDataHelper($this->getHelper()->getForUser());
        $this->generalStats = (array)$analysesHelper->getAllForSubject($this->subject, $this->filters);
    }

    public function render()
    {
        return view('livewire.analyses.analyses-subject-dashboard')->layout($this->getHelper()->getLayout());;
    }

    private function attainmentModeIsLearningGoal()
    {
        return $this->attainmentMode == 'LEARNING_GOAL' ? 1 : 0;
    }

    public function getDataProperty()
    {
    }

    public function getDataForGraph()
    {
        $result = PValueRepository::getPValuePerAttainmentForStudent(
            $this->getHelper()->getForUser(),
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues(),
            $this->subject,
            $this->attainmentModeIsLearningGoal(),
        )->get();

        $this->showEmptyStateForPValueGraph = $result->filter(fn($item) => !is_null($item['score']))->isEmpty();

        $this->dataValues = $result->map(function ($pValue, $key) {
            $link = false;
            if ($pValue->attainment_id) {
                $link = $this->getHelper()->getRouteForAttainmentShow(
                    BaseAttainment::findOrFail($pValue->attainment_id),
                    $this->subject
                );
            }
            $attainmentTranslationLabel = $this->attainmentMode == 'LEARNING_GOAL'
                ? __('student.leerdoel met nummer', ['number' => $key + 1])
                : __('student.eindterm met nummer', ['number' => $key + 1]);

            return (object)[
                'x'       => $key + 1,
                'title'   => ucfirst($attainmentTranslationLabel),
                'count'   => $pValue->cnt,
                'value'   => number_format(($pValue->score > 0 ? $pValue->score : 0), 2),
                'text'    => $pValue->description,
                'basedOn' => trans_choice('student.attainment_tooltip_title', $pValue->cnt ?? 0, [
                    'basedOn' => $pValue->cnt ?? 0
                ]),
                'link'    => $link,
            ];
        })->toArray();

        return [
            $this->showEmptyStateForPValueGraph,
            $this->dataValues,
        ];
    }

    protected function getMillerGeneralGraphData($subjectId)
    {
        return PValueTaxonomyMillerRepository::getPValueForStudentForSubject(
            $this->getHelper()->getForUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getRTTIGeneralGraphData($subjectId)
    {
        return PValueTaxonomyRTTIRepository::getPValueForStudentForSubject(
            $this->getHelper()->getForUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getBloomGeneralGraphData($subjectId)
    {
        return PValueTaxonomyBloomRepository::getPValueForStudentForSubject(
            $this->getHelper()->getForUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    public function redirectBack()
    {
        return redirect(
            $this->getHelper()->getRouteForDashboardShow()
        );
    }


    public function getDataForSubjectTimeSeriesGraph()
    {
        $results =
            // PValueRepository::getPValueForStudentBySubjectDayDateTimeSeries(
             PValueRepository::getPValueForStudentForSubjectByAttainmentDayDateTimeSeries(
            $this->getHelper()->getForUser(),
             $this->subject,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues(),
             $this->getIsLearningGoalFilter()
        );

        $set = [];
        $names = [];
        foreach($results as $result) {
            if (!in_array($result->id, $names)) {
                $names[] = $result->id;
                $prevScore = $result->score ?? 0;
            }
            $set[$result->gen_date][] = $prevScore = $result->score ?? $prevScore;
        }

        $newSet = collect($set)->map(function($arr, $key) {
            return [$key, ...$arr];
        })->values()->toArray();

        $eindtermen = collect($names)->map(function ($id) {
            return Attainment::withoutGlobalScope(AttainmentScope::class)->find($id)->name;
        })->toArray();


        return [false, $newSet, $eindtermen];
    }
}
