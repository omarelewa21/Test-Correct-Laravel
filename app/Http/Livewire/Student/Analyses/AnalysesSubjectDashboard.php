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
use tcCore\Scopes\AttainmentScope;
use tcCore\Subject;
use tcCore\User;

class AnalysesSubjectDashboard extends AnalysesDashboard
{
    public $subject;

    public $attainmentMode;

    public $generalStats = [];


    public function getAttainmentModeOptionsProperty()
    {
        return [
            ucfirst(__('student.eindterm')),
            ucfirst(__('student.leerdoel')),
        ];
    }

//    protected $topItems = [
//        3 => 'Schrijfvaardigheid',
//        5 => 'Literatuur',
//        6 => 'Oriëntatie op studie en beroep',
//    ];

    public function mount(?Subject $subject = null)
    {
        parent::mount();

        $this->subject = $subject;

        $this->setGeneralStats();
    }

    private function setGeneralStats() {
        $this->generalStats = [
            'test' => [
                'count'          => 5,
                'countQuestions' => 34,
                'averagePValue'  => 0.85,
                'averageMark'    => 8.5,
            ],
            'assesment' => [
                'count'          => 5,
                'countQuestions' => 34,
                'averagePValue'  => 0.85,
                'averageMark'    => 4.5,
            ],
        ];
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
            $this->getTeachersByFilterValues(),
            $this->subject,
            $this->attainmentMode,
        );;

        $this->dataValues = $result->map(function ($pValue, $key) {
            $link = false;
            if ($pValue->attainment_id) {
                $link = route('student.analyses.attainment.show', [
                    'attainment' => Attainment::withoutGlobalScope(AttainmentScope::class)->find($pValue->attainment_id)->uuid,
                    'subject'    => $this->subject->uuid
                ]);
            }

            $attainmentTranslationLabel = $this->attainmentMode
                ? __('student.leerdoel met nummer', ['number' => $key + 1])
                : __('student.eindterm met nummer', ['number' => $key + 1]);

            return (object)[
                'x'       => $key + 1,
                'title'   => ucfirst($attainmentTranslationLabel),
                'count'   => $pValue->cnt,
                'value'   => number_format(($pValue->score > 0 ? $pValue->score : 0), 2),
                'text'    => $pValue->serie,
                'basedOn' => trans_choice('student.attainment_tooltip_title', $pValue->cnt ?? 0, [
                    'basedOn' => $pValue->cnt ?? 0
                ]),
                'link'    => $link,
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
