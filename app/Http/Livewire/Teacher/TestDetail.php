<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use tcCore\GroupQuestion;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\TemporaryLogin;
use Illuminate\Support\Arr;

class TestDetail extends Component
{
    public $uuid;
    protected $test;
    public $groupQuestionDetail;
    public $referrer = '';
    public $mode;
    public $context = 'testdetail';
    public string $previousUrl;

    protected $queryString = ['referrer' => ['except' => '']];

    protected $listeners = [
        'test-deleted'        => 'redirectToTestOverview',
        'testSettingsUpdated' => '$refresh',
        'test-updated'        => '$refresh',
    ];

    public function mount($uuid)
    {
//        @TODO: Should this be implemented ?;
        Gate::authorize('canViewTestDetails', [Test::findByUuid($uuid)]);

        $this->uuid = $uuid;
        $this->setPreviousUrl();
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

    public function updatingPreviousUrl($value)
    {
        abort(403);
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
        redirect()->to($this->previousUrl);
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

    public function openDetail($questionUuid, $inTest = false)
    {
        $this->emit('openModal', 'teacher.question-detail-modal', ['questionUuid' => $questionUuid, 'inTest' => $inTest]);
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
        return auth()->user()->redirectToCakeWithTemporaryLogin($testTake->getPlannedTestOptions());
    }

    private function setContext()
    {
        if (isset($this->mode) && $this->mode === 'cms') {
            $this->context = 'question-bank';
        }
    }

    public function testContainsQuestion($questionId)
    {
        return false;
    }

    /**
     * always go back to teacher.tests route, but with or without params
     *
     * @return void
     */
    private function setPreviousUrl()
    {
        $urlComponents = parse_url(url()->previous());
        $this->previousUrl = url()->previous(); // with params
        if (array_key_exists('path', $urlComponents) && url($urlComponents['path']) !== route('teacher.tests')) { //
            $this->previousUrl = route('teacher.tests'); // without params
        }
    }
}
