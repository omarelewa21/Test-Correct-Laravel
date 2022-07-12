<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\GroupQuestion;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Question;
use tcCore\Test;

class TestDetail extends Component
{
    public $uuid;
    protected $test;
    public $groupQuestionDetail;

    protected $listeners = [
        'test-deleted'        => 'redirectToTestOverview',
        'testSettingsUpdated' => '$refresh',
    ];

    public function mount($uuid)
    {
        $this->uuid = $uuid;
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
            ->first();
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

    public function openTestInCMS()
    {
        $this->redirect(route('teacher.question-editor', [
            'testId'     => $this->uuid,
            'action'     => 'edit',
            'owner'      => 'test',
            'withDrawer' => 'true',
            'referrer'   => 'teacher.tests',
        ]));
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

    public function duplicateTest()
    {
        $test = Test::findByUuid($this->uuid);

        if ($test->canCopy(auth()->user())) {
            try {
                $newTest = $test->userDuplicate([], Auth::id());
            } catch (\Exception $e) {
                return 'Error duplication failed';
            }

            redirect()->to(route('teacher.test-detail', ['uuid' => $newTest->uuid]));

            return __('general.duplication successful');
        }

        if ($test->canCopyFromSchool(auth()->user())) {
            $this->emitTo('teacher.copy-test-from-schoollocation-modal', 'showModal',  $test->uuid);
            return true;
        }
    }

    public function openDetail($questionUuid)
    {
        $this->emit('openModal', 'teacher.question-detail-modal', ['questionUuid' => $questionUuid]);
    }

    public function getTemporaryLoginToPdfForTest($testUuid)
    {
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => sprintf('/tests/view/%s', $testUuid),
                'page_action' => sprintf("Loading.show();Popup.load('/tests/pdf_showPDFAttachment/%s', 1000);", $testUuid),
            ],
        ]);

        return $controller->toCakeUrl($request);
    }

    public function planTest()
    {
        $test = Test::findByUuid($this->uuid);
        if (!$test->hasDuplicateQuestions() && !$test->hasToFewQuestionsInCarousel() && !$test->hasNotEqualScoresForSubQuestionsInCarousel()) {
            $this->emit('openModal', 'teacher.planning-modal', ['testUuid' => $this->uuid]);
            return false;
        }
        $primaryAction = false;
        $message = __('modal.cannot_schedule_test_full_not_author');

        if ($test->author->is(auth()->user())) {
            $primaryAction = route('teacher.question-editor',
                [
                    'action'         => 'add',
                    'owner'          => 'test',
                    'testId'         => $test->uuid,
                    'testQuestionId' => '',
                    'type'           => '',
                    'isCloneRequest' => '',
                    'withDrawer'     => 'true',
                ]
            );
            $message = __('modal.cannot_schedule_test_full_author');
        }

//        $mode = [
//            'hasDuplicateQuestions' => $test->hasDuplicateQuestions() ,
//            'hasToFewQuestionsInCarousel' => $test->hasToFewQuestionsInCarousel(),
//            'hasEqualScoreForSubQuestions' => $test->hasEqualScoresForSubQuestions(),
//        ];
//
//        $message = $message . print_r($mode, true);

        $this->emit(
            'openModal',
            'alert-modal', [
            'message'               => $message,
            'title'                 => __('modal.cannot_schedule_test'),
            'primaryAction'         => $primaryAction,
            'primaryActionBtnLabel' => __('modal.Toets bewerken')
        ]);
    }

}
