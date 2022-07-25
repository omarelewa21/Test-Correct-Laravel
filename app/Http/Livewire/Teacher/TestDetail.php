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

}
