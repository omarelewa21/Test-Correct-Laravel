<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use tcCore\GroupQuestion;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\TemporaryLogin;

class TestDetail extends Component
{
    public $uuid;
    protected $test;
    public $groupQuestionDetail;
    public $referrer = '';
    public $mode;
    public $context = 'testdetail';

    protected $queryString = ['referrer' => ['except' => '']];

    protected $listeners = [
        'test-deleted'        => 'redirectToTestOverview',
        'testSettingsUpdated' => '$refresh',
    ];

    public function mount($uuid)
    {
//        @TODO: Should this be implemented ?;
        Gate::authorize('canViewTestDetails',[Test::findByUuid($uuid)]);

        $this->uuid = $uuid;
        $this->setContext();
    }

    public function booted()
    {
        $this->test = Test::whereUuid($this->uuid)
            ->with([
                'testQuestions' => function ($query) {
                    $query->orderBy('test_questions.order', 'asc');
                },
                'testQuestions.question',
                'testQuestions.question.authors'
            ])
            ->firstOrFail();
    }

    public function getAmountOfQuestionsProperty()
    {
        return $this->test->getAmountOfQuestions();
    }

    public function render()
    {
        return view('livewire.teacher.test-detail')->layout('layouts.app-teacher');
    }

    public function redirectToTestOverview()
    {
        redirect()->to(route('teacher.tests'));
    }

    public function showGroupDetails($groupUuid)
    {
        $groupQuestionId = Question::whereUuid($groupUuid)->value('id');
        $this->groupQuestionDetail = GroupQuestion::whereId($groupQuestionId)
            ->with(['groupQuestionQuestions', 'groupQuestionQuestions.question'])
            ->first();

        return true;
    }

    public function clearGroupDetails()
    {
        $this->reset('groupQuestionDetail');
    }

    public function isQuestionInTest()
    {
        return false;
    }

    public function openDetail($questionUuid)
    {
        $this->emit('openModal', 'teacher.question-detail-modal', ['questionUuid' => $questionUuid]);
    }

    public function handleReferrerActions()
    {
        if (blank($this->referrer)) return;
        if ($this->referrer === 'copy') {
            $this->dispatchBrowserEvent('notify', ['message' => __('general.duplication successful')]);
            $this->referrer = '';
        }
    }

    public function toPlannedTest($takeUuid)
    {
        $testTake = TestTake::whereUuid($takeUuid)->first();
        if($testTake->isAssessmentType()){
            $url = sprintf("test_takes/assessment_open_teacher/%s", $takeUuid);
        }else{
            $url = sprintf("test_takes/view/%s", $takeUuid);
        }
        $options = TemporaryLogin::buildValidOptionObject('page', $url);
        return auth()->user()->redirectToCakeWithTemporaryLogin($options);
    }

    private function setContext()
    {
        if (isset($this->mode) && $this->mode === 'cms') {
            $this->context = 'question-bank';
        }
    }
}
