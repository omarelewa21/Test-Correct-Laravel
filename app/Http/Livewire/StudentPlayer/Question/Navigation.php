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

        $canGoAway = parent::toOverview($currentQuestion);
        if ($canGoAway) {
            $this->dispatchBrowserEvent('show-loader', ['route' => route('student.test-take-overview', $this->testTakeUuid)]);
        }
        return true;
    }

    public function checkIfCurrentQuestionIsInfoscreen($question)
    {
        $questionUuid = $this->nav[$question - 1]['uuid'];
        if (Question::whereUuid($questionUuid)->first()->type === 'InfoscreenQuestion') {
            $this->dispatchBrowserEvent('mark-infoscreen-as-seen', $questionUuid);
            $this->updateQuestionIndicatorColor($question);
        }
    }

    public function goToQuestion($nextQuestion)
    {
        if($nextQuestion == 'toOverview'){
            return $this->dispatchBrowserEvent('show-loader', ['route' => route('student.test-take-overview', $this->testTakeUuid)]);
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

        $this->q = $nextQuestion;

        $details = $this->getDetailsQuestion();
        if ($this->q == 1) {
            $details = $this->getDetailsFirstQuestion();
        }
        if ($this->q == $this->nav->count()) {
            $details = $this->getDetailsLastQuestion();
        }

        $this->dispatchBrowserEvent('update-footer-navigation', $details);

        $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);

    }

    public function redirectFromClosedQuestion($navInfo)
    {
        $this->updateNavWithClosedQuestion($navInfo['closed_question']);
        $this->goToQuestion($navInfo['next_question']);
    }

    public function redirectFromClosedGroup($navInfo)
    {
        $this->updateNavWithClosedGroup($navInfo['closed_group']);
        $this->goToQuestion($navInfo['next_question']);
    }

    public function updateNavWithClosedQuestion($question)
    {
        $newNav = $this->nav->map(function ($item) use ($question) {
            if ($item['id'] == $question) {
                $item['closed'] = true;
                return $item;
            }
            return $item;
        });
        $this->nav = $newNav;
    }

    public function updateNavWithClosedGroup($groupId)
    {
        $newNav = $this->nav->map(function ($item) use ($groupId) {
            if ($item['group']['id'] == $groupId) {
                $item['group']['closed'] = true;
                if ($item['closeable']) {
                    $item['closed'] = true;
                }
                return $item;
            }
            return $item;
        });

        $this->nav = $newNav;
    }

    private function registerTimeForQuestion($question)
    {
        Answer::registerTime(
            $question['answer_id'],
            time() - $this->startTime
        );
        $this->startTime = time();
    }

    public function redirectTo($route)
    {
        return redirect()->to($route);
    }
}
