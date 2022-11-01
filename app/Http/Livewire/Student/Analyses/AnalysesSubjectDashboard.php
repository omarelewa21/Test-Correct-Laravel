<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use Illuminate\Support\Facades\Auth;
use tcCore\Attainment;
use tcCore\BaseAttainment;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\AnalysesGeneralDataHelper;
use tcCore\LearningGoal;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Scopes\AttainmentScope;
use tcCore\Subject;

class AnalysesSubjectDashboard extends AnalysesDashboard
{
    public $subject;

    public $attainmentMode;

    public $generalStats = [];


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
            $this->attainmentMode = EducationLevel::getAttainmentType(auth()->user());
        }
    }

    public function updatedAttainmentMode($value) {
        session(['STUDENT_ANALYSES_ATTAINMENT_MODE' =>  $value]);
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

        $this->setDefaultAttainmentMode();

        $this->setGeneralStats();
    }

    private function setGeneralStats()
    {
        $analysesHelper = new AnalysesGeneralDataHelper(Auth::user());
        $this->generalStats = (array)$analysesHelper->getAllForSubject($this->subject);
    }

    public function render()
    {
        $this->dispatchBrowserEvent('filters-updated');
        return view('livewire.student.analyses.analyses-subject-dashboard')->layout('layouts.student');
    }

    private function attainmentModeIsLearningGoal()
    {
        return $this->attainmentMode == 'LEARNING_GOAL' ? 1 : 0;
    }

    public function getDataProperty()
    {
        $result = PValueRepository::getPValuePerAttainmentForStudent(
            auth()->user(),
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues(),
            $this->subject,
            $this->attainmentModeIsLearningGoal(),
        );

        $this->showEmptyStateForPValueGraph = $result->count() === 0;

        $this->dataValues = $result->map(function ($pValue, $key) {
            $link = false;
            if ($pValue->attainment_id) {
                $link = route('student.analyses.attainment.show', [
                    'baseAttainment' => BaseAttainment::find($pValue->attainment_id)->uuid,
                    'subject'    => $this->subject->uuid
                ]);
            }
            $attainmentTranslationLabel = $this->attainmentMode == 'LEARNING_GOAL'
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

    public function showGrades()
    {
        return redirect(route('student.test-takes', ['tab' => 'graded']));
    }
}
