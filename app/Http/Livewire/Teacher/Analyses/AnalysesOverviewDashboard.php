<?php

namespace tcCore\Http\Livewire\Teacher\Analyses;

use tcCore\Http\Livewire\Student\Analyses\AnalysesDashboard;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Subject;

class AnalysesOverviewDashboard extends AnalysesDashboard
{
    const FILTER_SESSION_KEY = 'TEACHER_ANALYSES_FILTER';

    public function mount() {
        $this->studentUuid =  request('student_uuid');
        $this->classUuid = request('class_uuid');
        parent::mount();

    }

    public function getDataProperty()
    {
        $result = PValueRepository::getPValueForStudentBySubject(
            $this->getHelper()->getForUser(),
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues()
        );

        $this->dataValues = $result->map(function ($pValue) {

            $link = false;
            if ($pValue->subject_id) {
                $link = $this->getHelper()->getRouteForSubjectShow($pValue);
            }

            return (object) [
                'x'       => htmlspecialchars_decode($pValue->name),
                'title'   => htmlspecialchars_decode($pValue->name),
                'basedOn' => trans_choice('student.obv count questions', $pValue->cnt ?? 0),
                'value'   => number_format(($pValue->score > 0 ? $pValue->score : 0), 2),
                'link'    => $link,
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
}
