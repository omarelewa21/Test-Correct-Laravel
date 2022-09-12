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

class AnalysesSubjectDashboard extends AnalysesDashboard
{
    public $subject;

    protected $topItems = [
        3 => 'Schrijfvaardigheid',
        5 => 'Literatuur',
        6 => 'Oriëntatie op studie en beroep',
    ];

    public function mount(?Subject $subject = null)
    {
        parent::mount();

        $this->subject = $subject;
    }

    public function render()
    {
        $this->dispatchBrowserEvent('filters-updated');
        return view('livewire.student.analyses.analyses-subject-dashboard')->layout('layouts.student');
    }

    public function getDataProperty()
    {
        $result = PValueRepository::getPValuePerAttainmentForStudent(
            auth()->user(),
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues()
        );;

        $this->dataValues = $result->map(function ($pValue, $key) {
            return (object)[
                'x'       => $key + 1,
                'title'   => ucfirst(__('student.leerdoel', ['number' => $key + 1])),
                'count'   => $pValue->cnt,
                'value'   => number_format(($pValue->score > 0 ? $pValue->score : 0), 2),
                'text'    => $pValue->serie,
                'basedOn' => trans_choice('student.attainment_tooltip_title', $pValue->cnt, [
                    'basedOn' => $pValue->cnt
                ]),
                'link'    => route('student.analyses.attainment.show', [
                    'attainment' => Attainment::find($pValue->attainment_id)->uuid,
                    'subject' => $this->subject->uuid
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
        return redirect(route('student.analyses.show'));
    }
}
