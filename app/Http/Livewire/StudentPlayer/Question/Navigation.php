<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use tcCore\Answer;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Question;
use tcCore\Http\Livewire\StudentPlayer\Navigation as AbstractNavigation;

class Navigation extends AbstractNavigation
{
    public $testTakeUuid;
    public $startTime;

    protected $listeners = [
        'redirect-from-closing-a-question' => 'redirectFromClosedQuestion',
        'redirect-from-closing-a-group'    => 'redirectFromClosedGroup',
        'update-nav-with-closed-question'  => 'updateNavWithClosedQuestion',
        'update-nav-with-closed-group'     => 'updateNavWithClosedGroup',
        'current-question-answered'        => 'updateQuestionIndicatorColor',
    ];

    public function mount()
    {
        parent::mount();
        $this->startTime = time();
    }

    public function render()
    {
        return view('livewire.student-player.question.navigation');
    }

    public function toOverview($currentQuestion)
    {
        $this->checkIfCurrentQuestionIsInfoscreen($this->q);

        if (parent::toOverview($currentQuestion)) {
            $this->dispatchBrowserEvent(
                'show-loader',
                ['route' => route('student.test-take-overview', $this->testTakeUuid)]
            );
        }
        return true;
    }

    public function goToQuestion($nextQuestion)
    {
        if ($nextQuestion == 'toOverview') {
            return $this->dispatchBrowserEvent(
                'show-loader',
                ['route' => route('student.test-take-overview', $this->testTakeUuid)]
            );
        }

        if (!$this->nav->has($nextQuestion - 1)) {
            return;
        }

        $this->checkIfCurrentQuestionIsInfoscreen($this->q);

        $currentQuestion = $this->nav[$this->q - 1];

        $this->registerTimeForQuestion($currentQuestion);

        if ($this->nav[$nextQuestion - 1]['group']['id'] != $currentQuestion['group']['id'] && $currentQuestion['group']['closeable'] && !$currentQuestion['group']['closed']) {
            $this->dispatchBrowserEvent('close-this-group', $nextQuestion);
            return;
        }
        if ($currentQuestion['closeable'] && !$currentQuestion['closed']) {
            $this->dispatchBrowserEvent('close-this-question', $nextQuestion);
            return;
        }

        parent::goToQuestion($nextQuestion);
    }

    public function updateNavWithClosedGroup($groupId, $callable = null)
    {
        parent::updateNavWithClosedGroup($groupId, function ($item) {
            if ($item['closeable']) {
                $item['closed'] = true;
            }
        });
    }

    private function registerTimeForQuestion($question)
    {
        Answer::registerTime(
            $question['answer_id'],
            time() - $this->startTime
        );
        $this->startTime = time();
    }
}
