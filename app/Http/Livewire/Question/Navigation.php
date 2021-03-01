<?php

namespace tcCore\Http\Livewire\Question;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Livewire\Student\TestTake;
use tcCore\Question;
use function Symfony\Component\String\s;

class Navigation extends Component
{
    public $nav;
    public $testTakeUuid;
    public $q;
    public $queryString = ['q'];

    public $showCloseQuestionModal = false;

    protected $listeners = [
        'redirect-from-closing-a-question' => 'redirectFromClosedQuestion',
        'update-nav-with-closed-question' => 'updateNavWithClosedQuestion'
        ];

    public function mount()
    {
        if (!$this->q) {
            $this->q = 1;
        }
        $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
    }


    public function render()
    {
        return view('livewire.question.navigation');
    }

    private function getDetailsFirstQuestion()
    {
        return ['data' => ['prev' => false, 'next' => true, 'turnin' => false]];
    }

    private function getDetailsLastQuestion()
    {
        return ['data' => ['prev' => true, 'next' => false, 'turnin' => true]];
    }

    private function getDetailsQuestion()
    {
        return ['data' => ['prev' => true, 'next' => true, 'turnin' => false]];
    }

    public function previousQuestion()
    {
        $this->checkIfCurrentQuestionIsInfoscreen($this->q);

        if ($this->q > 1) {
            $this->goToQuestion($this->q-1);
        }

        $details = $this->getDetailsQuestion();
        if ($this->q == 1) {
            $details = $this->getDetailsFirstQuestion();
        }
        $this->dispatchBrowserEvent('update-footer-navigation', $details);

    }

    public function nextQuestion()
    {
        $this->checkIfCurrentQuestionIsInfoscreen($this->q);

        if ($this->q < $this->nav->count()) {
            $this->goToQuestion($this->q+1);
        }

        $details = $this->getDetailsQuestion();
        if ($this->q == $this->nav->count()) {
            $details = $this->getDetailsLastQuestion();
        }
        $this->dispatchBrowserEvent('update-footer-navigation', $details);
    }

    public function toOverview($currentQuestion)
    {
        $this->checkIfCurrentQuestionIsInfoscreen($currentQuestion);

        $isThisQuestion = $this->nav[$this->q-1];
        if ($isThisQuestion['closeable'] && !$isThisQuestion['closed']) {
            $this->dispatchBrowserEvent('close-this-question', $currentQuestion);
        } else {
            return redirect()->to(route('student.test-take-overview', $this->testTakeUuid));
        }
    }

    public function updateQuestionIndicatorColor()
    {
        $newNav = $this->nav->map(function (&$item, $key) {
            $q = $this->q;
            if ($key == --$q) {
                $item['answered'] = true;
                return $item;
            }
            return $item;
        });
        $this->nav = $newNav;
    }

    public function checkIfCurrentQuestionIsInfoscreen($question)
    {
        $questionUuid = $this->nav[--$question]['uuid'];

        if (Question::whereUuid($questionUuid)->first()->type === 'InfoscreenQuestion') {
            $this->emit('changeAnswerUpdatedAt', $questionUuid);
            $this->updateQuestionIndicatorColor();
        }
    }

    public function goToQuestion($question)
    {
        $isThisQuestion = $this->nav[$this->q-1];

        if ($isThisQuestion['closeable'] && !$isThisQuestion['closed']) {
            $this->dispatchBrowserEvent('close-this-question', $question);
        } else {
            $this->q = $question;

            $this->CheckIfCurrentQuestionIsInfoscreen($this->q);

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
    }

    public function redirectFromClosedQuestion($navInfo)
    {
        $this->updateNavWithClosedQuestion($navInfo['closed_question']);
        $this->goToQuestion($navInfo['next_question']);
    }

    public function updateNavWithClosedQuestion($question)
    {
        $newNav = $this->nav->map(function (&$item) use ($question) {
            if ($item['id'] == $question) {
                $item['closed'] = true;
                return $item;
            }
            return $item;
        });
        $this->nav = $newNav;
    }
}
